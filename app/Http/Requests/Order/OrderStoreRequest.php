<?php

namespace App\Http\Requests\Order;

use App\Http\Rules\CartItemsActive;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Cart;
use App\Http\Rules\CartItemsLeft;

class OrderStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'guest_hash' => [
                'required_without:customer_id',
                'string',
                'regex:/^[a-z0-9]{32}$/i',
                Rule::exists('carts')
            ],
            'customer_id' => [
                'required_without:guest_hash',
                'int',
                Rule::exists('customers', 'id')
            ],
            'cart_id' => [
                'required',
                'int',
                Rule::exists('carts', 'id'),
                new CartItemsActive,
                new CartItemsLeft
            ],
            'cart_items' => [
                'required',
                'array',
                'min:1'
            ],
            'delivery_type' => [
                'required',
                'int',
                Rule::in(Order::DELIVERY_TYPES)
            ],
            'full_name' => 'required|string|max:64',
            'email' => 'required|string|max:64|email:filter',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:128',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'cart_items.*' => 'Нельзя делать заказ с пустой корзиной!',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($customerId = auth()->guard('customers')->id()) {
            $this->merge([
                'customer_id' => $customerId,
            ]);
        }

        if ($cart = Cart::where($customerId ? 'customer_id' : 'guest_hash', $customerId ?? $this->guest_hash)->first()) {
            $this->merge([
                'cart_id' => $cart->id,
            ]);

            if (count($cart->cartItems)) {
                $this->merge([
                    'cart_items' => array($cart->cartItems->toArray()),
                ]);
            }
        }
    }

}
