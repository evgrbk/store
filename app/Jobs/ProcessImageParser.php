<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImageParser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var object $good
     */
    protected object $good;

    /**
     * @var string $imgSrc
     */
    protected string $imgSrc;

    /**
     * Create a new job instance.
     * @param object $good
     * @param string $imgSrc
     *
     * @return void
     */
    public function __construct(object $good, string $imgSrc)
    {
        $this->good = $good;
        $this->imgSrc = $imgSrc;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $collectionType = Media::IMAGE_TYPE;

        $this->good
            ->addMediaFromUrl($this->imgSrc)
            ->withCustomProperties(['importUrl' => $this->imgSrc])
            ->toMediaCollection($collectionType);
    }
}
