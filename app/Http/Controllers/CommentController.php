<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentFormRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Создание нового комментария
     * @param CommentFormRequest $request
     * @param Post $post
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(CommentFormRequest $request)
    {
        Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $request->validated('post_id'),
            'text' => $request->validated('text')
            ]);

        return redirect()->back();
    }
}
