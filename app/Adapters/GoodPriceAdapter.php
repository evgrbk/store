<?php

namespace App\Adapters;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Models\Discount;

class GoodPriceAdapter
{
    /**
     * Calculate prices for a good
     *
     * @param Model $model
     * @param Collection $currencies
     * @param int $margin
     * @return void
     */
    public function adapt(Model $model, Collection $currencies, int $margin): void
    {
        //Make price from integer and decimal parts
        $price_float = (float)($model->price_integer . '.' . $model->price_decimal);

        //Apply margin
        $price_float *= (1 + $margin / 100);

        //Get all active discounts
        $discounts = Discount::getAvailableList();
        //Apply all satisfied discounts
        foreach ($discounts as $discount) {
            if ($discount->type != Discount::TYPE_DISCOUNT) {
                continue;
            }
            if (!$discount->all_brands) {
                //Search brand if discount not for all
                if (!count($discount->brands->where('id', $model->brand_id))) {
                    continue;
                }
            }
            if (!$discount->all_categories) {
                //Search category if discount not for all
                if (!count($discount->categories->where('id', $model->category_id))) {
                    continue;
                }
            }

            //If all conditions are satisfied
            if ($discount->value_type == Discount::VALUE_TYPE_FIX) {
                //Subtract fixed amount from price
                $price_float -= $discount->value;
                if ($price_float < 0) {
                    $price_float = 0;
                }
            } else if ($discount->value_type == Discount::VALUE_TYPE_PERCENT) {
                //Subtract percent from price
                $price_float -= $price_float * ($discount->value / 100);
            }
        }

        //Apply price with margin and discounts to model
        $model->price = round((float)$price_float, strlen($model->price_decimal));
    }
}
