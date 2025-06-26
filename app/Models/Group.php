<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'cour_id', 'start_date', 'end_date', 'creator_id', 'description', 'image', 'price', 'max_students'];

    public function students()
    {
        return $this->belongsToMany(User::class, 'user_groups', 'group_id', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(user::class, 'creator_id');
    }

    public function course()
    {
        return $this->belongsTo(Cours::class , 'cour_id');
    }
}
