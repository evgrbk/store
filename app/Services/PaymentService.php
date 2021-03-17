<?php

namespace App\Services;

use App\Exceptions\HttpExceptionCodes;
use App\Mail\GoodSending;
use App\Models\Good;
use App\Models\Media;
use App\Models\Payment;
use App\Models\PaymentService as PaymentServiceModel;
use App\Repositories\GoodRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentServiceRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use ZanySoft\Zip\Zip;

abstract class PaymentService extends Service implements PaymentServiceInterface
{
    /**
     * @var PaymentServiceRepository
     */
    protected PaymentServiceRepository $paymentServiceRepository;

    /**
     * @var GoodRepository
     */
    protected GoodRepository $goodRepository;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $log;

    /**
     * @var CurlService
     */
    protected CurlService $curlService;

    protected $cart;

    protected $customer;

    protected $errors = [];

    protected $request;

    protected $settings;

    protected $currency;

    protected $orderIdField = 'uuid';
    /**
     * @var UploaderService
     */
    private UploaderService $uploaderService;

    /**
     * QiwiService constructor.
     * @param PaymentRepository $paymentRepository
     * @param PaymentServiceRepository $paymentServiceRepository
     * @param GoodRepository $goodRepository
     * @param CurlService $curlService
     * @param UploaderService $uploaderService
     */
    public function __construct(
        PaymentRepository $paymentRepository,
        PaymentServiceRepository $paymentServiceRepository,
        GoodRepository $goodRepository,
        CurlService $curlService,
        UploaderService $uploaderService
    ) {
        $this->repository = $paymentRepository;
        $this->paymentServiceRepository = $paymentServiceRepository;
        $this->goodRepository = $goodRepository;
        $this->curlService = $curlService;
        $this->uploaderService = $uploaderService;

        $this->settings = $this->getSettings($this->provider);

        $this->log = Log::channel('payment_' . $this->provider);
    }

    /**
     * Save Payment
     *
     * @param array $data
     * @return array
     */
    abstract public function createPayment(): array;

    abstract function callbackHandler();

    public function setCart(array $cart)
    {
        $this->cart = $cart;

        return $this;
    }

    public function setCurrency(string $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    protected function getSettings(string $provider)
    {
        $paymentService = $this->paymentServiceRepository
            ->getWhere('service_title', $provider)
            ->first();

        return $paymentService ? $paymentService->data : [];
    }

    protected function getSetting(string $key)
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : null;
    }

    public function getErrors()
    {
        return $this->errors;
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

        try {
            DB::beginTransaction();

            $payment = $this->store($data_to_save);

            foreach ($this->cart as $good) {
                $payment->goods()->attach($good['id'], ['quantity' => $good['quantity']]);
                    $this->goodRepository
                        ->reserveGood($good['id'], $good['quantity']);
            }

            DB::commit();
        } catch (Exception $e) {

            DB::rollBack();

            $this->log
                ->error('PAYMENT CREATE ERROR: ' . $e->getMessage());

            $this->errors['error_code'] = HttpExceptionCodes::SERVER_ERROR;

            throw new \Exception;

        }

        return $payment;

    }

    /**
     * @param string $paymentId
     * @return Payment|null
     */
    protected function getPayment(string $paymentId)
    {
        $query = $this->repository
            ->getOrdersQuery();
        $query = $this->repository
            ->setFilterByField($query, $this->orderIdField, $paymentId);
        $query = $this->repository
            ->getOrdersWith($query, ['goods.files']);
        return $this->repository
            ->getFirst($query);

    }

    public function normalizeAmount($amount=0)
    {
        return number_format(round(floatval($amount), 2, PHP_ROUND_HALF_DOWN), 2, '.', '');
    }

    public function succesProcessPayment($payment, $email = null)
    {
        $zipFileName = sprintf('order_%s_%s.zip', $payment->id, date('d-m-Y-H-i'));

        $zip_file = storage_path('app/public/uploader/' . $zipFileName);

        $zip = new \ZipArchive();
        $zip->open($zip_file,  \ZipArchive::CREATE);

        $fileIds = [];

        foreach ($payment->goods as $index => $good) {

            for($i=0; $i < $good->pivot['quantity']; $i++) {
                $file = $good->good_type === Good::TYPE_LIMITED ? $good->files[$i] : $good->files[0];

                if ($good->good_type === Good::TYPE_LIMITED) {
                    if (!isset($fileIds[$good->id])) {
                        $fileIds[$good->id] = [];
                    }
                    $fileIds[$good->id][] = $file->id;
                }

                $file_path = ''
                    . config('media.directory_path')
                    . '/'
                    . $file->id
                    . '/'
                    . $file->file_name;

                $zip->addFile(storage_path('app/public/' . $file_path), $file->file_name);
            }
        }

        $zip->close();

        $payment->zip_path = '/uploader/' . $zipFileName;
        $payment->save();

        Mail::queue(new GoodSending($zip_file, $payment, $email), config('queue.queue_title.mail'));

        $goods = $payment->goods()->get();

        foreach ($goods as $good) {
            if ($good->good_type != Good::TYPE_LIMITED) {
                continue;
            }

            //delete files from limited goods
            foreach($fileIds[$good->id] as $id) {
                $this->uploaderService
                    ->deleteFile($good, Media::FILE_TYPE, $id);
            }

            $good->count_reserved = $good->count_reserved - $good->pivot['quantity'];
            $good->good_left = $good->good_left - $good->pivot['quantity'];
            $good->save();
        }

//        Storage::delete($zip_file);
    }

    protected function failProcessPayment($payment)
    {
        $goods = $payment->goods()->get();

        foreach ($goods as $good) {
            $this->goodRepository
                ->unreserveGood($good['id'], $good->pivot['quantity']);
        }
    }
}
