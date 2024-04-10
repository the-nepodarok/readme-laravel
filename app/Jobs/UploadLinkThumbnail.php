<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\ThumbnailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Загрузка превью страницы для постов-ссылок со стороннего API-сервиса
 */
class UploadLinkThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(readonly Post $post)
    {}

    /**
     * Execute the job.
     */
    public function handle(ThumbnailService $thumbnailService): void
    {
        $thumbnailService->getThumbnail($this->post);
    }
}
