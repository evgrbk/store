<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'primary',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'primary' => 'boolean',
    ];
}
