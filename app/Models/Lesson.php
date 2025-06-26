<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lesson extends Model
{
    protected $fillable = ['cour_id', 'title','description', 'duration', 'url_video', 'url_pdf'];

    public function cours()
    {
        return $this->belongsTo(Cours::class, 'cour_id');
    }

    public function scopeByCoursId($query, $coursId)
    {
        return $query->where('cour_id', $coursId);
    }

}
