<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserScope implements Scope
{
    
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (!$user || !$user instanceof User) {
            $builder->whereRaw('1 = 0');
            return;
        }

        if ($user->hasRole('admin')) {
            return;
        }

        if ($user->hasRole('teacher')) {
            return;
        }

        if ($user->hasRole('student')) {
            $builder->where('user_id', $user->id);
        }
        $builder->whereRaw('1 = 0');
    }
}