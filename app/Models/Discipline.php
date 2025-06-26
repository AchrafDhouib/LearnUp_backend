<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discipline extends Model
{
    protected $fillable = ['name', 'description', 'image'];

    public function specialities(): HasMany
    {
        return $this->hasMany(Speciality::class);
    }
}
