<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'first_name',
        'avatar',
        'email',
        'password',
        'is_active',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class)
            ->with('passedExam');
    }

    public function passedExams(): HasMany
    {
        return $this->hasMany(PassedExams::class);
    }
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'user_groups', 'user_id', 'group_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Cours::class, 'creator_id');
    }

    public function createdGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'creator_id');
    }

    public function enrolledCourses(): HasMany
    {
        return $this->hasMany(StudentCourse::class);
    }

    public function getEnrolledCourses()
    {
        // Get courses via direct enrollment
        $directEnrollments = $this->enrolledCourses()
            ->with('course.speciality', 'course.creator')
            ->active()
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->course->id,
                    'name' => $enrollment->course->name,
                    'description' => $enrollment->course->description,
                    'image' => $enrollment->course->image,
                    'cours_url' => $enrollment->course->cours_url,
                    'price' => $enrollment->course->price,
                    'discount' => $enrollment->course->discount,
                    'speciality' => $enrollment->course->speciality,
                    'creator' => $enrollment->course->creator,
                    'enrollment_type' => 'direct',
                    'enrolled_at' => $enrollment->enrolled_at,
                    'progress' => $enrollment->progress,
                    'status' => $enrollment->status,
                    'completed_at' => $enrollment->completed_at
                ];
            });

        // Get courses via group enrollment
        $groupEnrollments = $this->groups()
            ->with('course.speciality', 'course.creator')
            ->get()
            ->map(function ($group) {
                // Get the user's progress for this group
                $userGroup = $this->groups()->where('group_id', $group->id)->first();
                $progress = $userGroup ? $userGroup->pivot->progress : 0;
                
                return [
                    'id' => $group->course->id,
                    'name' => $group->course->name,
                    'description' => $group->course->description,
                    'image' => $group->course->image,
                    'cours_url' => $group->course->cours_url,
                    'price' => $group->course->price,
                    'discount' => $group->course->discount,
                    'speciality' => $group->course->speciality,
                    'creator' => $group->course->creator,
                    'enrollment_type' => 'group',
                    'enrolled_at' => $group->created_at,
                    'progress' => $progress,
                    'status' => 'active',
                    'completed_at' => null,
                    'group_id' => $group->id,
                    'group_name' => $group->name
                ];
            });

        // Merge and remove duplicates (prioritize direct enrollments)
        $allCourses = $directEnrollments->concat($groupEnrollments);
        $uniqueCourses = $allCourses->unique('id')->values();

        return $uniqueCourses;
    }

    public function scopeAnswersByExam($query, $examId)
    {
        return $query->whereHas('answers', function ($query) use ($examId) {
            $query->whereHas('question', function ($query) use ($examId) {
                $query->where('exam_id', $examId);
            });
        });
    }

    public function activate()
    {
        $this->is_active = true;
        $this->save();
    }

    public function deactivate()
    {
        $this->is_active = false;
        $this->save();
    }
    
}
