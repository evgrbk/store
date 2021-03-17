<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearOrderFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:clear-files';

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
        \Log::info('Clear Order Files task start');

        $orders = Payment::query()->where('created_at', '>=', Carbon::now()->subDays(2))->get();

        foreach($orders as $order)
        {
            if (!$order->zip_path) {
                continue;
            }

            $zip_file = (storage_path('app/public' . $order->zip_path));

            if (!file_exists($zip_file)) {
                continue;
            };

            if (unlink($zip_file)) {
                $order->zip_path = null;
                $order->save();
            }
        }

        \Log::info('Clear Order Files task finish successfull');
    }
}
