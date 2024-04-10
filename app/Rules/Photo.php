<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class Photo implements ValidationRule
{
    const SUPPORTED_FILES = [
        'jpg',
        'jpeg',
        'png',
        'gif'
    ];

    const SUPPORTED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
    ];
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Создание объекта информации о файле
        $fileInfo = new \SplFileInfo($value);

        // Получение расширения файла
        $extension = $fileInfo->getExtension();

        if (Str::of($extension)->isEmpty()) {
            $path = $fileInfo->getPath();
            $extension = pathinfo($path)['extension'];
        }

        // Получение MIME-типа файла
        $file = file_get_contents($value);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($file);

        // Проверка типа файла
        if (!in_array($extension, self::SUPPORTED_FILES)
            or
            !in_array($mime, self::SUPPORTED_MIME_TYPES)) {
            $fail('Неверный формат. Прикрепите файл изображения jpg, png или gif.');
        }

    }
}
