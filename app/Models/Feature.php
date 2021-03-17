<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
	public $timestamps = false;

    protected $fillable = [
    	'title',
    	'active',
    	'type'
    ];

    protected $casts = [
    	'active' => 'boolean'
    ];
}
