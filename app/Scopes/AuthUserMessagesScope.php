<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuthUserMessagesScope implements \Illuminate\Database\Eloquent\Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(['sender_id' => Auth::id()])->orWhere(['receiver_id' => Auth::id()]);
    }
}
