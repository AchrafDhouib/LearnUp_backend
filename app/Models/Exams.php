<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exams extends Model
{
    protected $fillable = ['description', 'cours_id'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Cours::class,'cours_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
