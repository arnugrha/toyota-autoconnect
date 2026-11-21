<?php

namespace App\Services\Leasing\Adapters;

use App\Models\LeasingApplication;
use App\Services\Leasing\Contracts\LeasingProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AccLeasingAdapter implements LeasingProviderInterface
{
    public function getProviderCode(): string
    {
        return 'acc';
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
        $customEndpoint = config('services.leasing.acc_endpoint');
        $endpoint = $customEndpoint ?? $application->leasingProvider?->endpoint_url ?? 'https://api-sandbox.acc.co.id/v1/auto/submissions';

        // In local development without a custom real endpoint, simulate directly for seamless testing
        if ($this->shouldSimulateFallback() && empty($customEndpoint)) {
            return $this->simulateSubmission($application);
        }

        $payload = [
            'lead_source' => 'TOYOTA_AUTOCONNECT',
            'spk_code' => $application->code,
            'car_unit' => [
                'model' => $application->vehicle->name,
                'price' => $application->vehicle_price,
            ],
            'financial' => [
                'dp' => $application->down_payment,
                'tenor' => $application->tenor_months,
                'installment' => $application->monthly_installment,
            ],
            'applicant' => [
                'full_name' => $application->user->name,
                'email' => $application->user->email,
                'phone' => $application->user->phone,
                'details' => $application->applicant_data,
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
                    'reference_id' => $data['submission_id'] ?? ('ACC-' . strtoupper(substr(md5($application->code), 0, 8))),
                    'status' => 'submitted',
                    'message' => 'Pengajuan berhasil diteruskan ke Astra Credit Companies (ACC).',
                    'raw_response' => $data,
                ];
            }

            Log::error('ACC API Error Response', [
                'application' => $application->code,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($this->shouldSimulateFallback()) {
                Log::info("ACC API returned HTTP {$response->status()}, falling back to sandbox simulation for {$application->code}");
                return $this->simulateSubmission($application);
            }

            return [
                'success' => false,
                'reference_id' => null,
                'status' => 'draft',
                'message' => 'Server ACC mengembalikan respon kesalahan HTTP ' . $response->status(),
                'raw_response' => $response->json(),
            ];
        } catch (Throwable $e) {
            Log::warning('ACC API Connection Exception: ' . $e->getMessage(), [
                'application' => $application->code,
            ]);

            if ($this->shouldSimulateFallback()) {
                Log::info("ACC connection failed ({$e->getMessage()}), activating sandbox simulation fallback for {$application->code}");
                return $this->simulateSubmission($application);
            }

            return [
                'success' => false,
                'reference_id' => null,
                'status' => 'draft',
                'message' => 'Gagal menghubungi server ACC: ' . $e->getMessage(),
                'raw_response' => null,
            ];
        }
    }

    public function checkStatus(LeasingApplication $application): array
    {
        $customEndpoint = config('services.leasing.acc_endpoint');
        $endpoint = $customEndpoint ?? $application->leasingProvider?->endpoint_url ?? 'https://api-sandbox.acc.co.id/v1/auto/submissions';

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
                    'approved', 'passed' => 'approved',
                    'rejected', 'declined' => 'rejected',
                    'revision', 'incomplete' => 'need_revision',
                    default => 'processing',
                };

                return [
                    'success' => true,
                    'status' => $mappedStatus,
                    'message' => 'Status ACC berhasil diverifikasi: ' . ($data['status'] ?? 'Sedang Diproses'),
                    'raw_response' => $data,
                ];
            }

            if ($this->shouldSimulateFallback()) {
                return $this->simulateCheckStatus($application);
            }

            return [
                'success' => false,
                'status' => $application->status,
                'message' => 'Gagal mengambil status terkini dari server ACC.',
                'raw_response' => null,
            ];
        } catch (Throwable $e) {
            Log::warning('ACC status check exception: ' . $e->getMessage());

            if ($this->shouldSimulateFallback()) {
                return $this->simulateCheckStatus($application);
            }

            return [
                'success' => false,
                'status' => $application->status,
                'message' => 'Gangguan koneksi ke server ACC: ' . $e->getMessage(),
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
        $refId = 'ACC-SIM-' . strtoupper(substr(md5($application->code . now()->timestamp), 0, 8));

        return [
            'success' => true,
            'reference_id' => $refId,
            'status' => 'submitted',
            'message' => 'Pengajuan berhasil diterima oleh sistem Astra Credit Companies (ACC - Mode Simulasi Sandbox).',
            'raw_response' => [
                'submission_id' => $refId,
                'status' => 'SUBMITTED',
                'provider' => 'Astra Credit Companies (ACC)',
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
            'message' => 'Status ACC berhasil diverifikasi (Simulasi Sandbox): Sedang Ditinjau oleh Analis Kredit.',
            'raw_response' => [
                'submission_id' => $application->provider_reference,
                'status' => 'PROCESSING',
                'stage' => 'Credit Analysis & Verification',
                'environment' => 'sandbox_simulation',
                'synced_at' => now()->toIso8601String(),
            ],
        ];
    }
}
