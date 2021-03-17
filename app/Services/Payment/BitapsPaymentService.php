<?php

namespace App\Services\Payment;

use App\Exceptions\BuisinessLogicException;

use App\Exceptions\HttpExceptionCodes;
use App\Models\Good;
use App\Models\Payment;
use App\Services\FixerService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class BitapsPaymentService extends PaymentService
{
    protected $provider = 'blockchain';

    protected $orderIdField = 'external_id';

    public function createPayment(): array
    {
        try {
            $order = $this->createOrder();

        } catch (\Exception $e) {

            $this->log->error($e);

            if ($e instanceof BuisinessLogicException) {
                return [
                    'error_code' => HttpExceptionCodes::SERVER_ERROR,
                    'error_message' => $e->getMessage()];
            }

            return $this->getErrors();
        }

        return $this->getLink($order);

    }

    public function callbackHandler()
    {
        $data = $this->request->all();

        $this->log->info('CALLBACK_REQUEST: ' . json_encode($data));

        if ($this->request->get('event') != 'confirmed') {
            return;
        }
        if ($this->request->get('confirmations') < config('payment.bitaps.confirmations.' .  strtolower($this->request->get('currency')))) {
            return;
        }

        $payment = $this->getPayment($this->request->get('code'));


        if (!$payment) {
            $this->log->error('CALLBACK_RESPONSE: PAYMENT NOT FOUND');
            return;
        }

        if ($payment->status == Payment::STATUS_SUCCEEDED) {
            return;
        }

        if ($payment->status == Payment::STATUS_FAILED) {
            return;
        }

        $amount = $this->getAmount($this->request->get('amount'), $this->request->get('currency'));

        if ($amount < 0) {
            return;
        }

        if ($amount != $payment->additional_data['amount']) {
            $this->log->info('CALLBACK_RESPONSE: amount incorrect');
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'raw' => $this->request->all(),
                'external_status' => 'Amount incorrect'
            ]);

            $this->failProcessPayment($payment);
            return;
        }

        $this->succesProcessPayment($payment);

        $payment->update([
            'status' => Payment::STATUS_SUCCEEDED,
            'raw' => $this->request->all(),
            'external_status' => $this->request->get('event')
        ]);

        return;
    }

    protected function getLink(Payment $payment): array
    {

        try {
            $walletId = $this->getWallet($this->currency);

            if (!$walletId) {

                throw new \Exception("Оплата {$this->currency} временно не доступна.");

            }

            $service = new Bitaps($this->currency);
            $data = $service->createPaymentAddress($walletId);

            if (!isset($data['error_code'])) {
                $payment->invoice = $data['invoice'];
                $payment->external_id = $data['payment_code'];

                $additional_data =  $payment->additional_data;

                $additional_data['address'] = $data['address'];
                $payment->additional_data = $additional_data;
            }

            $payment->raw = $data;
            $payment->save();

        } catch (\Exception $e) {

            $this->log->error('CREATE PAYMENT: ' . $e->getMessage());
            $this->log->error('CREATE PAYMENT: ' . $e);

            foreach ($payment->goods as $good) {
                $this->goodRepository
                    ->unreserveGood($good->id, $good->pivot->quantity);

            }

            $payment->status = Payment::STATUS_FAILED;
            $payment->raw =  $e->getMessage() ?? null;
            $payment->save();

            return [
                'error' => true,
                'error_code' => 409,
                'error_message' => $e->getMessage()
            ];
        }

        return [
            'link' => config('app.front_url') . "/checkout/payment/{$this->provider}?orderId={$payment->uuid}"
        ];
    }


    /**
    * Covert amount to BTC
    *
    * $amount decimal - amount to convert
    * $from string - currency from convert
    * $to string - currency to convert
    */
    protected function convert($amount, string $from, string $to)
    {
        $service = new FixerService();

        $to = strtoupper($to);

        try {

            $response = $service->live($from, [$to]);

            if (isset($response['error']) or $response['success'] == false) {
                throw new BuisinessLogicException('Ошибка конвертации');
            }

            $rate = $response['rates'][$to];

            $result = round($amount/$rate, config('payment.bitaps.round.' . strtolower($to), 8));
        } catch (\Exception $e) {

            $this->log->error($e);

            throw new BuisinessLogicException('Ошибка конвертации');
        }

        return $result;

    }

    public function getPaymentData(string $paymentId)
    {
        if (!$paymentId) {
            return response()->json([
                'status' => 'fail'
            ], 404);
        }

        $this->orderIdField = 'uuid';

        $payment = $this->getPayment($paymentId);

        if (!$payment or $payment->status != Payment::STATUS_REQUESTED) {
            return response()->json([
              'status' => 'fail'
            ], 404);
        }

        $walletId = $this->getWallet($payment->additional_data['currency']);

        if (!$walletId) {
            return response()->json([
              'status' => 'fail'
            ], 404);
        }

        $service = new Bitaps( $payment->additional_data['currency']);
        $data = $service->getListWalletAddressTransactions($walletId, $payment->additional_data['address']);

        if (isset($data['error'])) {
            return response()->json([
              'status' => 'fail',
              'error_message' => 'Не удалось получить данные о платеже'
            ], 500);
        };

        $payStatus = 'waiting';

        if (count($data['pending_transactions']['tx_list']) > 0) {
            $payStatus = 'pending';
        }

        if (count($data['transactions']['tx_list']) > 0) {
            $payStatus = 'success';
        }

        return response()->json([
            'status' => 'ok',
            'id' => $payment->id,
            'amount' => $payment->total_sum,
            'amount_btc' => $payment->additional_data['amount'],
            'address' => $payment->additional_data['address'],
            'expires_at' => Carbon::now()->lessThan(Carbon::parse($payment->expires_at))
                ? Carbon::parse($payment->expires_at)->diffInSeconds()
                : 0,
            "currency" => $payment->additional_data['currency'],
            "pay_status" => $payStatus
        ], 200);
    }

    protected function getWallet(string $currency)
    {
        return config("payment.bitaps.wallet.{$currency}");
    }

    protected function getAmount($amount, $currency)
    {
        if ($currency == 'BTC') {
            return $amount / 100000000;
        }

        if ($currency == 'ETH') {
            return $amount / 1000000000000000000;
        }

        return round($amount, config('payment.bitaps.round.' . strtolower($currency), 8));
    }

    public function createOrder()
    {
        $price_integer = 0; // rubles
        $price_decimal = 0; // penny

        $ids = [];
        $quantity = [];
        foreach ($this->cart as $good) {
            $ids[] = $good['id'];
            $quantity[$good['id']] = $good['quantity'];
        }

        $goods = $this
            ->goodRepository
            ->getRecordsByIds($ids);

        if (count($goods) == 0) {

            $this->log
                ->error('PAYMENT CREATE ERROR: ' . 'goods not found');

            $this->errors['error_code'] = HttpExceptionCodes::GOOD_NOT_FOUND;

            throw new \Exception;
        }

        foreach ($goods as $index => $good) {

            if (!$good OR !$good->active) {

                $this->log
                    ->error('PAYMENT CREATE ERROR: ' . 'goods not active: ID=' . $good->id);

                $this->errors['error_code'] = HttpExceptionCodes::GOOD_NOT_FOUND;
                $this->errors['goods'][] = [
                    'id' => $good->id
                ];

                throw new \Exception;

            }

            if (!$good->files or count($good->files) == 0) {

                $this->log
                    ->error('PAYMENT CREATE ERROR: ' . 'files does not exists for goods ID=' . $good->id);

                $this->errors['error_code'] = HttpExceptionCodes::NO_GOOD_FILE;
                $this->errors['goods'][] = [
                    'id' => $good->id
                ];

                throw new \Exception;

            }

            if ($good->good_type == Good::TYPE_LIMITED and ($good->good_left - $good->count_reserved) < $quantity[$good->id]) {

                $this->errors['error_code'] = HttpExceptionCodes::NOT_ENOUGH_GOOD;
                $this->errors['goods'][] = [
                    'id' => $good->id,
                    'balance' => $good->good_left-$good->count_reserved
                ];

                throw new \Exception;
            }

            $price_integer += $good->price_integer * $quantity[$good->id];
            $price_decimal += $good->price_decimal * $quantity[$good->id];
        }

        // count cent part
        $price_decimal_rub = strlen(substr($price_decimal, 0, -2)) ? substr($price_decimal, 0, -2) : 0;
        $price_decimal_cent = substr($price_decimal, -2);
        $price_integer = (int)($price_integer + $price_decimal_rub);
        $price_decimal = (int)$price_decimal_cent;

        // create data to save a payment
        $paymentService = $this->paymentServiceRepository
            ->getWhere('service_title', $this->provider)
            ->first();

        if (!$paymentService) {
            $this->log
                ->error('PAYMENT CREATE ERROR: payment settings not found');

            $this->errors['error_code'] = HttpExceptionCodes::SERVER_ERROR;

            throw new \Exception;
        }

        $data_to_save['payment_service_id'] = $paymentService->id;
        $data_to_save['goods'] = $this->cart;
        $data_to_save['email'] = $this->customer;
        $data_to_save['status'] = Payment::STATUS_REQUESTED;
        $data_to_save['uuid'] = (string) Str::uuid();
        $data_to_save['total_sum'] = $price_integer . '.' . $price_decimal;
        $data_to_save['total_sum_rub'] = $price_integer . '.' . $price_decimal;
        $data_to_save['additional_data'] = [
            'amount' => $this->convert($data_to_save['total_sum'] , 'RUB', $this->currency),
            'currency' => $this->currency
        ];
        $data_to_save['expires_at'] = Carbon::now()->addMinutes(20);

        try {
            DB::beginTransaction();

            $payment = $this->store($data_to_save);

            foreach ($this->cart as $good) {
                $payment->goods()->attach($good['id'], ['quantity' => $good['quantity']]);
                $this->goodRepository
                    ->reserveGood($good['id'], $good['quantity']);
            }

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            $this->log
                ->error('PAYMENT CREATE ERROR: ' . $e->getMessage());

            $this->errors['error_code'] = HttpExceptionCodes::SERVER_ERROR;

            throw new \Exception;

        }

        return $payment;

    }

    public function getPaymentAddress($paymentId)
    {
        if (!$paymentId) {
            return response()->json([
                'status' => 'fail'
            ], 404);
        }

        $this->orderIdField = 'uuid';

        $payment = $this->getPayment($paymentId);

        if (!$payment or $payment->status != Payment::STATUS_REQUESTED) {
            return response()->json([
                'status' => 'fail'
            ], 404);
        }

        $additional_data =  $payment->additional_data;
        if (!isset($additional_data['old_addresses'])) {
            $additional_data['old_addresses'] = [];
        }

        if (count($additional_data['old_addresses']) >= 2) {

            $payment->status = Payment::STATUS_EXPIRED;
            $payment->save();

            return response()->json([
                'status' => 'fail'
            ], 404);
        }

        $walletId = $this->getWallet($payment->additional_data['currency']);

        if (!$walletId) {
            return response()->json([
                'status' => 'fail'
            ], 404);
        }

        $service = new Bitaps( $payment->additional_data['currency']);
        $data = $service->createPaymentAddress($walletId);

        if (isset($data['error'])) {
            return response()->json([
                'error_message' => 'Невозможно получить адрес'
            ], 500);
        }

        $additional_data['old_addresses'][] =  $additional_data['address'];

        $additional_data['address'] = $data['address'];
        $payment->additional_data = $additional_data;
        $payment->expires_at = Carbon::now()->addMinutes(20);
        $payment->external_id = $data['payment_code'];
        $payment->invoice = $data['invoice'];

        $payment->save();

        return response()->json([
            'address' => $data['address'],
            'expires_at' => Carbon::now()->lessThan(Carbon::parse($payment->expires_at))
                ? Carbon::parse($payment->expires_at)->diffInSeconds()
                : 0,
        ], 200);

    }

}
