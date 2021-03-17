<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoImportLog extends Model
{
    public const STATUS_OK = 1;
    public const STATUS_ERROR = 2;

    protected $fillable = [
        'auto_import_id',
        'status',
        'message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'auto_import_id' => 'integer',
        'status' => 'integer',
    ];
}
