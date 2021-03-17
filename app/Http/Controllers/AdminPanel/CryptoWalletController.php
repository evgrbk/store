<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Services\Payment\Bitaps;
use App\Services\Payment\BitapsPaymentService;
use Illuminate\Http\Request;

class CryptoWalletController extends Controller
{
    protected $service;

    public function __construct(BitapsPaymentService $bitapsPaymentService){
        $this->service = $bitapsPaymentService;
    }

    public function info(Request $request, string $currency)
    {
        $walletId = config("payment.bitaps.wallet.{$currency}");
        $service = new Bitaps($currency);
        $data = $service->walletInfo($walletId);

        if (isset($data['error'])) {
            return response()->json([
                'status' => 'fail'
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'balance_amount' => $data['balance_amount'] / $this->minCryptoAmount($currency),
            'address_count' => $data['address_count'],
            'wallet_id' => isset($data['wallet_id']) ? $data['wallet_id'] : null
        ], 200);

    }

    public function payout(Request $request, string $currency)
    {
        $walletId = config("payment.bitaps.wallet.{$currency}");
        $service = new Bitaps($currency);

        try {

            $amount = $this->getAvailableAmount($request->amount , $currency);

            $amount = $amount * $this->minCryptoAmount($currency);

            if ($amount < 0) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Недостаточно средств',
                ]);
            }

            $address = config("payment.wallet.address.{$currency}");

            $data = $service->sendPayment($address, $amount, $walletId);

        } catch (\Exception $e) {

            \Log::error($e);

            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ]);

        }

        if (isset($data['error'])) {
            return response()->json([
                'status' => 'fail',
                'message' => $data['error_message']
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'На адрес ' . $data['tx_list'][0]['address'] . ' отправлено  ' . $data['tx_list'][0]['amount'] . ' ' . $currency
        ]);

    }

    protected function getAvailableAmount($amount, $currency)
    {
        $fee = config("payment.bitaps.fee.{$currency}");

        $amount = $amount - $fee;

        return round($amount, config('payment.bitaps.round.' . strtolower($currency), 8));

    }

    protected function minCryptoAmount(string $currency)
    {
        $values = [
            'btc' => 100000000,
            'eth' => 1000000000000000000
        ];

        if (!isset($values[$currency])) {
            throw new \Exception('Unknown currency');
        }

        return $values[$currency];

    }
}
