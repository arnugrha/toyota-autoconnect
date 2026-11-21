<?php

namespace App\Services\Leasing\Adapters;

use App\Models\LeasingApplication;
use App\Services\Leasing\Contracts\LeasingProviderInterface;
use Illuminate\Support\Facades\Log;

class MockLeasingAdapter implements LeasingProviderInterface
{
    /**
     * Whether the mock should simulate a network/connection failure.
     */
    protected bool $shouldFail = false;

    /**
     * Force a specific status response for testing purposes.
     */
    protected ?string $forcedStatus = null;

    public function setShouldFail(bool $fail): self
    {
        $this->shouldFail = $fail;
        return $this;
    }

    public function setForcedStatus(?string $status): self
    {
        $this->forcedStatus = $status;
        return $this;
    }

    public function getProviderCode(): string
    {
        return 'mock_leasing';
    }

    public function submitApplication(LeasingApplication $application): array
    {
        Log::info('MockLeasingAdapter: Submitting application', [
            'application_code' => $application->code,
            'customer' => $application->user->name,
            'vehicle' => $application->vehicle->name,
        ]);

        if ($this->shouldFail) {
            Log::warning('MockLeasingAdapter: Simulated provider connection failure for ' . $application->code);
            return [
                'success' => false,
                'reference_id' => null,
                'status' => 'draft',
                'message' => 'Simulasi kegagalan koneksi ke endpoint provider leasing.',
                'raw_response' => ['error' => 'Connection timeout / mock gateway unreachable'],
            ];
        }

        $mockReference = 'MOCK-LEASING-' . strtoupper(substr(md5($application->code . microtime()), 0, 10));

        return [
            'success' => true,
            'reference_id' => $mockReference,
            'status' => 'submitted',
            'message' => 'Aplikasi pembiayaan berhasil diterima oleh Mock Leasing Provider.',
            'raw_response' => [
                'status_code' => '200',
                'application_no' => $mockReference,
                'received_at' => now()->toIso8601String(),
                'provider' => 'Mock Leasing Adapter',
            ],
        ];
    }

    public function checkStatus(LeasingApplication $application): array
    {
        if ($this->shouldFail) {
            return [
                'success' => false,
                'status' => $application->status,
                'message' => 'Gagal menghubungi server leasing provider.',
                'raw_response' => null,
            ];
        }

        $status = $this->forcedStatus ?? 'processing';

        return [
            'success' => true,
            'status' => $status,
            'message' => 'Status pembiayaan terverifikasi dari mock provider.',
            'raw_response' => [
                'provider_status' => $status,
                'checked_at' => now()->toIso8601String(),
            ],
        ];
    }
}

