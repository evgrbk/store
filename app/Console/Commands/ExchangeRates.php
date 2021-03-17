<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Services\ExchangeRatesApiService;
use Illuminate\Console\Command;

class ExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rate:update';

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

        \Log::info('Update exchange rate start');

        $currencies = Currency::all();

        $base = 'RUB';

        foreach($currencies as $currency) {

            $this->info("Get rate for {$currency->currency_title}");

            $req_url = "https://api.exchangerate.host/latest?base={$currency->currency_title}&symbols={$base}";
            $response_json = file_get_contents($req_url);

            if(false !== $response_json) {
                try {
                    $response = json_decode($response_json);
                    if($response->success === true) {
                        $rate = $response->rates->{$base};

                        $this->info("{$currency->currency_title} = {$rate} {$base}");

                        list($integer_part, $decimal_part) = explode('.', $rate);

                        $currency->integer_part = $integer_part;
                        $currency->decimal_part = $decimal_part;
                        $currency->status = 'SUCCESS';
                        $currency->date = $response->date;
                        $currency->save();

                        $this->info("Success updatet {$currency->currency_title}");

                    }

                } catch(Exception $e) {
                    \Log::info('Update exchange rate finish error');
                    $this->error("Fail updatet {$currency->currency_title}");
                    $currency->status = 'FAIL';
                    $currency->save();
                }
            }

        }

        \Log::info('Update exchange rate finish successfull');

    }
}
