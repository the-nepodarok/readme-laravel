<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Youtube implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $host = parse_url($value, PHP_URL_HOST);
        $path = parse_url($value, PHP_URL_PATH);

        if (
            ($host !== 'www.youtube.com' and $host !== 'youtu.be') // Проверка, что хост относится к youtube
            or
            (Str::of($path)->isEmpty() or $path === '/') // Проверка, что в ссылке есть ID видео
            or
            !(Http::get($value)->successful()) // Проверка, что видео по ссылке существует
        ) {
            $fail('Неверный формат ссылки. Приложите ссылку на Youtube');
        }
    }
}
