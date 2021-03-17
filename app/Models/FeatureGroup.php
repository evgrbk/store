<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureGroup extends Model
{
    protected $table = 'features_groups';

    public $timestamps = false;

    protected $fillable = ['title', 'active', 'description'];

    protected $casts = [
    	'active' => 'boolean'
    ];

    public function features()
    {
    	return $this->belongsToMany('App\Models\Feature', 'features_group_feature', 'features_group_id', 'feature_id' );
    }
}
