<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentFormRequest extends FormRequest
{
    protected $errorBag;

    public function initialize(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::initialize($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->errorBag = $this->post_id; // Присвоение идентификатора поста форме для отображения ошибок
    }

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
            'text' => 'required|string|min:3|max:400',
            'post_id' => 'required|int|exists:posts,id'
        ];
    }

    public function messages()
    {
        return [
            'text.required' => 'Введите текст комментария',
            'text.min' => 'Комментарий слишком короткий',
            'text.max' => 'Комментарий слишком длинный'
        ];
    }
}
