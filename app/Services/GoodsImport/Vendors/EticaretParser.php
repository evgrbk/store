<?php

namespace App\Services\GoodsImport\Vendors;

use App\Services\GoodsImport\BaseParser;

class EticaretParser extends BaseParser
{
    /**
     * @var string $signature
     */
    public string $signature = "Product";

    /**
     * @var array $src
     */
    public array $src = [
        '*.Name',
        '*.category',
        '*.Product_code',
        '*.Brand',
        '*.NameE',
        '*.mainCategory',
        '*.Price',
        '*.Stock',
        '*.Image1',
        '*.Image2',
        '*.Image3',
        '*.Image4',
        '*.Image5',
    ];

    /**
     * @var string $good_prefix
     */
    public string $good_prefix = "*.";
}
