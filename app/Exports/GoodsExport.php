<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GoodsExport implements FromCollection, WithStrictNullComparison, WithHeadings
{
    protected $goods;
    protected $headers;

    public function __construct($goods, $headers)
    {
        $this->goods = $goods;
        $this->headers = $headers;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->goods;
    }

    public function headings(): array
    {
        return $this->headers;
    }
}
