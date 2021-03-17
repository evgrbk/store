<?php

namespace App\Services;

interface PaymentServiceInterface
{
    /**
     * Create payment function
     *
     * @param array $data
     * @return array
     */
    public function createPayment(): array;

    public function callbackHandler();
}
