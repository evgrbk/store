<?php

namespace App\Services\Payment;

use App\Models\Good;
use App\Models\Payment;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class QiwiPaymentService extends PaymentService
{
    protected $provider = 'qiwi';

    public function createPayment(): array
    {
        try {
            $order = $this->createOrder();
        } catch (\Exception $e) {
            return $this->getErrors();
        }

        return $this->getUrl($order);
    }

    public function callbackHandler()
    {
        $data = $this->request->all();

        $this->log->info('CALLBACK_REQUEST: ' . json_encode($data));

        $service = new Qiwi($this->getSetting('secret_key'));

        if ($this->request->header('X-Api-Signature-SHA256') != $service->getAuthHash($data)) {

            $this->log->error('CALLBACK_RESPONSE: UNAUTHORIZED REQUEST WIDTH HASH=' . $this->request->header('X-Api-Signature-SHA256'));

            return response()->json(
                [], JsonResponse::HTTP_UNAUTHORIZED);
        };

        $payment = $this->getPayment($data['bill']['billId']);

        if (!$payment) {
            $this->log->info('CALLBACK_RESPONSE: no payment with UUID=' . (int)$this->request->get('bill.billId'));
            return $this->errorResponse();
        }

        if ($payment->status == Payment::STATUS_SUCCEEDED) {
            return $this->successResponse();
        }

        $payment->update([
            'status' => Payment::STATUS_SUCCEEDED,
            'raw' => $data,
            'external_status' => $data['bill']['status']['value']
        ]);

        $this->succesProcessPayment($payment);

        return $this->successResponse();
    }

    protected function getUrl(payment $payment): array
    {
        try {

            $key = $this->getSetting('secret_key');

            if (!$key) {
                throw new \Exception('Оплата Qiwi временно не доступна.');
            }

            $billPayments = new Qiwi($key);
            
            $fields = [
              'amount' => $payment->total_sum,
              'currency' => 'RUB',
              'expirationDateTime' => (Carbon::now()->addDay())->toAtomString()
            ];

            $response = $billPayments->createBill($payment->uuid, $fields);

            if (isset($response['error'])) {
                throw new \Exception($response['error_message']);
            }

        } catch (\Exception $e) {

            $this->log->error('CREATE PAYMENT: ' . $e->getMessage());
            foreach ($payment->goods as $good) {
                $this->goodRepository
                    ->unreserveGood($good->id, $good->pivot->quantity);
            }

            $payment->status = Payment::STATUS_FAILED;
            $payment->raw = $response['data'] ?? $e->getMessage() ?? null;
            $payment->save();

            return [
                'error' => true,
                'error_code' => 409,
                'error_message' => $e->getMessage()
            ];
        }

        $payment->external_status = $response['status']['value'];
        $payment->raw = $response;
        $payment->save();

        return [
            'link' => $response['payUrl'] . '&successUrl=' . config('app.front_url') . '/payment/success'
        ];

    }

    public function errorResponse()
    {
        return response()
            ->json('fail', JsonResponse::HTTP_BAD_REQUEST);

    }

    public function successResponse()
    {
        return response()
            ->json('ok', JsonResponse::HTTP_OK);
    }
}
