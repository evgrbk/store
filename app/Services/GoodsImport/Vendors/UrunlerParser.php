<?php

namespace App\Services\GoodsImport\Vendors;

use App\Services\GoodsImport\BaseParser;

class UrunlerParser extends BaseParser
{
    /**
     * @var string $signature
     */
    public string $signature = "Urunler.Urun";

    /**
     * @var array $src
     */
    public array $src = [
        'Urunler.*.UrunAdi',
        'Urunler.*.Kategori',
        'Urunler.*.Barkod',
        'Urunler.*.Marka',
        'Urunler.*.SatisFiyati',
        'Urunler.*.IndirimliFiyat',
        'Urunler.*.Aciklama',
        'Urunler.*.Resimler',
    ];

    /**
     * @var string $good_path
     */
    public string $good_path = "Urunler";

    /**
     * @var string $good_prefix
     */
    public string $good_prefix = "Urunler.*.";
}
