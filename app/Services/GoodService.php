<?php

namespace App\Services;

use App\Adapters\GoodPriceAdapter;
use App\Adapters\MediaConversionsAdapter;
use App\Http\Resources\AdminPanel\GoodExportResource;
use App\Imports\TranslateGoodImport;
use App\Models\Language;
use App\Models\Media;
use App\Repositories\CategoryRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\GoodRepository;
use App\Services\GoodsImport\BaseParser;
use App\Services\GoodsImport\ParserService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Setting;
use Illuminate\Support\Arr;
use App\Exports\GoodsExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\GoodRating;
use App\Models\GoodTranslate;
use Illuminate\Support\Collection;

class GoodService extends Service
{
    /**
     * @var UploaderService
     */
    private UploaderService $uploaderService;

    /**
     * @var ParserService
     */
    private ParserService $parserService;

    /**
     * @var MediaConversionsAdapter
     */
    private MediaConversionsAdapter $mediaConversionsAdapter;

    /**
     * @var GoodPriceAdapter
     */
    private GoodPriceAdapter $goodPriceAdapter;

    /**
     * @var CurrencyRepository
     */
    private CurrencyRepository $currencyRepository;

    /**
     * @var int
     */
    private int $goodMargin;

    /**
     * GoodService constructor.
     * @param GoodRepository $goodRepository
     * @param UploaderService $uploaderService
     * @param ParserService $parserService
     * @param MediaConversionsAdapter $mediaConversionsAdapter
     * @param GoodPriceAdapter $goodPriceAdapter
     * @param CurrencyRepository $currencyRepository
     */
    public function __construct(
        GoodRepository $goodRepository,
        UploaderService $uploaderService,
        ParserService $parserService,
        MediaConversionsAdapter $mediaConversionsAdapter,
        GoodPriceAdapter $goodPriceAdapter,
        CurrencyRepository $currencyRepository
    )
    {
        $this->repository = $goodRepository;
        $this->uploaderService = $uploaderService;
        $this->parserService = $parserService;
        $this->mediaConversionsAdapter = $mediaConversionsAdapter;
        $this->goodPriceAdapter = $goodPriceAdapter;
        $this->currencyRepository = $currencyRepository;

        $this->goodMargin = Arr::get(Setting::getPricing(), 'margin', Setting::DEFAULT_MARGIN);
    }

    /**
     * Get list of goods with media
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getList(int $limit, array $params = []): LengthAwarePaginator
    {
        $goods = $this
            ->repository
            ->paginateAllGoodsWithMediaWithCategories($limit, $params);

        $currencies = $this->currencyRepository
            ->all();

        foreach ($goods as $good) {
            $this->mediaConversionsAdapter
                ->adaptImages($good);

            $this->goodPriceAdapter
                ->adapt($good, $currencies, $this->goodMargin);
        }

        return $goods;
    }

    /**
     * get goods list with filter
     *
     * @param int $limit
     * @param array $data
     * @return LengthAwarePaginator
     */
    public function getListWithFilter(int $limit, array $data = []): LengthAwarePaginator
    {
        $query = $this
            ->repository
            ->getGoodsQueryWithFilter();

        if (isset($data['category_id'])) {
            $query = $this
                ->repository
                ->whereCategories($query, $data['category_id']);
        }

        if (isset($data['q'])) {
            $query = $this
                ->repository
                ->filterByQuery($query, $data['q']);
        }

        $query = $this
            ->repository
            ->active($query);

        $query = $this
            ->repository
            ->filterByPrice($query, $data);

        $query = $this
            ->repository
            ->withSort($query, $data);

        $goods = $this
            ->repository
            ->getPaginate($query, $limit);

        $currencies = $this->currencyRepository
            ->all();

        foreach ($goods as $good) {
            $this->mediaConversionsAdapter
                ->adaptImages($good);

            $this->goodPriceAdapter
                ->adapt($good, $currencies, $this->goodMargin);
        }

        return $goods;
    }

    /**
     * Get one good with media
     *
     * @param int $id
     * @return Model
     */
    public function getGood(int $id): Model
    {
        $data = $this->repository
            ->getRecordWithCategoryWithMedia($id);

        $this->mediaConversionsAdapter
            ->adaptImages($data);

        $currencies = $this->currencyRepository
            ->all();

        $this->goodPriceAdapter
            ->adapt($data, $currencies, $this->goodMargin);

        return $data;
    }

    public function getGoodBySlug(string $slug): Model
    {
        $data = $this->repository
            ->getRecordWithCategoryWithMediaBySlug($slug);

        $this->mediaConversionsAdapter
            ->adaptImages($data);

        $currencies = $this->currencyRepository
            ->all();

        $this->goodPriceAdapter
            ->adapt($data, $currencies, $this->goodMargin);

        return $data;
    }

    /**
     * Store goods and return model with media
     *
     * @param array $data
     * @return Model
     * @throws Exception
     */
    public function store(array $data): Model
    {
        try {
            DB::beginTransaction();
            $saved_model = parent::store($data);

            if (isset($data['features'])) {
                $this->syncFeatures($data['features'], $saved_model);
            }

            $this->handleFiles($data, $saved_model);

            $saved_model = $this->updateGoodLeft($saved_model);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            // todo create Exception for this case
            throw new Exception($e->getMessage());
        }

        $saved_model = $this->repository
            ->getWithMediaWithCategory($saved_model);

        $saved_model = $this->mediaConversionsAdapter
            ->adaptImages($saved_model);

        return $saved_model;
    }

    /**
     * Update a good and return model with media
     *
     * @param array $data
     * @param int $id
     * @return Model
     * @throws Exception
     */
    public function update(array $data, int $id): Model
    {
        try {
            DB::beginTransaction();

            $model = parent::update($data, $id);

            if (isset($data['features'])) {
                $this->syncFeatures($data['features'], $model);
            }

            if (isset($data['deleted_files']) && count($data['deleted_files'])) {

                foreach ($data['deleted_files'] as $file_id) {
                    if ($file_id) {
                        $this->uploaderService
                            ->deleteFile($model, Media::IMAGE_TYPE, $file_id);
                        $this->uploaderService
                            ->deleteFile($model, Media::FILE_TYPE, $file_id);
                    }
                }
            }

            $this->handleFiles($data, $model);

            $model = $this->updateGoodLeft($model);

            DB::commit();

            $model = $this
                ->repository
                ->getWithMediaWithCategory($model);
            $model = $this->mediaConversionsAdapter
                ->adaptImages($model);

            return $model;
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }

    /**
     * Delete a good with all media files
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        $model = $this->repository
            ->getRecord($id);

        $this->uploaderService
            ->delete($model, Media::FILE_TYPE);
        $this->uploaderService
            ->delete($model, Media::IMAGE_TYPE);

        return parent::delete($id);
    }

    /**
     * @param array $data
     * @param Model $model
     */
    private function handleFiles(array $data, model $model)
    {
        // todo refactor to 1 upload method calling
        // *
        if (isset($data['file'])) {
            // make arrays with files because UploaderService takes an array
            // if we get just 1 file we need to make an array anyway
            $files = is_array($data['file']) ? $data['file'] : [$data['file']];
            $this->uploaderService
                ->upload($files, $model);
        }
        if (isset($data['img'])) {
            // make arrays with images because UploaderService takes an array
            // if we get just 1 image we need to make an array anyway
            $images = is_array($data['img']) ? $data['img'] : [$data['img']];
            $this->uploaderService
                ->upload($images, $model);
        }
    }

    /**
     * @param Model $model
     */
    private function updateGoodLeft(model $model)
    {
        $data = [
            'good_left' => count($model->files)
        ];
        return parent::update($data, $model->id);

    }

    protected function syncFeatures($features, $model)
    {
        $sync = [];

        foreach ($features as $feature) {
            $sync[$feature['id']] = ['value' => $feature['value'] ?? null];
        }

        $model->features()->sync($sync);
    }

    public function storeFile(object $file): string
    {
        return $file->store('import');
    }

    public function getFile(string $file): string
    {
        return File::get(storage_path("app/$file"));
    }

    public function parseFile(array $data)
    {
        $type = "App\Services\Parser\\$data[type]";
        $keys = array_keys($data['fields']);
        $xml = simplexml_load_string($this->getFile($data['file']));

        return (new $type)
            ->parse($xml, $keys);
    }

    /**
     * Prepare data in Parser service
     *
     * @param array $data
     * @param Model $model
     */
    public function prepareImport(array $data): JsonResponse
    {
        $parserResult = $this->parserService->prepare($data);

        return $parserResult;
    }

    public function importToDb(array $fields, array $data): int
    {
        array_walk($fields, function (&$field) {
            $field = substr($field, strpos($field, ".") + 1);
        });
        $fields = array_flip($fields);
        $categoryRep = new CategoryRepository();
        try {
            DB::beginTransaction();
            foreach ($data as $i => $item) {
                foreach ($fields as $key => $field) {
                    $row[$key] = $item[$field];
                }
                if ($fields['parent_category_title']) {
                    $parentCategory[$i] = $categoryRep
                        ->store([
                            'title' => $row['parent_category_title']
                        ]);
                }
                if ($fields['sub_category_title'] && isset($parentCategory[$i])) {
                    $childCategory[$i] = $categoryRep
                        ->store([
                            'title' => $row['sub_category_title'],
                            'category_id' => $parentCategory[$i]->id
                        ]);
                }
                $price = explode(',', $row['price']);
                $row['seo_h1'] = $row['good_title'] . $row['good_internal_id'] . $i;
                $row['seo_title'] = $row['good_title'] . $row['good_internal_id'] . $i;
                $row['seo_description'] = $row['good_title'] . $row['good_internal_id'] . $i;
                $row['seo_slug'] = $row['good_title'] . $row['good_internal_id'] . $i;
                $row['active'] = true;
                $row['price_integer'] = $price[0];
                $row['price_decimal'] = $price[1];
                $row['good_type'] = 'unlimited';
                $row['category_id'] = $childCategory[$i]->id ?? $parentCategory[$i]->id;
                $newGood[$i] = $this->repository
                    ->store($row);
            }
            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return count($newGood);
    }

    /**
     * Export goods
     *
     * @param array $params
     * @return ?BinaryFileResponse
     */
    public function exportExcel(array $params): ?BinaryFileResponse
    {
        $availableColumns = [
            'id' => 'id',
            'good_title' => 'Название',
            'good_description' => 'Описание',
            'category_id' => 'Категория',
            'good_type' => 'Тип',
            'good_left' => 'Остаток',
            'seo_slug' => 'slug',
            'active' => 'Активен'
        ];

        $columns = [];
        foreach ($params['columns'] as $key) {
            if (array_key_exists($key, $availableColumns)) {
                $columns[$key] = $availableColumns[$key];
            }
        }

        if ($columns) {
            $goods = $this
                ->repository
                ->getWithFiltersAndOrders($params, array_keys($columns));

            if (array_key_exists('category_id', $columns)) {
                foreach ($goods as $good) {
                    $good->category_id = $good->category->title;
                }
            }

            return Excel::download(new GoodsExport($goods, array_values($columns)), 'goods.xlsx');
        }
    }

    /**
     * Set rating of good
     *
     * @param array $params
     * @return float
     */
    public function setRating(array $params): float
    {
        $goodRating = GoodRating::updateOrCreate([
            'good_id' => $params['good_id'],
            'customer_id' => $params['customer_id'],
        ], ['value' => $params['value']]);
        return $goodRating->good->rating;
    }

    /**
     * Translate goods
     *
     * @param array $params
     */
    public function translate(array $params): JsonResponse
    {
        $import = Excel::toArray(new TranslateGoodImport, $params['file']);
        //Check rows exist
        if (!isset($import[0][0][0]) || !isset($import[0][0][1]) || !isset($import[0][0][2]) || !isset($import[0][0][3])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Неправильный формат данных в файле с переводами!',
            ], 422);
        }

        //Remove header
        $importColumns = array_slice($import[0], 1);
        //Check columns exist
        if (!count($importColumns)) {
            return response()->json([
                'status' => 'error',
                'message' => 'В файле с переводами отсутствуют данные!',
            ], 422);
        }

        $goods = $this->repository->allColumns(['id', 'good_title', 'good_description']);

        $createdTranslates = 0;
        $updatedTranslates = 0;

        try {
            DB::beginTransaction();
            foreach ($importColumns as $column) {
                $column = Arr::flatten($column);
                //Search good
                $searchGoods = $goods->where('good_title', $column[0]);
                if ($searchGoods && $goodWithDescription = $searchGoods->where('good_description', $column[2])) {
                    $searchGoods = $goodWithDescription;
                }
                if (isset($column[1]) || isset($column[3]))
                    foreach ($searchGoods as $good) {
                        $goodTranslate = GoodTranslate::updateOrCreate(
                            ['good_id' => $good->id, 'language_id' => $params['language_id']],
                            ['good_title' => $column[1] ?? null, 'good_description' => $column[3] ?? null]
                        );

                        if ($goodTranslate->wasRecentlyCreated) {
                            $createdTranslates++;
                        }
                        if ($goodTranslate->wasChanged()) {
                            $updatedTranslates++;
                        }
                    }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Не удалось создать переводы!',
            ], 422);
        }

        if (!$createdTranslates && !$updatedTranslates) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Переводы не были обновлены, все переводы уже актуальны!',
            ]);
        } else {
            return response()->json([
                'status' => 'ok',
                'message' => ($createdTranslates ? 'Создано: ' . $createdTranslates . ' переводов. ' : '') .
                    ($updatedTranslates ? 'Обновлено: ' . $updatedTranslates . ' переводов.' : '')
            ]);
        }
    }

    /**
     * Get translations by good
     *
     * @param int $id
     * @return Collection
     */
    public function goodTranslates(int $id): Collection
    {
        $response = collect();
        $translates = GoodTranslate::where('good_id', $id)->with('language')->get();
        $languages = Language::all();

        foreach ($languages as $lang) {
            if ($translate = $translates->where('language_id', $lang->id)->first()) {
                $response->push($translate);
            } else {
                $item = collect();
                $item->good_id = $id;
                $item->language_id = $lang->id;
                $item->good_title = '';
                $item->good_description = '';
                $item->language = $lang;
                $response->push($item);
            }
        }

        return $response;
    }

    /**
     * Update translation
     *
     * @param array $params
     * @return GoodTranslate|null
     */
    public function translateUpdate(array $params): ?GoodTranslate
    {
        if (!isset($params['good_title']) && !isset($params['good_description'])) {
            // Delete translation if title AND description is not specified
            GoodTranslate::where('good_id', $params['good_id'])->where('language_id', $params['language_id'])->delete();
            return null;
        } else {
            return GoodTranslate::updateOrCreate([
                'good_id' => $params['good_id'],
                'language_id' => $params['language_id']
            ], [
                'good_title' => $params['good_title'],
                'good_description' => $params['good_description']
            ]);
        }
    }
}
