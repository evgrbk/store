<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Mail\GoodSending;
use App\Models\Good;
use App\Models\Media;
use App\Models\Payment;
use App\Services\Payment\PaymentProvider;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        Payment::query()->where('id', $id)->delete();

        return response()->json([], 204);
    }

    public function sentGoods(Request $request, int $id)
    {
        $payment = Payment::query()->find($id);

        $zip = storage_path('app/public/' . $payment->zip_path);

        if ($payment->zip_path  and file_exists($zip)) {

            Mail::queue(new GoodSending($zip, $payment, $request->email), config('queue.queue_title.mail'));

            return response()->json('ok', 200);
        }

        $providerClass = new PaymentProvider();

        $provider  = $providerClass->makeProvider($payment->payment->service_title);

        $provider->succesProcessPayment($payment, $request->get('email'));

        return response()->json('ok', 200);

    }
}
