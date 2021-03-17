<?php

namespace App\Console\Commands;

use App\Mail\GoodSending;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GoodsDeliver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:deliver {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orderId = $this->argument('id');

        if (!$orderId) {
            $this->info('Order #' . $orderId . ' does not exists');
            return;
        }

        $order = Payment::find($orderId);

        $zip_file = (storage_path('app/public' . $order->zip_path));

        if (!file_exists($zip_file)) {
            $this->info('Files for order #' . $orderId . ' do not exists');
            return;
        };

        Mail::queue(new GoodSending($zip_file, $order), config('queue.queue_title.mail'));

        $this->info('Files for order #' . $orderId . ' were sent successfully!');
    }
}
