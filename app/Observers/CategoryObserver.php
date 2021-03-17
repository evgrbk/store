<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CategoryObserver
{
    /**
     * Handle the category "creating" event.
     *
     * @param  \App\Category  $category
     * @return void
     */
    public function creating(Category $category)
    {

        $category->seo_slug = Str::slug($category->seo_slug ?? $category->title);
    }

    /**
     * Handle the category "updating" event.
     *
     * @param  \App\Category  $category
     * @return void
     */
    public function updating(Category $category)
    {
        $category->seo_slug = Str::slug($category->seo_slug ?? $category->title);
    }

}
