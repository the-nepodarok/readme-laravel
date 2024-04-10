<?php

namespace App\Services;

use App\Models\Post;
use App\Support\FileUploadHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ThumbnailService implements ShouldQueue
{
    use Dispatchable;
    private string $baseUri = 'https://api.thumbnail.ws/api/';
    private int $width = 220;
    private string $path;

    public function __construct(private FileUploadHandler $uploadHandler)
    {
        $this->path = env('APP_THUMBNAIL_PATH');
    }

    /**
     * Получение thumbnail-превью страницы по ссылке из поста и прикрепление к посту
     * @param Post $post
     * @return void
     */
    public function getThumbnail(Post $post): void
    {
        $apiQueryUrl = $this->buildQuery($post->link);

        $filename = $this->uploadHandler->uploadFromUrl($apiQueryUrl, $this->path);

        $this->attachThumbnail($post, $filename);

    }

    /**
     * Прикрепление названия файла к публикации
     * @param Post $post
     * @param string $filename
     * @return void
     */
    public function attachThumbnail(Post $post, string $filename): void
    {
        if (Storage::disk('public')->exists($this->path . DIRECTORY_SEPARATOR . $filename)) {
            $post->update(['link_preview' => $filename]);
        }
    }

    /**
     * Построение адреса запроса для API
     * @param string $url
     * @return string
     */
    private function buildQuery(string $url): string
    {
        $options = [
            'url' => $url,
            'width' => $this->width,
        ];

        return $this->baseUri . env('APP_THUMBNAIL_KEY') . '/thumbnail/get?' . Arr::query($options);
    }
}
