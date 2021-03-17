<?php

namespace App\Services;

use App\Adapters\GoodPriceAdapter;
use App\Models\Setting;
use App\Repositories\CartRepository;
use App\Repositories\CartItemRepository;
use App\Repositories\CurrencyRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Exception;

class CartService
{
    /**
     * @var CartRepository
     */
    protected $cartRepository;

    /**
     * @var CartItemRepository
     */
    protected $cartItemRepository;

    /**
     * @var GoodPriceAdapter
     */
    private GoodPriceAdapter $goodPriceAdapter;

    /**
     * @var CurrencyRepository
     */
    private CurrencyRepository $currencyRepository;

    /**
     * CartService constructor.
     *
     * @param CartRepository $cartRepository
     * @param CartItemRepository $cartItemRepository
     * @param GoodPriceAdapter $goodPriceAdapter
     */
    public function __construct(CartRepository $cartRepository, CartItemRepository $cartItemRepository, GoodPriceAdapter $goodPriceAdapter, CurrencyRepository $currencyRepository)
    {
        $this->cartRepository = $cartRepository;
        $this->cartItemRepository = $cartItemRepository;
        $this->goodPriceAdapter = $goodPriceAdapter;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * Get all cart goods
     *
     * @param array $data
     * @return Model
     */
    public function getAll(array $data): model
    {
        return $this->getCart($data);
    }

    /**
     * Get or create cart
     *
     * @param array $data
     * @return Model
     */
    public function addGood(array $data): model
    {
        $cart = $this->getCart($data);

        if (!$cart->cartItems()->where('good_id', $data['good_id'])->first()) {
            try {
                $cartItemData = [
                    'cart_id' => $cart->id,
                    'good_id' => $data['good_id'],
                    'count' => $data['count'] ?? 1
                ];

                $this
                    ->cartItemRepository
                    ->store($cartItemData);

            } catch (Exception $e) {
                report($e);
            }
        }

        return $this->getCart($data);
    }

    /**
     * Delete good from cart
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function deleteGood(array $data, $id): model
    {
        $cart = $this->getCart($data);

        $cart_item = $this
            ->cartItemRepository
            ->getWhere('cart_id', $cart->id)
            ->where('good_id', $id)
            ->first();

        if ($cart_item) {
            try {
                $cart_item->delete();
            } catch (Exception $e) {
                report($e);
            }
        }

        return $this->getCart($data);
    }

    /**
     * Update good from cart
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function updateGood(array $data, int $id): model
    {
        $cart = $this->getCart($data);

        $cart_item = $this
            ->cartItemRepository
            ->getWhere('cart_id', $cart->id)
            ->where('good_id', $id)
            ->first();

        if ($cart_item) {
            try {
                $this
                    ->cartItemRepository
                    ->update(['count' => $data['count']], $cart_item->id);
            } catch (Exception $e) {
                report($e);
            }
        }

        return $this->getCart($data);
    }

    /**
     * Get or create cart
     *
     * @param array $data
     * @return Model
     */
    public function getCart(array $data): model
    {
        $customerId = auth()->guard('customers')->id();
        $guestHash = $data['guest_hash'] ?? null;

        if ($customerId || $guestHash) {
            if (!($cart = $this->cartRepository->getWhereWithRelations($customerId ? 'customer_id' : 'guest_hash', $customerId ?? $guestHash)->first())) {
                $cart = $this
                    ->cartRepository
                    ->store([$customerId ? 'customer_id' : 'guest_hash' => $customerId ?? $this->createHash()]);
                $cart->cartSum = 0;
                return $cart;
            } else {
                $sum = 0;
                $currencies = $this->currencyRepository
                    ->all();
                $goodMargin = Arr::get(Setting::getPricing(), 'margin', Setting::DEFAULT_MARGIN);
                foreach ($cart->cartItems as $cartItem) {
                    $this->goodPriceAdapter
                        ->adapt($cartItem->good, $currencies, $goodMargin);
                    $sum += $cartItem->good->price * $cartItem->count;
                }
                $cart->cartSum = round($sum, 2);
                return $cart;
            }
        } else {
            $cart = $this
                ->cartRepository
                ->store(['guest_hash' => $this->createHash()]);
            $cart->cartSum = 0;
            return $cart;
        }
    }

    /**
     * Create unique hash
     *
     * @return string
     */
    public function createHash(): string
    {
        $hash = md5(uniqid(rand(), true));
        //Check hash exists
        $hashes = $this->cartRepository->allColumns(['id', 'guest_hash'])->pluck('id', 'guest_hash')->toArray();
        while (isset($hashes[$hash])) {
            $hash = md5(uniqid(rand(), true));
        }
        return $hash;
    }
}
