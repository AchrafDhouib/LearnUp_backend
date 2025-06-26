<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = ['passed_exam_id'];

    public function passedExam()
    {
        return $this->belongsTo(PassedExams::class, 'passed_exam_id');
    }
}
