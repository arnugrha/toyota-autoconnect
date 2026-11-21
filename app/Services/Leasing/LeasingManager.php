<?php

namespace App\Services\Leasing;

use App\Models\LeasingProvider;
use App\Services\Leasing\Adapters\AccLeasingAdapter;
use App\Services\Leasing\Adapters\MockLeasingAdapter;
use App\Services\Leasing\Adapters\TafLeasingAdapter;
use App\Services\Leasing\Contracts\LeasingProviderInterface;
use InvalidArgumentException;

class LeasingManager
{
    /**
     * Cache of instantiated adapters.
     *
     * @var array<string, LeasingProviderInterface>
     */
    protected array $adapters = [];

    /**
     * Resolve provider adapter instance based on provider code.
     */
    public function resolve(LeasingProvider|string $provider): LeasingProviderInterface
    {
        $code = is_string($provider) ? $provider : $provider->code;

        if (isset($this->adapters[$code])) {
            return $this->adapters[$code];
        }

        $adapter = match ($code) {
            'taf' => new TafLeasingAdapter(),
            'acc' => new AccLeasingAdapter(),
            'mock_leasing' => new MockLeasingAdapter(),
            default => new MockLeasingAdapter(),
        };

        $this->adapters[$code] = $adapter;

        return $adapter;
    }

    /**
     * Manually bind an adapter instance (useful for testing and dependency injection).
     */
    public function bind(string $code, LeasingProviderInterface $adapter): void
    {
        $this->adapters[$code] = $adapter;
    }
}

