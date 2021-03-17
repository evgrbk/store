<?php

namespace App\Services\Payment;

use App\Models\Good;
use App\Models\Payment;
use App\Services\PaymentService;

class BitcoinPaymentService extends PaymentService
{
    protected $provider = 'bitcoin';

    protected $orderIdField = 'id';

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
        if (!$this->checkIp($this->request->ip())) {
            return;
        }

        if (!isset($this->request->m_operation_id) or !isset($this->request->m_sign)) {
            return;
        }

        if (!$this->checkSign($this->request->all())) {
            $this->log->error('CALLBACK_RESPONSE: UNAUTHORIZED REQUEST WIDTH SIGN=' . $data['m_sogn']);
            return $this->errorResponse();
        }

        $payment = $this->getPayment($this->request->get('m_orderid'));

        if (!$payment) {
            $this->log->error('CALLBACK_RESPONSE: PAYMENT NOT FOUND');
            return $this->errorResponse();
        }

        if ($payment->status == Payment::STATUS_SUCCEEDED) {
            return $this->successResponse();
        }

        if ($payment->status == Payment::STATUS_FAILED) {
            return $this->successResponse();
        }

        if ($this->request->get('m_status') != 'success') {
            $this->log->info('CALLBACK_RESPONSE: STATUS NOT SUCCESS');
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'raw' => $this->request->all(),
                'external_status' => $this->request->get('m_status')
            ]);

            $this->failProcessPayment($payment);
            return $this->errorResponse();
        }

        $payment->update([
            'status' => Payment::STATUS_SUCCEEDED,
            'raw' => $this->request->all(),
            'external_status' => $this->request->get('m_status')
        ]);

        $this->succesProcessPayment($payment);

        return $this->successResponse();
    }

    protected function getUrl(Payment $payment): array
    {
        try {

            $data = [
                'm_shop' => $this->getSetting('shop_id'),
                'm_orderid' => $payment->uuid,
                'm_amount' => $this->normalizeAmount($payment->total_sum),
                'm_curr' => 'RUB',
                'm_desc' => base64_encode('Оплата электронных товаров')
            ];

            $data['m_sign'] = $this->makeSign($data);

        } catch (\Exception $e) {

            $this->log->error('CREATE PAYMENT: ' . $e->getMessage());

            foreach ($payment->goods as $good) {
                $this->goodRepository
                    ->unreserveGood($good->id, $good->pivot->quantity);

            }

            $payment->status = Payment::STATUS_FAILED;
            $payment->raw =  $e->getMessage() ?? null;
            $payment->save();

            return [
                'error' => true,
                'error_code' => 409,
                'error_message' => $e->getMessage()
            ];
        }

        return [
            'link' =>'https://payeer.com/merchant/?' . http_build_query($data)
        ];
    }

    protected function makeSign(array $data): string
    {
        $data['m_key'] = $this->getSetting('secret_key'); //'dark';

        return strtoupper(hash('sha256', implode(':', $data)));
    }

    public function checkIp(string $ip): bool
    {
        if (app()->environment() != 'production') {
            return true;
        };

        return in_array($ip, config('payment.payeer.ips'));
    }

    protected function checkSign(array $data): bool
    {
        if (app()->environment() != 'production') {
            return true;
        };

        $arrHash = [
            $data['m_operation_id'],
            $data['m_operation_ps'],
            $data['m_operation_date'],
            $data['m_operation_pay_date'],
            $data['m_shop'],
            $data['m_orderid'],
            $data['m_amount'],
            $data['m_curr'],
            $data['m_desc'],
            $data['m_status'],
        ];

        if (isset($data['m_params'])) {
            $arrHash[] = $data['m_params'];
        }

        $arrHash[] = $this->getSetting('secret_key');

        $sign = strtoupper(hash('sha256', implode(':', $arrHash)));

        return $sign === $data['m_sign'];
    }

    protected function errorResponse(): string
    {
        return $this->request->m_orderid . '|error';
    }

    protected function successResponse(): string
    {
        return $this->request->m_orderid . '|success';
    }
}
