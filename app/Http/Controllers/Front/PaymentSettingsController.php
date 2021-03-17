<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPanel\PaymentService\CreatePaymentServiceResource;
use App\Models\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{

    public function blockchainSettings(): JsonResponse
    {
    	$data = PaymentService::where('service_title', PaymentService::BLOCKCHAIN_SERVICE)->first();
       
        return response()
            ->json(CreatePaymentServiceResource::make($data), JsonResponse::HTTP_OK);
    }

    public function activeServices(): JsonResponse
    {
    	$services = PaymentService::where('active', 1)->pluck('service_title');

        $data = PaymentService::where('service_title', PaymentService::BLOCKCHAIN_SERVICE)->first();

    	return response()
            ->json([
                'services' => $services,
                'blockchainSettings' => CreatePaymentServiceResource::make($data)
            ], JsonResponse::HTTP_OK);
    }
}
