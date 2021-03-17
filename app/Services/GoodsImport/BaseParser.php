<?php

namespace App\Services\GoodsImport;

use App\Jobs\ProcessImageParser;
use App\Models\Media;
use App\Repositories\CategoryRepository;
use App\Repositories\GoodRepository;
use App\Repositories\BrandRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class BaseParser
{
    /**
     * @var string $signature
     */
    public string $signature;

    /**
     * @var array $src
     */
    public array $src;

    /**
     * @var array $dest
     */
    protected array $dest = [
        'goods.good_title' => 'Название товара',
        'categories.title' => 'Название категории',
        'goods.sku' => 'Артикул',
        'brands.name' => 'Название бренда',
        'goods.good_description' => 'Описание товара',
        'goods.seo_h1' => 'seo h1',
        'goods.seo_title' => 'seo title',
        'goods.seo_description' => 'seo description',
        'goods.seo_slug' => 'seo slug',
        'goods.active' => 'Активность товара',
        'goods.good_left' => 'Остаток товара',
        'goods.good_type' => 'Тип товара',
        'goods.price' => 'Цена',
        'image.src' => 'Изображение'
    ];

    /**
     * @var string $good_path
     */
    public string $good_path;

    /**
     * @var string $good_prefix
     */
    public string $good_prefix;

    /**
     * @var GoodRepository
     */
    public $goodRepo;

    /**
     * @var CategoryRepository
     */
    public $categoryRepo;

    /**
     * @var BrandRepository
     */
    public $brandRepo;

    /**
     * @var array
     */
    public $slugs = [];

    /**
     * @var array
     */
    public $categories = [];

    /**
     * @var array
     */
    public $brands = [];

    /**
     * @var array
     */
    public $skuCodes = [];

    /**
     * @var Collection
     */
    public $images;

    /**
     * @var int
     */
    public $goodsAddedCount = 0;

    /**
     * @var int
     */
    public $goodsUpdatedCount = 0;


    /**
     * BaseService constructor
     */
    public function __construct()
    {
        $this->goodRepo = app(GoodRepository::class);
        $this->categoryRepo = app(CategoryRepository::class);
        $this->brandRepo = app(BrandRepository::class);
    }

    /**
     * Detect parser by signature
     *
     * @param object $xmlDump
     * @return bool
     */
    public function detect(object $xmlDump): bool
    {
        $nesting = explode('.', $this->signature);

        foreach ($nesting as $nextNest) {
            if (isset($xmlDump->$nextNest)) {
                $xmlDump = $xmlDump->$nextNest;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Get dests
     *
     * @return  array
     */
    public function getDest(): array
    {
        return $this->dest;
    }

    /**
     * Get src
     * @param object $xmlDump
     * @return array
     */
    public function getSrc(object $xmlDump): array
    {
        $srcExists = [];

        foreach ($this->src as $src) {
            $nesting = array_map('trim', explode('.', $src));

            if ($this->goNest($xmlDump, $nesting, 0)) {
                $srcExists[] = $src;
            }
        }

        return $srcExists;
    }

    /**
     * Recursive go to nest
     * @param object $xml
     * @param array $nesting
     * @param int $nestPosition
     * @return bool
     */
    private function goNest(object $xml, array $nesting, int $nestPosition): bool
    {
        $nest = $nesting[$nestPosition];
        $last = count($nesting) - 1;

        if (!isset($nesting[$nestPosition]))
            return false;

        if ($nesting[$nestPosition] === '*') {
            if (!isset($nesting[$nestPosition + 1]))
                return false;
            $nestNext = $nesting[$nestPosition + 1];
            foreach ($xml->children() as $item) {
                if (isset($item->$nestNext)) {
                    $xml = $item->$nestNext;
                    if ($nestPosition < $last - 2) {
                        return $this->goNest($xml, $nesting, $nestPosition + 2);
                    }
                } else {
                    return false;
                }
            }
        } else if ($nesting[$nestPosition] === '') {
            return false;
        } else {
            if (isset($xml->$nest)) {
                $xml = $xml->$nest;
                if ($nestPosition < $last) {
                    return $this->goNest($xml, $nesting, $nestPosition + 1);
                }
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Import to db
     * @param object $xml
     * @param array $fields
     * @return JsonResponse
     */
    public function importDB(object $xml, array $fields): JsonResponse
    {
        try {
            $goods = $this->goodRepo->allColumns(['id', 'seo_slug', 'sku']);
            $this->slugs = $goods->pluck('id', 'seo_slug')->toArray();
            $this->skuCodes = $goods->pluck('id', 'sku')->toArray();
            $this->categories = $this->categoryRepo->allColumns(['id', 'title'])->toArray();
            $this->brands = $this->brandRepo->allColumns(['id', 'name'])->toArray();
            $this->images = Media::where('model_type', 'App\Models\Good')->get(['model_id', 'custom_properties']);

            if (isset($this->good_path)) {
                $xml = $this->goToXml($xml, $this->good_path);
            }

            DB::beginTransaction();
            foreach ($xml->children() as $goodXml) {
                $categoryData = [];
                $goodData = [];
                $brandData = [];
                $imageData = [];

                foreach ($fields as $src => $dest) {
                    $categoryData = $this->processCategory($goodXml, $categoryData, $src, $dest);
                    $brandData = $this->processBrand($goodXml, $brandData, $src, $dest);
                    $goodData = $this->processGood($goodXml, $goodData, $src, $dest);
                    $imageData = $this->processImage($goodXml, $imageData, $src, $dest);
                }

                if (!empty($categoryData)) {
                    $categoryData = $this->createCategory($categoryData);
                }
                if (!empty($brandData)) {
                    $brandData = $this->createBrand($brandData);
                }
                if (!empty($goodData) && !empty($goodData['good_title'])) {
                    $goodDataObj = $this->createGood($goodData, $categoryData, $brandData);
                    if (!empty($imageData) && $goodDataObj) {
                        $this->createImage($imageData, $goodDataObj);
                    }
                }
            }
            DB::commit();

            $message = $this->goodsAddedCount ? 'Добавлено ' . $this->goodsAddedCount . ' товаров. ' : '';
            $message .= $this->goodsUpdatedCount ? 'Обновлено ' . $this->goodsUpdatedCount . ' товаров.' : '';

            return response()->json([
                'status' => 'ok',
                'message' => empty($message) ? 'Ничего не было импортировано!' : $message,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Не удалось импортировать товары!',
            ], 422);
        }
    }

    /**
     * Go to xml path
     *
     * @param object $xml
     * @param string $path
     * @return object|null
     */
    public function goToXml(object $xml, string $path): ?object
    {
        $nesting = explode('.', $path);
        foreach ($nesting as $nextNest) {
            if (isset($xml->$nextNest)) {
                $xml = $xml->$nextNest;
            } else {
                return null;
            }
        }

        return $xml;
    }

    /**
     * Process category
     *
     * @param object $good
     * @param array $categoryData
     * @param string $src
     * @param string $dest
     *
     * @return array
     */
    public function processCategory(object $good, array $categoryData, string $src, string $dest): array
    {
        if (Str::startsWith($dest, 'categories')) {
            $src = str_replace($this->good_prefix, '', $src);

            $good = $this->goToXml($good, $src);
            if (is_null($good)) {
                return $categoryData;
            } else {
                $good = (string)$good;
            }

            $categoryData[str_replace('categories.', '', $dest)] = $good;
        }

        return $categoryData;
    }

    /**
     * Create category
     *
     * @param array $categoryXml
     *
     * @return array
     */
    public function createCategory(array $categoryXml): array
    {
        $categoryData = [
            'title' => 'Title',
        ];

        $categoryData = array_replace($categoryData, $categoryXml);

        //Search category
        foreach ($this->categories as $category) {
            if ($category['title'] == $categoryData['title']) {
                return $category;
            }
        }

        //If not found category
        $category = $this->categoryRepo->store($categoryData)->toArray();
        array_push($this->categories, $category);

        return $category;
    }

    /**
     * Process category
     *
     * @param object $good
     * @param array $goodData
     * @param string $src
     * @param string $dest
     *
     * @return array
     */
    public function processGood(object $good, array $goodData, string $src, string $dest): array
    {
        if (Str::startsWith($dest, 'goods')) {
            $src = str_replace($this->good_prefix, '', $src);

            $good = $this->goToXml($good, $src);
            if (is_null($good)) {
                return $goodData;
            } else {
                $good = (string)$good;
            }

            if ($dest === 'goods.price') {
                $price = preg_split('/[.|,]/', $good);

                $goodData['price_integer'] = isset($price[0]) && $price[0] != '' ? $price[0] : 0;
                $goodData['price_decimal'] = isset($price[1]) && $price[1] != '' ? $price[1] : 0;
            } else {
                $goodData[str_replace('goods.', '', $dest)] = $good;
            }
        }

        return $goodData;
    }

    /**
     * Create good
     *
     * @param array $goodXml
     * @param array $categoryData
     * @param array $brandData
     *
     * @return object
     */
    public function createGood(array $goodXml, array $categoryData, array $brandData): object
    {
        $goodData = [
            'good_title' => 'Title',
            'good_description' => 'Description',
            'seo_h1' => 'seo h1',
            'seo_title' => 'seo title',
            'seo_description' => 'seo description',
            'active' => 0,
            'good_left' => 0,
            'count_reserved' => 0,
            'good_type' => 0,
            'price_integer' => 0,
            'price_decimal' => 0,
            'seo_slug' => '',
        ];

        $goodData = array_replace($goodData, $goodXml);

        $goodData['category_id'] = $categoryData['id'] ?? null;
        $goodData['brand_id'] = $brandData['id'] ?? null;

        //Set unique seo_slug
        if (!isset($this->slugs[$goodData['seo_slug']])) {
            $i = 1;
            $slug = Str::slug($goodData['good_title']);

            while (isset($this->slugs[$slug])) {
                $slug = Str::slug($goodData['good_title']) . '-' . $i;
                $i++;
            }
            $goodData['seo_slug'] = $slug;
            $this->slugs[$goodData['seo_slug']] = 1;
        }

        if (isset($goodData['sku']) && isset($this->skuCodes[$goodData['sku']])) {
            $good = $this->goodRepo->update($goodXml, $this->skuCodes[$goodData['sku']]);
            if ($good->wasChanged())
                $this->goodsUpdatedCount++;
        } else {
            $good = $this->goodRepo->store($goodData);
            $this->goodsAddedCount++;
        }

        return $good;
    }

    /**
     * Process image
     *
     * @param object $good
     * @param array $imageData
     * @param string $src
     * @param string $dest
     *
     * @return array
     */
    public function processImage(object $good, array $imageData, string $src, string $dest): array
    {
        if (Str::startsWith($dest, 'image')) {
            $src = str_replace($this->good_prefix, '', $src);

            $image_src = $this->goToXml($good, $src);

            if (Str::endsWith($dest, 'src')) {
                $imageData[] = $image_src;
            }
        }

        return $imageData;
    }

    /**
     * Create image
     *
     * @param array $imageData
     * @param object $goodData
     *
     */
    public function createImage(array $imageData, object $goodData)
    {
        $modelImages = $this->images->where('model_id', $goodData->id);
        foreach ($imageData as $imgSrc) {
            if (Str::startsWith($imgSrc, 'http://') || Str::startsWith($imgSrc, 'https://')) {
                $exist = false;
                foreach ($modelImages as $img) {
                    if (Arr::get($img->custom_properties, 'importUrl') == $imgSrc) {
                        $exist = true;
                        break;
                    }
                }
                if (!$exist) {
                    ProcessImageParser::dispatch($goodData, $imgSrc);
                }
            }
        }
    }

    /**
     * Process brand
     *
     * @param object $good
     * @param array $brandData
     * @param string $src
     * @param string $dest
     *
     * @return array
     */
    public function processBrand(object $good, array $brandData, string $src, string $dest): array
    {
        if (Str::startsWith($dest, 'brands')) {
            $src = str_replace($this->good_prefix, '', $src);

            $good = $this->goToXml($good, $src);
            if (is_null($good)) {
                return $brandData;
            } else {
                $good = (string)$good;
            }

            $brandData[str_replace('brands.', '', $dest)] = $good;
        }

        return $brandData;
    }

    /**
     * Create brand
     *
     * @param array $brandXml
     *
     * @return array
     */
    public function createBrand(array $brandXml): array
    {
        $brandData = [
            'name' => 'Name',
        ];

        $brandData = array_replace($brandData, $brandXml);

        //Search brand
        foreach ($this->brands as $brand) {
            if ($brand['name'] == $brandData['name']) {
                return $brand;
            }
        }

        //If not found brand
        $brand = $this->brandRepo->store($brandData)->toArray();
        array_push($this->brands, $brand);

        return $brand;
    }

}
