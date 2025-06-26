<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([UserScope::class])]
class PassedExams extends Model
{
    protected $fillable = ['user_id', 'exam_id', 'score'];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exams::class, 'exam_id');
    }

    public function userAnswer()
    {
        return $this->hasMany(UserAnswer::class,'user_id');
    }

    public function certification()
    {
        return $this->hasOne(Certification::class, 'passed_exam_id');
    }
}
