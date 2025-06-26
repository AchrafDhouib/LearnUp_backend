<?php

namespace App\Models;

use App\Models\Scopes\PassedExamsScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([PassedExamsScope::class])]
class UserAnswer extends Model
{
    protected $fillable = ['user_id', 'passed_exam_id', 'question_id', 'answer_id'];

    public function passedExam()
    {
        return $this->belongsTo(PassedExams::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
