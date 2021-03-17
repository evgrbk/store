<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentService extends Model
{
    public const QIWI_SERVICE = 'qiwi';
    public const YANDEX_KASSA_SERVICE = 'yandex_kassa';
    public const BLOCKCHAIN_SERVICE = 'blockchain';
    public const PAYEER_SERVICE = 'payeer';

    public const PAYMENT_SERVICES = [
        // self::YANDEX_KASSA_SERVICE,
        self::BLOCKCHAIN_SERVICE,
        self::QIWI_SERVICE,
        self::PAYEER_SERVICE
    ];

    protected $fillable = [
        'service_title',
        'data',
        'base_settings'
    ];

    protected $casts = [
        'data' => 'array',
        'base_settings' => 'array',
        'active' => 'boolean'
    ];
}
