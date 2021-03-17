<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserChangedData extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $data;

    /**
     * UserChangedData email constructor.
     *
     * @param string $name
     * @param array $data
     */
    public function __construct(string $name, array $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user-changedData')
            ->subject("Ваши данные были изменены")
            ->with([
                'name' => $this->name,
                'data' => $this->data,
            ]);
    }
}
