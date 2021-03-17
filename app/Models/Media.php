<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    public const IMAGE_TYPE = 'images';
    public const FILE_TYPE = 'files';

    public const CONVERSION_TYPE_SMALL = 'small';
    public const CONVERSION_TYPE_MIDDLE = 'middle';
    public const CONVERSION_TYPE_BIG = 'big';

    public const CONVERSION_TYPES = [
        self::CONVERSION_TYPE_SMALL,
        self::CONVERSION_TYPE_MIDDLE,
        self::CONVERSION_TYPE_BIG,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'custom_properties' => 'array',
    ];
}
