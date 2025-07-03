<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cours extends Model
{
    protected $fillable = ['name', 'cours_url','speciality_id', 'creator_id', 'description', 'image', 'is_accepted', 'price', 'discount', 'required_score'];

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function exam(): HasOne
    {
        return $this->hasOne(Exams::class, 'cours_id');
    }

    public function creator() // created by teacher
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exams::class, 'cours_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class,'cour_id');
    }

    public function enrolledStudents(): HasMany
    {
        return $this->hasMany(StudentCourse::class, 'cours_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'cour_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class, 'cours_id');
    }

    public function getAverageRating()
    {
        $avg = $this->reviews()->avg('rating');
        return $avg !== null ? (float) $avg : 0.0;
    }

    public function getReviewsCount()
    {
        return $this->reviews()->count();
    }

    public function getAllEnrolledStudents()
    {
        // Get students enrolled directly
        $directStudents = $this->enrolledStudents()
            ->with('user:id,name,first_name,last_name,email,avatar')
            ->active()
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->user->id,
                    'name' => $enrollment->user->name,
                    'first_name' => $enrollment->user->first_name,
                    'last_name' => $enrollment->user->last_name,
                    'email' => $enrollment->user->email,
                    'avatar' => $enrollment->user->avatar,
                    'enrollment_type' => 'direct',
                    'enrolled_at' => $enrollment->enrolled_at,
                    'progress' => $enrollment->progress,
                    'status' => $enrollment->status,
                    'completed_at' => $enrollment->completed_at
                ];
            });

        // Get students enrolled via groups
        $groupStudents = $this->groups()
            ->with('students:id,name,first_name,last_name,email,avatar')
            ->get()
            ->flatMap(function ($group) {
                return $group->students->map(function ($student) use ($group) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'email' => $student->email,
                        'avatar' => $student->avatar,
                        'enrollment_type' => 'group',
                        'enrolled_at' => $group->created_at,
                        'progress' => 0, // Default progress for group enrollments
                        'status' => 'active',
                        'completed_at' => null,
                        'group_id' => $group->id,
                        'group_name' => $group->name
                    ];
                });
            });

        // Merge and remove duplicates (prioritize direct enrollments)
        $allStudents = $directStudents->concat($groupStudents);
        $uniqueStudents = $allStudents->unique('id')->values();

        return $uniqueStudents;
    }

    public function getTotalStudentsCount()
    {
        // Combine all counts, removing duplicates
        $allStudentIds = collect();
        
        // Add direct enrollments
        $directStudentIds = $this->enrolledStudents()->active()->pluck('user_id');
        $allStudentIds = $allStudentIds->concat($directStudentIds);
        
        // Add group enrollments
        $groupStudentIds = $this->groups()
            ->with('students:id')
            ->get()
            ->flatMap(function ($group) {
                return $group->students->pluck('id');
            });
        $allStudentIds = $allStudentIds->concat($groupStudentIds);
        
        // Add user group enrollments
        $userGroupStudentIds = $this->groups()
            ->with('userGroups.user:id')
            ->get()
            ->flatMap(function ($group) {
                return $group->userGroups->pluck('user.id');
            });
        $allStudentIds = $allStudentIds->concat($userGroupStudentIds);

        return $allStudentIds->unique()->count();
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
