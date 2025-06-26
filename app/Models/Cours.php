<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cours extends Model
{
    protected $fillable = ['name', 'cours_url','speciality_id', 'creator_id', 'description', 'image', 'is_accepted'];

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function exam(): HasOne
    {
        return $this->hasOne(Exams::class);
    }

    public function creator() // created by teacher
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exams::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class,'cour_id');
    }

    public function scopeBySpecialityId($query, $specialityId)
    {
        return $query->where('speciality_id', $specialityId);
    }

    public function scopeByDisciplineId($query, $disciplineId)
    {
        return $query->whereHas('speciality', function ($q) use ($disciplineId) {
            $q->where('discipline_id', $disciplineId);
        });
    }

    public function accept()
    {
        $this->is_accepted = true;
        $this->save();
    }

    public function reject()
    {
        $this->is_accepted = false;
        $this->save();
    }
}
