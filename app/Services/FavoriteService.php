<?php

namespace App\Services;

use App\Models\CustomerFavorite;
use Illuminate\Support\Collection;

class FavoriteService extends Service
{
    /**
     * Get favorites goods of customer
     *
     * @param array $data
     * @return Collection
     */
    public function getCustomerFavorites(array $data): Collection
    {
        return CustomerFavorite::where('customer_id', $data['customer_id'])->get()->pluck('good_id');
    }

    /**
     * Create setting
     *
     * @param array $data
     * @return bool
     */
    public function createOrDelete(array $data): bool
    {
        $favorite = CustomerFavorite::where('customer_id', $data['customer_id'])->where('good_id', $data['good_id'])->first();
        if ($favorite) {
            $favorite->delete();
            return false;
        } else {
            CustomerFavorite::create([
                'good_id' => $data['good_id'],
                'customer_id' => $data ['customer_id']
            ]);
            return true;
        }
    }

}
