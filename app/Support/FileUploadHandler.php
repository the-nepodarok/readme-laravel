<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\UnableToWriteFile;

class FileUploadHandler
{
    public function __construct()
    {}

    /**
     * Загружает файл по ссылке
     * @param string $url Ссылка на файл
     * @param string $path Путь сохранения файла
     * @return string Название файла для записи в базу данных
     * @throws UnableToWriteFile
     * @throws ConnectionException
     */
    public function uploadFromUrl(string $url, string $path): string
    {
        $response = Http::get($url);

        if ($response->successful()) {
            // получение содержимого файла
            $file = $response->body();

            // получение расширения файла
            $finfo = finfo_open(FILEINFO_EXTENSION);
            $extension = Str::of($finfo->buffer($file))->before('/');

            // генерация уникального имени файла
            $filename = uniqid() . ".$extension";

            $path = $path . DIRECTORY_SEPARATOR . $filename;

            // перенос файла в директорию
            Storage::disk('public')->put($path, $file) ?: throw new UnableToWriteFile('Ошибка записи файла; попробуйте позже.');

            return $filename;
        }

        throw new ConnectionException('Не удалось загрузить файл. Проверьте ссылку или попробуйте снова позднее');
    }

    /**
     * Загружает переданный файл из формы в папку uploads
     * @param $file
     * @param string $path Путь сохранения файла
     * @return string Название файла
     */
    public function upload($file, string $path): string
    {
        $filename = $file->hashName();

        if ($file->store($path)) {
            return $filename;
        } else {
            throw new UnableToWriteFile('Ошибка записи файла; попробуйте позже.');
        }
    }
}
