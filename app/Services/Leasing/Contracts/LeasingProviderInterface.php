<?php

namespace App\Services\Leasing\Contracts;

use App\Models\LeasingApplication;

interface LeasingProviderInterface
{
    /**
     * Get the unique provider identifier code.
     */
    public function getProviderCode(): string;

    /**
     * Submit a leasing application to the financing provider.
     *
     * @return array{
     *     success: bool,
     *     reference_id: ?string,
     *     status: string,
     *     message: string,
     *     raw_response: ?array
     * }
     */
    public function submitApplication(LeasingApplication $application): array;

    /**
     * Poll / check the current evaluation status of an application.
     *
     * @return array{
     *     success: bool,
     *     status: string,
     *     message: string,
     *     raw_response: ?array
     * }
     */
    public function checkStatus(LeasingApplication $application): array;
}

