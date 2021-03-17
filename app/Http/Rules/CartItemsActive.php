<?php

namespace App\Http\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CartItem;

class CartItemsActive implements Rule
{
    /**
     * Good name with error
     *
     * @var string
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
            if (!$item->good->active) {
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
        return 'Товар ' . $this->goodName . ' недоступен для заказа.';
    }
}
