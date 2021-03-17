<?php

namespace App\Services;

use App\Adapters\BrandPaymentImageAdapter;
use App\Adapters\MediaConversionsAdapter;
use App\Models\MainItem;
use App\Models\Media;
use App\Repositories\MainItemRepository;
use App\Repositories\PageSettingRepository;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PageSettingService extends Service
{
    /**
     * @var PageSettingRepository
     */
    public PageSettingRepository $settingRepository;

    /**
     * @var UploaderService
     */
    public UploaderService $uploaderService;

    /**
     * @var MainItemRepository
     */
    public MainItemRepository $mainImageRepository;

    /**
     * @var MediaConversionsAdapter
     */
    public MediaConversionsAdapter $mediaConversionsAdapter;

    /**
     * @var BrandPaymentImageAdapter
     */
    public BrandPaymentImageAdapter $brandPaymentImageAdapter;

    /**
     * PageSettingService constructor.
     *
     * @param PageSettingRepository $settingRepository
     * @param UploaderService $uploaderService
     * @param MainItemRepository $mainImageRepository
     * @param MediaConversionsAdapter $mediaConversionsAdapter
     * @param BrandPaymentImageAdapter $brandPaymentImageAdapter
     */
    public function __construct(
        PageSettingRepository $settingRepository,
        UploaderService $uploaderService,
        MainItemRepository $mainImageRepository,
        MediaConversionsAdapter $mediaConversionsAdapter,
        BrandPaymentImageAdapter $brandPaymentImageAdapter
    ) {
        $this->settingRepository = $settingRepository;
        $this->uploaderService = $uploaderService;
        $this->mainImageRepository = $mainImageRepository;
        $this->mediaConversionsAdapter = $mediaConversionsAdapter;
        $this->repository = $mainImageRepository;
        $this->brandPaymentImageAdapter = $brandPaymentImageAdapter;
    }

    /**
     * Create new setting for page
     *
     * @param array $data
     * @param array|null $files
     * @return Model
     */
    public function createSetting(array $data, array $files = null): Model
    {
        $model = $this->settingRepository
            ->store($data);

        if (isset($data['image_bg'])) {
            $this->uploaderService
                ->upload(array($data['image_bg']), $model);
        }

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $itemInfo = [];
                $itemInfo['title'] = $item['title'];
                $itemInfo['description'] = $item['description'];
                $itemInfo['link'] = $item['link'];
                $itemInfo['link_text'] = $item['link_text'];
                $itemInfo['page_settings_id'] = $model->id;

                $img = $this->mainImageRepository
                    ->store($itemInfo);

                $this->uploaderService
                    ->upload(array($item['img']), $img);
            }
        }

        if (isset($data['brand_logos'])) {
            foreach ($data['brand_logos'] as $item) {
                $itemInfo = [];
                $itemInfo['page_settings_id'] = $model->id;
                $itemInfo['type'] = MainItem::BRAND_TYPE;

                $itemModel = $this->mainImageRepository
                    ->store($itemInfo);

                $this->uploaderService
                    ->upload(array($item), $itemModel);
            }
        }

        if (isset($data['payment_logos'])) {
            foreach ($data['payment_logos'] as $item) {
                $itemInfo = [];
                $itemInfo['page_settings_id'] = $model->id;
                $itemInfo['type'] = MainItem::PAYMENT_TYPE;

                $itemModel = $this->mainImageRepository
                    ->store($itemInfo);

                $this->uploaderService
                    ->upload(array($item), $itemModel);
            }
        }

        return $model;
    }

    /**
     * Get all settings
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getAllSettings(int $limit): LengthAwarePaginator
    {
        $settings = $this
            ->settingRepository
            ->paginateAllWithRelation($limit);

        foreach ($settings as $setting) {
            $this->mediaConversionsAdapter
                ->adaptImages($setting);

            foreach ($setting->items as $item) {
                $this->mediaConversionsAdapter
                    ->adaptImages($item);
            }
        }

        $this->brandPaymentImageAdapter
            ->adaptCollection($settings);

        return $settings;
    }

    /**
     * Get one setting record
     *
     * @param int $id
     * @return Model
     */
    public function getOneSettingsById(int $id): Model
    {
        $settings =  $this->settingRepository
            ->getRecord($id);

        foreach ($settings->items as $setting) {
            $this->mediaConversionsAdapter
                ->adaptImages($setting);
        }

        foreach ($settings->brands as $brand) {
            $this->mediaConversionsAdapter
                ->adaptImages($brand);
        }

        foreach ($settings->payments as $payment) {
            $this->mediaConversionsAdapter
                ->adaptImages($payment);
        }

        $this->mediaConversionsAdapter
            ->adaptImages($settings);

        $this->brandPaymentImageAdapter
            ->adapt($settings);

        return $settings;
    }

    /**
     * To get settings for a page by page title
     *
     * @param string $page
     * @return Model|null
     */
    public function getOneSettingsByPage(string $page): ?Model
    {
        $settings =  $this->settingRepository
            ->getRecordByPage($page);

        if ($settings) {
            foreach ($settings->items as $setting) {
                $this->mediaConversionsAdapter
                    ->adaptImages($setting);
            }

            foreach ($settings->brands as $brand) {
                $this->mediaConversionsAdapter
                    ->adaptImages($brand);
            }

            foreach ($settings->payments as $payment) {
                $this->mediaConversionsAdapter
                    ->adaptImages($payment);
            }
        }

        if ($settings) {
            $this->brandPaymentImageAdapter
                ->adapt($settings);
        }

        return $settings;
    }

    /**
     * Destroy one record
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function destroyOneSetting(int $id): bool
    {
        $model = $this
            ->settingRepository
            ->getRecordWith($id);

        foreach ($model->items as $itemModel) {
            $this
                ->uploaderService
                ->delete($itemModel, Media::FILE_TYPE);
            $this
                ->uploaderService
                ->delete($itemModel, Media::IMAGE_TYPE);
            $itemModel->delete();
        }

        $this
            ->uploaderService
            ->delete($model, Media::FILE_TYPE);
        $this
            ->uploaderService
            ->delete($model, Media::IMAGE_TYPE);

        return $this
            ->settingRepository
            ->destroy($id);
    }

    /**
     * update setting
     *
     * @param array $data
     * @param int $id
     * @return Model
     * @throws Exception
     */
    public function updateSetting(array $data, int $id): Model
    {

        try {
            DB::beginTransaction();

            $model = $this
                ->settingRepository
                ->update($data, $id);

            // delete files
            if (isset($data['deleted_files'])) {
                $this->deleteFiles($model, $data);
            }

            $itemsIds = [];
            // update items
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $itemInfo['title'] = isset($item['title']) ? $item['title'] : null;
                    $itemInfo['description'] = isset($item['description']) ? $item['description']: null;
                    $itemInfo['link'] = isset($item['link']) ? $item['link'] : null;
                    $itemInfo['link_text'] = isset($item['link_text']) ? $item['link_text'] : null;
                    $itemInfo['page_settings_id'] = $model->id;

                    $img = $this->mainImageRepository
                        ->update($itemInfo, $item['id']);

                    if (isset($item['img'])) {
                        $this
                            ->uploaderService
                            ->delete($img, Media::IMAGE_TYPE);

                        $this->uploaderService
                            ->upload(array($item['img']), $img);
                    }
                }
            }

            if (isset($data['brand_logos'])) {
                foreach ($data['brand_logos'] as $item) {
                    $itemInfo['page_settings_id'] = $model->id;
                    $itemInfo['type'] = MainItem::BRAND_TYPE;

                    $itemModel = $this->mainImageRepository
                        ->store($itemInfo);

                    $this->uploaderService
                        ->upload(array($item), $itemModel);
                }
            }

            if (isset($data['payment_logos'])) {
                foreach ($data['payment_logos'] as $item) {
                    $itemInfo['page_settings_id'] = $model->id;
                    $itemInfo['type'] = MainItem::PAYMENT_TYPE;

                    $itemModel = $this->mainImageRepository
                        ->store($itemInfo);

                    $this->uploaderService
                        ->upload(array($item), $itemModel);
                }
            }

//            $this->handleFiles($data, $model);

            DB::commit();

            return $model;
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }

    /**
     * To update page settings for a page by page title
     *
     * @param array $data
     * @param string $page
     * @return Model
     * @throws Exception
     */
    public function updateSettingByPage(array $data, string $page): Model
    {

        try {
            DB::beginTransaction();

            $model = $this
                ->settingRepository
                ->updateByPage($data, $page);

            if (isset($data['deleted_files'])) {

                MainItem::whereHas('media',
                    function($query) use ($data) {
                        $query->whereIn('id', $data['deleted_files']);
                    })->delete();
            }

            $itemIds = [];
            // update items
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $itemInfo['title'] = isset($item['title']) ? $item['title'] : null;
                    $itemInfo['description'] = isset($item['description']) ? $item['description']: null;
                    $itemInfo['link'] = isset($item['link']) ? $item['link'] : null;
                    $itemInfo['link_text'] = isset($item['link_text']) ? $item['link_text'] : null;
                    $itemInfo['page_settings_id'] = $model->id;

                    $img = isset($item['id'])
                        ? $this->mainImageRepository
                        ->update($itemInfo, $item['id'])
                        : $this->mainImageRepository
                        ->store($itemInfo);

                    if (isset($item['img'])) {
                        $this
                            ->uploaderService
                            ->delete($img, Media::IMAGE_TYPE);

                        $this->uploaderService
                            ->upload(array($item['img']), $img);
                    }

                    $itemIds[] = $img->id;
                }
            }

            MainItem::whereNotIn('id', $itemIds)->whereNull('type')->delete();

            if (isset($data['brand_logos'])) {
                foreach ($data['brand_logos'] as $item) {
                    $itemInfo = [];
                    $itemInfo['page_settings_id'] = $model->id;
                    $itemInfo['type'] = MainItem::BRAND_TYPE;

                    $itemModel = $this->mainImageRepository
                        ->store($itemInfo);

                    $this->uploaderService
                        ->upload(array($item), $itemModel);
                }
            }

            if (isset($data['payment_logos'])) {
                foreach ($data['payment_logos'] as $item) {
                    $itemInfo = [];
                    $itemInfo['page_settings_id'] = $model->id;
                    $itemInfo['type'] = MainItem::PAYMENT_TYPE;

                    $itemModel = $this->mainImageRepository
                        ->store($itemInfo);

                    $this->uploaderService
                        ->upload(array($item), $itemModel);
                }
            }

            DB::commit();

            $this->mediaConversionsAdapter
                ->adaptImages($model);

            foreach ($model->items as $setting) {
                $this->mediaConversionsAdapter
                    ->adaptImages($setting);
            }

            foreach ($model->brands as $brand) {
                $this->mediaConversionsAdapter
                    ->adaptImages($brand);
            }

            foreach ($model->payments as $payment) {
                $this->mediaConversionsAdapter
                    ->adaptImages($payment);
            }

            $this->brandPaymentImageAdapter
                ->adapt($model);

            return $model;
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }

    /**
     * upload image
     *
     * @param array $data
     * @param Model $model
     */
    private function handleFiles(array $data, model $model)
    {
        if (isset($data['image_bg'])) {
            // make arrays with images because UploaderService takes an array
            // if we get just 1 image we need to make an array anyway
            $images = is_array($data['image_bg']) ? $data['image_bg'] : [$data['image_bg']];
            $this->uploaderService
                ->upload($images, $model);
        }

        if (isset($data['brand_logos'])) {
            // make arrays with images because UploaderService takes an array
            // if we get just 1 image we need to make an array anyway
            $images = is_array($data['brand_logos']) ? $data['brand_logos'] : [$data['brand_logos']];
            $this->uploaderService
                ->upload($images, $model);
        }

        if (isset($data['payment_logos'])) {
            // make arrays with images because UploaderService takes an array
            // if we get just 1 image we need to make an array anyway
            $images = is_array($data['payment_logos']) ? $data['payment_logos'] : [$data['payment_logos']];
            $this->uploaderService
                ->upload($images, $model);
        }
    }

    /**
     * delete files if isset params 'deleted_files'
     *
     * @param $model
     * @param array $data
     * @throws Exception
     */
    protected function deleteFiles($model, array $data)
    {
        foreach ($data['deleted_files'] as $file_id) {
            if (!$file_id) {
                continue;
            }
            $this
                ->uploaderService
                ->deleteFile($model, Media::IMAGE_TYPE, $file_id);
            $this
                ->uploaderService
                ->deleteFile($model, Media::FILE_TYPE, $file_id);
        }
    }
}
