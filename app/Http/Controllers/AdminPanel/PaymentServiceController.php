<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPanel\PaymentService\PaymentServiceAllRequest;
use App\Http\Requests\AdminPanel\PaymentService\PaymentServiceCreateRequest;
use App\Http\Requests\AdminPanel\PaymentService\PaymentServiceUpdateRequest;
use App\Http\Resources\AdminPanel\PaymentService\CreatePaymentServiceResource;
use App\Services\PaymentSettingsService;
use Illuminate\Http\JsonResponse;

class PaymentServiceController extends Controller
{
    /**
     * @var PaymentSettingsService
     */
    public PaymentSettingsService $paymentService;

    /**
     * PaymentServiceController constructor.
     *
     * @param PaymentSettingsService $paymentService
     */
    public function __construct(PaymentSettingsService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param PaymentServiceAllRequest $request
     * @return JsonResponse
     */
    public function index(PaymentServiceAllRequest $request): JsonResponse
    {
        $limit = $request->get('limit');

        $data = $this
            ->paymentService
            ->getPaymentSettingsList($limit);

        return response()
            ->json([
                'data' => CreatePaymentServiceResource::collection($data),
                'count' => $data->total()
            ], JsonResponse::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $data = $this
            ->paymentService
            ->getOneRecord($id);

        return response()
            ->json(['data' => CreatePaymentServiceResource::make($data)], JsonResponse::HTTP_OK);
    }

    public function store(PaymentServiceCreateRequest $request): JsonResponse
    {
        $data = $this
            ->paymentService
            ->createPaymentServiceSetting($request->all());

        return response()->json(['data' => CreatePaymentServiceResource::make($data)], JsonResponse::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param PaymentServiceUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(PaymentServiceUpdateRequest $request, int $id): JsonResponse
    {
        $data = $this
            ->paymentService
            ->updatePaymentSetting($id, $request->all());

        return response()->json(['data' => CreatePaymentServiceResource::make($data)], JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $data = $this
            ->paymentService
            ->destroyOnePaymentSetting($id);

        return response()->json($data, JsonResponse::HTTP_OK);
    }

    /**
     * Return list of payment services
     *
     * @return JsonResponse
     */
    public function paymentServiceList(): JsonResponse
    {
        $data = $this->paymentService
            ->getListOfServices();

        return response()->json(['data' => $data], JsonResponse::HTTP_OK);
    }
}
