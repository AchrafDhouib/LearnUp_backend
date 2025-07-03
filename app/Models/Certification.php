<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certification extends Model
{
    protected $fillable = [
        'passed_exam_id',
        'certificate_number',
        'student_name',
        'course_name',
        'instructor_name',
        'score',
        'required_score',
        'issued_date',
        'validity_period',
        'achievement_description'
    ];

    protected $casts = [
        'issued_date' => 'date',
        'score' => 'decimal:2',
        'required_score' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certification) {
            if (empty($certification->certificate_number)) {
                $certification->certificate_number = 'CERT-' . strtoupper(Str::random(8));
            }
        });
    }

    public function passedExam()
    {
        return $this->belongsTo(PassedExams::class, 'passed_exam_id');
    }
}
