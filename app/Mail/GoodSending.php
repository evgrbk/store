<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GoodSending extends Mailable
{
    use Queueable, SerializesModels;

    private string $files;

    private $payment;

    private $email;

    /**
     * Create a new message instance.
     *
     * @param string $files
     * @param Payment $payment
     */
    public function __construct(string $files, Payment $payment, $email = null)
    {
        $this->files = $files;
        $this->payment = $payment;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->markdown('emails.good-sending')
            ->subject("Спасибо за покупку")
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->to($this->email ?? $this->payment->email)
            ->with([
                    'goods' => $this->payment->goods,
                    'orderID' => $this->payment->id,
                    'orderPrice' => $this->payment->total_sum,
                ])
            ->attach($this->files, [
                'as' => 'files.zip',
                'mime' => 'application/zip'
            ]);

        return $email;
    }
}
