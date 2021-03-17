<?php

namespace App\Services\Payment;

class PaymentProvider
{
    protected $mapProviders = [
        'qiwi' => QiwiPaymentService::class,
        'payeer' => PayeerPaymentService::class,
        'blockchain' => BitapsPaymentService::class
    ];

    public function makeProvider(string $name)
    {
        return app()->make($this->mapProviders[$name]);

    }
}
