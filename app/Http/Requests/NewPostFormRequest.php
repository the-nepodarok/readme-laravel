<?php

namespace App\Http\Requests;

use App\Rules\Photo;
use App\Rules\Youtube;
use Illuminate\Foundation\Http\FormRequest;

class NewPostFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'header' => 'required|min:3|max:30',
            'hashtags' => 'nullable|string|regex:/^(#[A-z_А-я\d]+(\h)?)+$/ui',
            'text' => 'sometimes|required|min:3',
            'quote' => 'sometimes|required|min:3|max:128',
            'quote_author' => 'sometimes|required|min:3',
            'link' => 'sometimes|required|url:https',
            'video' => ['sometimes', 'required', 'url', new Youtube],
            'photo_url' => ['sometimes', 'exclude_with:photo_file', 'required', 'url:https', new Photo],
            'photo_file' => 'sometimes|required_without:photo_url|nullable|mimes:jpg,png,gif|max:10240'
        ];
    }

    public function messages(): array
    {
        return [
            'header.required' => 'Введите название поста',
            'header.min' => 'Название слишком короткое, добавьте ещё пару слов',
            'hashtags.regex' => 'Неверный формат. Хэштэги должны начинаться с "#" и разделяться пробелом.',
            'text.required' => 'Поле должно быть заполнено.',
            'text.min' => 'Введите ещё пару слов',
            'quote.required' => 'Введите текст цитаты',
            'quote.min' => 'Цитата слишком короткая, введите ещё пару слов',
            'quote_author.required' => 'Поле должно быть заполнено',
            'quote_author.min' => 'Поле должно содержать больше 3 символов',
            'link.required' => 'Введите текст ссылки',
            'link.url' => 'Неверный формат ссылки',
            'video.required' => 'Введите текст ссылки',
            'video.url' => 'Неверный формат ссылки. Приложите ссылку на Youtube',
            'photo_url.required' => 'Введите ссылку на картинку или приложите файл',
            'photo_url.url' => 'Неверный формат ссылки. Измените её или приложите файл',
            'photo_file.max' => 'Файл слишком большой. Размер не должен превышать 10 Мб',
            'photo_file.mimes' => 'Неверный формат файла. Приложите картинку в формате jpg, png или gif',
            'photo_file.required_without' => 'Введите ссылку на картинку или приложите файл',
        ];
    }
}
