<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\QiwiCallbackRequest;
use App\Http\Requests\Payment\SavePaymentRequest;
use App\Models\PaymentService as PaymentServiceModel;
use App\Services\PaymentService;
use App\Services\Payment\PaymentProvider;
use App\Services\Payment\Qiwi;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * @var PaymentProvider
     */
    private PaymentProvider $paymentProvider;

    private $log;

    /**
     * QiwiController constructor.
     * @param PaymentProvider $paymentProvider
     */
    public function __construct(PaymentProvider $paymentProvider)
    {
        $this->paymentProvider = $paymentProvider;
        $this->log = Log::channel('payment_qiwi');
    }

    /**
     * Save Payment
     *
     * @param SavePaymentRequest $request
     * @return JsonResponse
     */
    public function savePayment(SavePaymentRequest $request): JsonResponse
    {
        $data = $request->all();

        $service = $this->paymentProvider->makeProvider($data['payment_service']);

        $data = $service
            ->setCart($request->cart)
            ->setCurrency($request->currency)
            ->setCustomer($request->email)
            ->createPayment();

        if (isset($data['error_code'])) {
            return response()
                ->json(['data' => $data], JsonResponse::HTTP_CONFLICT);
        }

        return response()
            ->json(['data' => $data], JsonResponse::HTTP_OK);
    }

    public function callback(Request $request, string $provider)
    {
        $service = $this->paymentProvider->makeProvider($provider);

        return $service
            ->setRequest($request)
            ->callbackHandler();

    }

    public function getPaymentData(Request $request, string $provider)
    {
        $service = $this->paymentProvider->makeProvider($provider);

        return $service
            ->getPaymentData($request->orderId);
    }

    public function getAddress(Request $request)
    {
        $service = $this->paymentProvider->makeProvider(PaymentServiceModel::BLOCKCHAIN_SERVICE);

        return $service
            ->getPaymentAddress($request->orderId);
    }


}
