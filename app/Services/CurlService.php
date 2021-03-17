<?php

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * ATTENTION! THIS IS A THIRD PARTY CLASS
 * CODE STYLE OF THIS CLASS CAN DIFFER FROM YOUR PROJECT CODE STYLE
 *
 * Class CurlService
 * @package App\SharedServices
 */
class CurlService
{
    /**
     * @var string
     */
    public const REQUEST_TYPE_POST = 'POST';

    /**
     * @var string
     */
    public const REQUEST_TYPE_PUT = 'PUT';

    /**
     * @var string
     */
    public const REQUEST_TYPE_GET = 'GET';

    /**
     * @var string
     */
    public const REQUEST_TYPE_DELETE = 'DELETE';

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $proxy;

    /**
     * @var string
     */
    private $proxy_login;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var bool
     */
    private $curl_error = false;

    /**
     * @var string
     */
    private $curl_error_text;

    /**
     * @var string
     */
    private $request_type;

    /**
     * @var array
     */
    private $post_fields;

    /**
     * @var array
     */
    private $get_fields;

    /**
     * @var array
     */
    private $headers;

    /**
     * @return string
     */
    public function initCurl(): string
    {
        // -------------------------
        // gather curl

        // to debug curl
        // you should create the directory and file first!
        if ($this->debug) {
            $fp = fopen(storage_path().'/logs/curl/curl.log', 'a');
            fwrite($fp,
                '-----------------------'
                . PHP_EOL
                . 'local.INFO: START ' . Carbon::now()->toDateTimeString()
                . PHP_EOL
            );
            fclose($fp);
        }

        $curlopt_array = [
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
        ];

        // to debug
        // you should create the directory and file first!
        if ($this->debug) {
            $curlopt_array[CURLOPT_VERBOSE] = true;
            $curlopt_array[CURLOPT_STDERR] = fopen(storage_path().'/logs/curl/curl.log', 'a');
        }

        // to set proxy login and password
        if ($this->proxy) {
            $curlopt_array[CURLOPT_PROXY] = $this->proxy;
        }

        // to set proxy login and password
        if ($this->proxy_login) {
            $curlopt_array[CURLOPT_PROXYUSERPWD] = $this->proxy_login;
        }

        // to set headers
        if ($this->headers) {
            $curlopt_array[CURLOPT_HTTPHEADER] = $this->headers;
        }

        // to set reqeust type
        switch($this->request_type) {
            case self::REQUEST_TYPE_POST:
                $curlopt_array[CURLOPT_POST] = true;
                break;
            case self::REQUEST_TYPE_PUT:
                $curlopt_array[CURLOPT_CUSTOMREQUEST] = self::REQUEST_TYPE_PUT;
                break;
            case self::REQUEST_TYPE_DELETE:
                $curlopt_array[CURLOPT_CUSTOMREQUEST] = self::REQUEST_TYPE_DELETE;
                break;
        }

        // to set post fields
        if ($this->post_fields) {
            $curlopt_array[CURLOPT_POSTFIELDS] = http_build_query($this->post_fields);
        }

        // to set get fields
        if ($this->get_fields) {
            $curlopt_array[CURLOPT_URL] = $this->url . '?' . http_build_query($this->get_fields);
        }

        // ------------------
        // init curl
        $ch = curl_init();
        curl_setopt_array($ch, $curlopt_array);

        // timeout has to be set right before curl_exec
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if (strlen($error) > 1) {
            $this->curl_error = true;
            $this->curl_error_text = $error;
        }

        return $result;
    }

    /**
     * @param string $directory
     * @param string $filename
     * @param bool $xml
     * @return bool
     */
    public function downloadFile(string $directory, string $filename, $xml = false)
    {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_PROXY, $this->proxy);

        if ($xml == true) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }

        // to set proxy login and password
        if ($this->proxy_login) {
            $curlopt_array[CURLOPT_PROXYUSERPWD] = $this->proxy_login;
        }
        // timeout has to be set right before curl_exec
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);

        $result = curl_exec($ch);

        $error = curl_error($ch);
        curl_close($ch);

        if (strlen($error) > 1) {
            $this->curl_error = true;
            $this->curl_error_text = $error;
        }

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        if(file_exists($directory.$filename)){
            unlink($directory.$filename);
        }

        file_put_contents($directory.$filename, $result);

        return filesize($directory.$filename) > 0;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @param string $proxy
     */
    public function setProxy(string $proxy): void
    {
        $this->proxy = $proxy;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @param string $proxy_login
     *
     * example login:password
     */
    public function setProxyLogin(string $proxy_login): void
    {
        $this->proxy_login = $proxy_login;
    }

    /**
     * @param string $request_type
     */
    public function setRequestType(string $request_type): void
    {
        $this->request_type = $request_type;
    }

    /**
     * @param array $post_fields
     */
    public function setPostFields(array $post_fields): void
    {
        $this->post_fields = $post_fields;
    }

    /**
     * @return bool
     */
    public function isCurlError(): bool
    {
        return $this->curl_error;
    }

    /**
     * @return string
     */
    public function getCurlErrorText(): string
    {
        return $this->curl_error_text;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * @param array $get_fields
     */
    public function setGetFields(array $get_fields): void
    {
        $this->get_fields = $get_fields;
    }
}
