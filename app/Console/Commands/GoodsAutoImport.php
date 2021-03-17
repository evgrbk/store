<?php

namespace App\Console\Commands;

use App\Http\Requests\AdminPanel\Good\ImportGoodRequest;
use App\Services\GoodsImport\ParserService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use App\Models\AutoImport;
use App\Models\AutoImportLog;
use Illuminate\Support\Facades\Http;
use Throwable;

class GoodsAutoImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goods:auto-import {schedule}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import goods from auto-import table';

    /**
     * @var ParserService
     */
    private ParserService $parserService;

    /**
     * Create a new command instance.
     *
     * @param ParserService $parserService
     * @return void
     */
    public function __construct(ParserService $parserService)
    {
        parent::__construct();
        $this->parserService = $parserService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $schedule = $this->argument('schedule');
        if (!isset(AutoImport::SCHEDULE_HOURS[$schedule])) {
            $this->error('Schedule not found!');
            return 0;
        }

        $imports = AutoImport::readyToImport($schedule)
            ->get();

        foreach ($imports as $import) {
            $import->status = AutoImport::STATUS_IN_PROGRESS;
            $import->withoutTimestamps()->save();

            try {
                $file = Http::retry(3, 1000)
                    ->timeout(60)
                    ->get($import->url)
                    ->throw()
                    ->body();
            } catch (Throwable $e) {
                report($e);
                $this->importError($import, 'Не удалось загрузить файл. ' . $e->getMessage());
                continue;
            }

            try {
                $response = $this->parserService->import(new ImportGoodRequest([
                    'type' => $import->parser_type,
                    'fields' => Arr::collapse($import->selected_fields)
                ]), $file);
            } catch (Throwable $e) {
                report($e);

                $this->importError($import, 'Ошибка при парсинге. ' . $e->getMessage());
                continue;
            }

            if (optional($response->getData())->status === "ok") {
                $this->importSuccess($import, optional($response->getData())->message);
                continue;
            }

            $this->importError($import, optional($response->getData())->message);
        }
        return 0;
    }

    /**
     * When import error
     *
     * @param $import
     * @param $message
     */
    public function importError($import, $message)
    {
        $import->status = AutoImport::STATUS_ERROR;
        $import->withoutTimestamps()->save();

        AutoImportLog::create([
            'auto_import_id' => $import->id,
            'status' => AutoImportLog::STATUS_ERROR,
            'message' => $message ?? 'Неизвестная ошибка'
        ]);
    }

    /**
     * When import success
     *
     * @param $import
     * @param $message
     */
    public function importSuccess($import, $message)
    {
        $import->status = AutoImport::STATUS_PENDING;
        $import->imported_at = now();
        $import->withoutTimestamps()->save();

        AutoImportLog::create([
            'auto_import_id' => $import->id,
            'status' => AutoImportLog::STATUS_OK,
            'message' => $message ?? 'Импорт завершен успешно'
        ]);
    }
}
