<?php

namespace App\Console\Commands;

use App\Repositories\CurrencyRateRepository;
use Illuminate\Console\Command;
use App\Services\ExchangeRatesApiService;
use App\Services\CurrencyService;

class CurrenciesRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currencies:rates {--no-limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates currency rates';

    /**
     * @var ExchangeRatesApiService
     */
    private ExchangeRatesApiService $exchangeRatesApiService;

    /**
     * @var CurrencyService
     */
    private CurrencyService $currencyService;

    /**
     * @var CurrencyRateRepository
     */
    private $currencyRateRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->currencyService = app(CurrencyService::class);
        $this->exchangeRatesApiService = new ExchangeRatesApiService();
        $this->currencyRateRepository = new CurrencyRateRepository();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $allRates = $this->currencyRateRepository
            ->withCurrency()
            ->auto();

        if (!$this->option('no-limit')) {
            $allRates = $allRates->where(function ($query) {
                $query->whereNull('rate_updated_at')
                    ->orWhereRaw('DATEDIFF(NOW(), rate_updated_at) >= `interval`');
            });
        }

        $allRates = $allRates->get();

        if (!count($allRates)) {
            return 0;
        }

        $codes = $allRates->pluck('currency.code')->toArray();

        //Getting lastest rates
        $base = $this->currencyService->getBaseCode();
        //Delete base from codes
        if (($key = array_search($base, $codes)) !== false) {
            unset($codes[$key]);
        }

        if ($base && ($exchangeRates = $this->exchangeRatesApiService->getLatest($base, $codes))) {
            foreach ($allRates as $rate) {
                if (isset($exchangeRates['rates'][$rate->currency->code])) {
                    $data['rate'] = 1 / $exchangeRates['rates'][$rate->currency->code];
                    if (!$this->option('no-limit')) {
                        $diffPercent = abs((1 - $rate->rate / $data['rate']) * 100);
                        if ($diffPercent >= $rate->limit) {
                            $this->currencyRateRepository
                                ->updateWithoutTimestamps($data, $rate->id);
                        }
                    } else {
                        $this->currencyRateRepository
                            ->updateWithoutTimestamps($data, $rate->id);
                    }
                }
                if ($rate->currency->code == $base) {
                    $data['rate'] = 1.0000;
                    $this->currencyRateRepository
                        ->updateWithoutTimestamps($data, $rate->id);
                }
            }
        }
        return 0;
    }
}
