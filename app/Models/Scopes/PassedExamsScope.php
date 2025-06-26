<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class PassedExamsScope implements Scope
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
            $builder->whereHas('exam.course', function ($query) use ($user) {
                $query->where('creator_id', $user->id);
            });
            return;
        }

        if ($user->hasRole('student')) {
            $builder->whereHas('passedExam', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
            return;
        }

    $builder->whereRaw('1 = 0');
    }
}
