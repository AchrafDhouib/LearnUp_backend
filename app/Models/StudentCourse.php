<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCourse extends Model
{
    protected $fillable = [
        'user_id',
        'cours_id',
        'enrollment_type',
        'status',
        'enrolled_at',
        'completed_at',
        'progress'
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Cours::class, 'cours_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByEnrollmentType($query, $type)
    {
        return $query->where('enrollment_type', $type);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now()
        ]);
    }

    public function updateProgress($progress)
    {
        $this->update(['progress' => min(100, max(0, $progress))]);
        
        // Auto-complete if progress reaches 100%
        if ($this->progress >= 100) {
            $this->markAsCompleted();
        }
    }
} 