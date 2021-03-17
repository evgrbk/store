<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserLoginPass extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    private $data;

    /**
     * UserLoginPass email constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user-loginpass')
            ->subject("Ваши данные для входа")
            ->with([
                'name' => $this->data['name'],
                'url' => $this->data['url'],
                'login' => $this->data['login'],
                'pass' => $this->data['pass']
            ]);
    }
}
