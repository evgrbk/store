<?php 

namespace App\Services\Payment;

use Curl\Curl;
use Illuminate\Support\Facades\Log;

class Qiwi
{
    const BILLS_URI = 'https://api.qiwi.com/partner/bill/v1/';

    private $secretKey;

    protected $httpService;

    protected $log;

    public function __construct(string $secretKey)
    {   
        $this->secretKey = $secretKey;
        $this->httpService = new Curl();
        $this->log = Log::channel('payment_qiwi');
    }

    public function createBill(string $billId, array $params)
    {

        $data = [
            'amount' => [
                'currency' => $params['currency'],
                'value' => $params['amount']
            ],
            'expirationDateTime' => $params['expirationDateTime'],
        ];

        return $this->sendRequest('/bills/' . $billId, 'PUT', $data);
    }

    protected function sendRequest($uri, $method = 'GET', $body = [])
    {
        $url = self::BILLS_URI.$uri;
      
        $this->log->info('REQUEST: ' . $url . "\n\r" . json_encode($body));

        $this->httpService->setHeader('Accept', 'application/json');
        $this->httpService->setHeader('Authorization', 'Bearer '. $this->secretKey);
        switch ($method) {
        case 'GET':
            $this->httpService->get($url);
            break;
        case 'POST':
            $this->httpService->setHeader('Content-Type', 'application/json;charset=UTF-8');
            $this->httpService->post($url, json_encode($body, JSON_UNESCAPED_UNICODE));
            break;
        case 'PUT':
            $this->httpService->setHeader('Content-Type', 'application/json;charset=UTF-8');
            $this->httpService->put($url, json_encode($body, JSON_UNESCAPED_UNICODE), true);
            break;
        default:
            throw new \Exception('Not supported method '.$method.'.');
        }

        if (true === $this->httpService->error) {

            $this->log->error($this->httpService->error_message . "\n\rRESPONSE: " . $this->httpService->response);

            return [
                'error' => true,
                'error_message' => $this->httpService->error_message,
                'data' => json_decode($this->httpService->response)
            ];
            
        }

        if (false === empty($this->httpService->response)) {
            
            $json = json_decode($this->httpService->response, true);

            $this->log->info("RESPONSE: " . $this->httpService->response);

            if (null === $json) {

                return [
                    'error' => true,
                    'error_message' => $this->httpService->error_message,
                    'data' => json_decode($this->httpService->response)
                ];
            }

            if (true === isset($json['errorCode'])) {

                return [
                    'error' => true,
                    'error_message' => isset($json['description']) ? $json['description'] : $json['errorCode'],
                    'data' => json_decode($this->httpService->response)
                ];
                    
            } 

            return $json;
        }

        return false;

    }

    public function getAuthHash(array $body)
    {
        if (app()->environment() != 'production') {
            return true;
        };
        
        $invoiceParameters = implode('|', [
            (string)$body['bill']['amount']['currency'],
            $this->normalizeAmount($body['bill']['amount']['value']),
            (string)$body['bill']['billId'],
            (string)$body['bill']['siteId'],
            (string)$body['bill']['status']['value']
        ]);

        return hash_hmac("sha256", $invoiceParameters, $this->secretKey);        
    }

    public function normalizeAmount($amount=0)
    {
        return number_format(round(floatval($amount), 2, PHP_ROUND_HALF_DOWN), 2, '.', '');

    }
}