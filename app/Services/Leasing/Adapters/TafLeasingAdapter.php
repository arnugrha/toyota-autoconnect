<?php

namespace App\Services\Leasing\Adapters;

use App\Models\LeasingApplication;
use App\Services\Leasing\Contracts\LeasingProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TafLeasingAdapter implements LeasingProviderInterface
{
    public function getProviderCode(): string
    {
        return 'taf';
    }

    /**
     * Determine if sandbox simulation fallback is enabled.
     */
    protected function shouldSimulateFallback(): bool
    {
        return (bool) config('services.leasing.mock_fallback', env('LEASING_MOCK_FALLBACK', app()->isLocal()));
    }

    public function submitApplication(LeasingApplication $application): array
    {
        $customEndpoint = config('services.leasing.taf_endpoint');
        $endpoint = $customEndpoint ?? $application->leasingProvider?->endpoint_url ?? 'https://api-sandbox.taf.co.id/v1/applications';

        // In local development without a custom real endpoint, simulate directly for seamless testing
        if ($this->shouldSimulateFallback() && empty($customEndpoint)) {
            return $this->simulateSubmission($application);
        }

        $payload = [
            'partner_code' => 'TOYOTA_AUTOCONNECT',
            'application_number' => $application->code,
            'vehicle' => [
                'name' => $application->vehicle->name,
                'category' => $application->vehicle->category,
                'price' => $application->vehicle_price,
            ],
            'financing' => [
                'down_payment' => $application->down_payment,
                'tenor_months' => $application->tenor_months,
                'estimated_installment' => $application->monthly_installment,
            ],
            'customer' => [
                'name' => $application->user->name,
                'email' => $application->user->email,
                'phone' => $application->user->phone,
                'employment_type' => $application->applicant_data['employment_type'] ?? 'Karyawan',
                'monthly_income' => $application->applicant_data['monthly_income'] ?? 0,
            ],
        ];

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Toyota-AutoConnect-Leasing/1.0',
                ])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'reference_id' => $data['reference_id'] ?? ('TAF-' . strtoupper(substr(md5($application->code), 0, 8))),
                    'status' => 'submitted',
                    'message' => 'Pengajuan berhasil dikirimkan ke sistem Toyota Astra Financial Services.',
                    'raw_response' => $data,
                ];
            }

            Log::error('TAF API Error Response', [
                'application' => $application->code,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($this->shouldSimulateFallback()) {
                Log::info("TAF API returned HTTP {$response->status()}, falling back to sandbox simulation for {$application->code}");
                return $this->simulateSubmission($application);
            }

            return [
                'success' => false,
                'reference_id' => null,
                'status' => 'draft',
                'message' => 'Server TAF mengembalikan respons kesalahan HTTP ' . $response->status(),
                'raw_response' => $response->json(),
            ];
        } catch (Throwable $e) {
            Log::warning('TAF API Connection Exception: ' . $e->getMessage(), [
                'application' => $application->code,
            ]);

            if ($this->shouldSimulateFallback()) {
                Log::info("TAF connection failed ({$e->getMessage()}), activating sandbox simulation fallback for {$application->code}");
                return $this->simulateSubmission($application);
            }

            return [
                'success' => false,
                'reference_id' => null,
                'status' => 'draft',
                'message' => 'Gagal menghubungkan ke layanan TAF: ' . $e->getMessage(),
                'raw_response' => null,
            ];
        }
    }

    public function checkStatus(LeasingApplication $application): array
    {
        $customEndpoint = config('services.leasing.taf_endpoint');
        $endpoint = $customEndpoint ?? $application->leasingProvider?->endpoint_url ?? 'https://api-sandbox.taf.co.id/v1/applications';

        if ($this->shouldSimulateFallback() && empty($customEndpoint)) {
            return $this->simulateCheckStatus($application);
        }

        try {
            $response = Http::timeout(5)
                ->get($endpoint . '/' . ($application->provider_reference ?? $application->code));

            if ($response->successful()) {
                $data = $response->json();
                $providerStatus = strtolower($data['status'] ?? 'processing');

                $mappedStatus = match ($providerStatus) {
                    'approved', 'accept' => 'approved',
                    'rejected', 'declined' => 'rejected',
                    'revision', 'need_document' => 'need_revision',
                    default => 'processing',
                };

                return [
                    'success' => true,
                    'status' => $mappedStatus,
                    'message' => 'Status TAF berhasil disinkronisasi: ' . ($data['status'] ?? 'Dalam Analisis'),
                    'raw_response' => $data,
                ];
            }

            if ($this->shouldSimulateFallback()) {
                return $this->simulateCheckStatus($application);
            }

            return [
                'success' => false,
                'status' => $application->status,
                'message' => 'Gagal mengambil status terkini dari server TAF.',
                'raw_response' => null,
            ];
        } catch (Throwable $e) {
            Log::warning('TAF status check exception: ' . $e->getMessage());

            if ($this->shouldSimulateFallback()) {
                return $this->simulateCheckStatus($application);
            }

            return [
                'success' => false,
                'status' => $application->status,
                'message' => 'Gangguan koneksi ke layanan TAF: ' . $e->getMessage(),
                'raw_response' => null,
            ];
        }
    }

    /**
     * Provide a realistic sandbox simulation response for development and testing.
     *
     * @return array<string, mixed>
     */
    protected function simulateSubmission(LeasingApplication $application): array
    {
        $refId = 'TAF-SIM-' . strtoupper(substr(md5($application->code . now()->timestamp), 0, 8));

        return [
            'success' => true,
            'reference_id' => $refId,
            'status' => 'submitted',
            'message' => 'Pengajuan berhasil dikirimkan ke Toyota Astra Financial Services (TAF - Mode Simulasi Sandbox).',
            'raw_response' => [
                'reference_id' => $refId,
                'status' => 'SUBMITTED',
                'provider' => 'Toyota Astra Financial Services (TAF)',
                'environment' => 'sandbox_simulation',
                'applicant_name' => $application->user->name,
                'vehicle_unit' => $application->vehicle->name,
                'timestamp' => now()->toIso8601String(),
                'notes' => 'Simulasi endpoint development aktif karena kredensial API eksternal belum dikonfigurasi.',
            ],
        ];
    }

    /**
     * Provide a realistic status check simulation for development and testing.
     *
     * @return array<string, mixed>
     */
    protected function simulateCheckStatus(LeasingApplication $application): array
    {
        return [
            'success' => true,
            'status' => 'processing',
            'message' => 'Status TAF berhasil disinkronisasi (Simulasi Sandbox): Dalam Analisis Analis Kredit.',
            'raw_response' => [
                'reference_id' => $application->provider_reference,
                'status' => 'PROCESSING',
                'stage' => 'Document & Credit Review',
                'environment' => 'sandbox_simulation',
                'synced_at' => now()->toIso8601String(),
            ],
        ];
    }
}
