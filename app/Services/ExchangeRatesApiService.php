<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class ExchangeRatesApiService
{
    /**
     * ExchangeRatesApiService endpoint
     *
     * @var string $url
     */
    private $url = "https://api.exchangeratesapi.io/";

    /**
     * Send request
     *
     * @param string $url_method
     * @param array $body
     * @return array
     */
    private function sendRequest($url_method, $body = []): ?array
    {
        $url = $this->url . $url_method;

        try {
            return Http::retry(3, 1000)
                ->timeout(2)
                ->get($url, $body)
                ->throw()
                ->json();
        } catch (Throwable $e) {
            report($e);
            return null;
        }
    }

    /**
     * Get latest rates by base
     *
     * @param string $base
     * @param array $symbols
     */
    public function getLatest(string $base, array $symbols)
    {
        $data = [
            'base' => $base,
            'symbols' => implode(',', $symbols),
        ];

        $response = $this->sendRequest('latest', $data);

        return $response;
    }

}
