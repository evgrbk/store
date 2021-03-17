<?php

namespace App\Adapters;

use App\Models\PageSetting;

class BrandPaymentImageAdapter
{
    /**
     * @var MediaConversionsAdapter
     */
    private MediaConversionsAdapter $mediaConversionsAdapter;

    /**
     * BrandPaymentImageAdapter constructor.
     * @param MediaConversionsAdapter $mediaConversionsAdapter
     */
    public function __construct(MediaConversionsAdapter $mediaConversionsAdapter)
    {
        $this->mediaConversionsAdapter = $mediaConversionsAdapter;
    }

    /**
     * Adapt page settings brand and payment images for output
     *
     * @param $settings
     * @return void
     */
    public function adapt($settings)
    {
        $brands = [];
        foreach ($settings->brands as $brand) {
            $brands['images'][] = $brand->images->first();
        }
        $settings->brands = $brands;

        $payments = [];
        foreach ($settings->payments as $payment) {
            $payments['images'][] = $payment->images->first();
        }
        $settings->payments = $payments;
    }

    /**
     * Adapt page settings brand and payment images for output
     *
     * @param $settings
     * @return void
     */
    public function adaptCollection($settings): void
    {
        foreach ($settings as $setting) {
            foreach ($setting->brands as $brand) {
                $this->mediaConversionsAdapter
                    ->adaptImages($brand);
            }

            foreach ($setting->payments as $payment) {
                $this->mediaConversionsAdapter
                    ->adaptImages($payment);
            }

            $brands = [];
            foreach ($setting->brands as $brand) {
                $brands['images'][] = $brand->images->first();
            }
            $setting->brands = $brands;

            $payments = [];
            foreach ($setting->payments as $payment) {
                $payments['images'][] = $payment->images->first();
            }

            $setting->payments = $payments;
        }
    }
}
