<?php

namespace App\Services\Payment;

use Curl\Curl;
use Illuminate\Support\Facades\Log;

class Bitaps
{
    protected $httpService;

    protected $log;

    protected $url;

    protected $confirmations;

    protected $callbackUrl;

    public function __construct(string $currency)
    {
        $this->url = str_replace('${currency}', $currency, config('payment.bitaps.url'));
        $this->confirmations = config('payment.bitaps.confirmations.' . $currency);
        $this->httpService = new Curl();
        $this->log = Log::channel('payment_bitaps');
        $this->callbackUrl = config('app.url') . "/api/payment/blockchain/callback";
    }

    protected function sendRequest($uri, $method = 'GET', $body = [])
    {
        $url = $this->url . $uri;

        $this->log->info('REQUEST: ' . $url . "\n" . json_encode($body ));
//        $this->log->info('REQUEST: ' . $url . "\n" . $body);

        switch ($method) {
        case 'GET':
            $this->httpService->get($url);
            break;
        case 'POST':
            $this->httpService->setHeader('Content-Type', 'application/json;charset=UTF-8');
            $this->httpService->post($url, json_encode($body));
            break;
        default:
            throw new \Exception('Not supported method '.$method.'.');
        }

        $this->log->info("RESPONSE: " . $this->httpService->response);

        if (true === $this->httpService->error) {

            $this->log->error("RESPONSE: " . $this->httpService->error_message);

            if (false === empty($this->httpService->response)) {

                $json = json_decode($this->httpService->response, true);

                if (null === $json) {

                    return [
                        'error' => true,
                        'error_message' => $this->httpService->error_message,
                        'data' => json_decode($this->httpService->response)
                    ];
                }

                if (true === isset($json['error_code'])) {

                    return [
                        'error' => true,
                        'error_message' => isset($json['message']) ? $json['message'] : $json['error_code'],
                        'data' => json_decode($this->httpService->response)
                    ];

                }

                return $json;
            }

            return [
                'error' => true,
                'error_message' => $this->httpService->error_message,
                'data' => json_decode($this->httpService->response)
            ];
        }


        return json_decode($this->httpService->response, true);

    }

    public function createPaymentAddress(string $walletId)
    {
        $data = [
            'wallet_id' => $walletId,
            'callback_link' => $this->callbackUrl,
            'confirmations' =>  $this->confirmations
        ];

        return $this->sendRequest('/create/wallet/payment/address', 'POST', $data);
    }

    public function sendPayment(string $address, string $amount, string $walletId)
    {
        $data = [
            'receivers_list' => [
                [
                    'address' => $address,
                    'amount' => number_format($amount, 0, "", ""),

                ]
            ]
        ];

        return $this->sendRequest("/wallet/send/payment/{$walletId}", 'POST', $data);
    }

    public function createForwardingAddress(string $address)
    {
        $data = [
            'forwarding_address' => $address,
            'callback_url' => $this->callbackUrl,
            'confirmations' => $this->confirmations
        ];

        return $this->sendRequest('/create/payment/address', 'POST', $data);
    }


    public function getListWalletAddressTransactions(string $walletId, string $address)
    {
        // dd($walletId);

        return $this->sendRequest("/wallet/address/transactions/{$walletId}/{$address}");
    }

    public function walletInfo(string $walletId)
    {
        return $this->sendRequest("/wallet/state/{$walletId}");
    }

}
