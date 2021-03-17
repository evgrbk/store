<?php

namespace App\Services;

use App\Adapters\GoodPriceAdapter;
use App\Http\Resources\Order\OrderResource;
use App\Repositories\CartRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderGoodRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class OrderService extends Service
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var OrderRepository
     */
    private $orderGoodRepository;

    /**
     * @var CartRepository
     */
    protected $cartRepository;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var GoodPriceAdapter
     */
    private GoodPriceAdapter $goodPriceAdapter;

    /**
     * @var CurrencyRepository
     */
    private CurrencyRepository $currencyRepository;

    /**
     * OrderService constructor.
     *
     * @param OrderRepository $orderRepository
     * @param OrderGoodRepository $orderGoodRepository
     * @param CartRepository $cartRepository
     * @param CustomerRepository $customerRepository
     * @param GoodPriceAdapter $goodPriceAdapter
     * @param CurrencyRepository $customerRepository
     */
    public function __construct(OrderRepository $orderRepository,
                                OrderGoodRepository $orderGoodRepository,
                                CartRepository $cartRepository,
                                CustomerRepository $customerRepository,
                                GoodPriceAdapter $goodPriceAdapter,
                                CurrencyRepository $currencyRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->orderGoodRepository = $orderGoodRepository;
        $this->cartRepository = $cartRepository;
        $this->customerRepository = $customerRepository;
        $this->goodPriceAdapter = $goodPriceAdapter;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * Get orders
     *
     * @param int $limit
     * @param int|null $customerId
     * @return LengthAwarePaginator
     */
    public function getList(int $limit, ?int $customerId = null): LengthAwarePaginator
    {
        if ($customerId) {
            return $this->orderRepository
                ->paginateAllWithCustomer($limit, $customerId);
        } else {
            return $this->orderRepository
                ->paginateAll($limit);
        }
    }

    /**
     * Get order
     *
     * @param int $id
     * @param int|null $customerId
     * @return Model|null
     */
    public function showOne(int $id, ?int $customerId = null): ?Model
    {
        if ($customerId) {
            return $this->orderRepository->getByCustomer($id, $customerId);
        } else {
            return $this->orderRepository->getRecord($id);
        }
    }

    /**
     * Create order
     *
     * @param array $data
     * @return null|Model
     */
    public function create(array $data): ?Model
    {
        try {
            DB::beginTransaction();

            $data['status'] = Order::STATUS_PENDING;

            $order = $this->orderRepository
                ->store($data);

            $cartItems = $this->cartRepository
                ->getRecord($data['cart_id'])
                ->cartItems()
                ->with('good.ratings')
                ->get();

            $margin = Arr::get(Setting::getPricing(), 'margin', Setting::DEFAULT_MARGIN);
            $currencies = $this->currencyRepository
                ->all();

            foreach ($cartItems as $item) {
                $this->goodPriceAdapter
                    ->adapt($item->good, $currencies, $margin);
                $orderGoods[] = [
                    'order_id' => $order->id,
                    'good_id' => $item->good_id,
                    'good_price' => $item->good->price,
                    'good_count' => $item->count
                ];
            }

            $this->orderGoodRepository->storeMany($orderGoods);

            //Add address to customer if not exists
            if (isset($data['customer_id'])) {
                $addresses = $order->customer->addresses;

                if (empty($addresses) || !in_array($order->address, $addresses)) {
                    $addresses[] = $order->address;

                    $this->customerRepository->update([
                        'addresses' => $addresses
                    ], $order->customer_id);
                }
            }

            //Clear cart
            $this->cartRepository
                ->getRecord($data['cart_id'])
                ->cartItems()->delete();

            DB::commit();

            $order = $this->orderRepository
                ->model
                ->with('orderGoods.good.ratings', 'orderGoods.good.translates')
                ->find($order->id);

            foreach ($order->orderGoods as $item) {
                $this->goodPriceAdapter
                    ->adapt($item->good, $currencies, $margin);
            }

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return null;
        }
    }

    /**
     * Update order
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        return $this->orderRepository
            ->update($data, $id);
    }

    /**
     * Delete order
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->orderRepository
            ->destroy($id);
    }

}
