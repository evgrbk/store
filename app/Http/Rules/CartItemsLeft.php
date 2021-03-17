<?php

namespace App\Http\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CartItem;
use App\Models\Good;

class CartItemsLeft implements Rule
{
    /**
     * The ID that should be ignored.
     *
     * @var mixed
     */
    protected $goodName;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $items = CartItem::where('cart_id', $value)->with('good')->get();

        foreach ($items as $item) {
            if ($item->good->good_type == Good::TYPE_LIMITED && $item->good->good_left >= $item->count) {
                continue;
            } else if ($item->good->good_type == Good::TYPE_UNLIMITED) {
                continue;
            } else {
                $this->goodName = $item->good->good_title;
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Товара ' . $this->goodName . ' недостаточно на остатке.';
    }
}
