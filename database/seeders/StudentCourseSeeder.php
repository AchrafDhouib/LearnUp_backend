<?php

namespace Database\Seeders;

use App\Models\StudentCourse;
use App\Models\User;
use App\Models\Cours;
use Illuminate\Database\Seeder;

class StudentCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some students and courses
        $students = User::role('student')->take(5)->get();
        $courses = Cours::take(10)->get();

        if ($students->isEmpty() || $courses->isEmpty()) {
            return;
        }

        // Create some direct enrollments
        foreach ($students as $student) {
            // Enroll in 2-3 random courses directly
            $randomCourses = $courses->random(rand(2, 3));
            
            foreach ($randomCourses as $course) {
                // Check if already enrolled
                $existingEnrollment = StudentCourse::where('user_id', $student->id)
                    ->where('cours_id', $course->id)
                    ->first();

                if (!$existingEnrollment) {
                    StudentCourse::create([
                        'user_id' => $student->id,
                        'cours_id' => $course->id,
                        'enrollment_type' => 'direct',
                        'status' => rand(1, 10) > 8 ? 'completed' : 'active', // 20% chance of being completed
                        'progress' => rand(0, 100),
                        'enrolled_at' => now()->subDays(rand(1, 30)),
                        'completed_at' => rand(1, 10) > 8 ? now()->subDays(rand(1, 10)) : null,
                    ]);
                }
            }
        }
    }
} 