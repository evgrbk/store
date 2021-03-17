<?php

namespace App\Observers;

use App\Models\Good;
use Illuminate\Support\Str;

class GoodsObserver
{
    /**
     * Handle the good "creating" event.
     *
     * @param  \App\Models\Good  $good
     * @return void
     */
    public function creating(Good $good)
    {
        $good->seo_slug = Str::slug($good->seo_slug ?? $good->good_title);
    }

    /**
     * Handle the good "updating" event.
     *
     * @param  \App\Models\Good  $good
     * @return void
     */
    public function updating(Good $good)
    {
        $good->seo_slug = Str::slug($good->seo_slug ?? $good->good_title);
    }

}
