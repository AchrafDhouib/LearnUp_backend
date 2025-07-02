<?php

namespace App\Http\Controllers;

use App\Models\StudentCourse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentCourseController extends Controller
{
    /**
     * Get all courses for a specific student
     */
    public function getStudentCourses($studentId)
    {
        try {
            $student = User::findOrFail($studentId);
            
            if (!$student->hasRole('student')) {
                return response()->json(['error' => 'User is not a student'], 403);
            }

            $courses = $student->getEnrolledCourses();

            return response()->json($courses);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get current user's courses
     */
    public function getMyCourses(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user->hasRole('student')) {
                return response()->json(['error' => 'User is not a student'], 403);
            }

            $courses = $user->getEnrolledCourses();

            return response()->json($courses);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Enroll a student in a course directly
     */
    public function enrollInCourse(Request $request)
    {
        try {
            $request->validate([
                'course_id' => 'required|exists:cours,id',
            ]);

            $user = $request->user();
            
            if (!$user->hasRole('student')) {
                return response()->json(['error' => 'User is not a student'], 403);
            }

            // Check if already enrolled
            $existingEnrollment = StudentCourse::where('user_id', $user->id)
                ->where('cours_id', $request->course_id)
                ->first();

            if ($existingEnrollment) {
                return response()->json(['error' => 'Student is already enrolled in this course'], 400);
            }

            // Create enrollment
            $enrollment = StudentCourse::create([
                'user_id' => $user->id,
                'cours_id' => $request->course_id,
                'enrollment_type' => 'direct',
                'status' => 'active',
                'progress' => 0
            ]);

            return response()->json([
                'message' => 'Successfully enrolled in course',
                'enrollment' => $enrollment->load('course')
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update course progress
     */
    public function updateProgress(Request $request, $courseId)
    {
        try {
            $request->validate([
                'progress' => 'required|integer|min:0|max:100',
            ]);

            $user = $request->user();
            
            if (!$user->hasRole('student')) {
                return response()->json(['error' => 'User is not a student'], 403);
            }

            $enrollment = StudentCourse::where('user_id', $user->id)
                ->where('cours_id', $courseId)
                ->first();

            if (!$enrollment) {
                return response()->json(['error' => 'Student is not enrolled in this course'], 404);
            }

            $enrollment->updateProgress($request->progress);

            return response()->json([
                'message' => 'Progress updated successfully',
                'enrollment' => $enrollment->load('course')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark course as completed
     */
    public function markAsCompleted(Request $request, $courseId)
    {
        try {
            $user = $request->user();
            
            if (!$user->hasRole('student')) {
                return response()->json(['error' => 'User is not a student'], 403);
            }

            $enrollment = StudentCourse::where('user_id', $user->id)
                ->where('cours_id', $courseId)
                ->first();

            if (!$enrollment) {
                return response()->json(['error' => 'Student is not enrolled in this course'], 404);
            }

            $enrollment->markAsCompleted();

            return response()->json([
                'message' => 'Course marked as completed',
                'enrollment' => $enrollment->load('course')
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Drop course enrollment
     */
    public function dropCourse(Request $request, $courseId)
    {
        try {
            $user = $request->user();
            
            if (!$user->hasRole('student')) {
                return response()->json(['error' => 'User is not a student'], 403);
            }

            $enrollment = StudentCourse::where('user_id', $user->id)
                ->where('cours_id', $courseId)
                ->first();

            if (!$enrollment) {
                return response()->json(['error' => 'Student is not enrolled in this course'], 404);
            }

            $enrollment->update(['status' => 'dropped']);

            return response()->json([
                'message' => 'Course dropped successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Complete a lesson and update progress
     */
    public function completeLesson(Request $request, $courseId)
    {
        try {
            $user = $request->user();
            
            if (!$user->hasRole('student')) {
                return response()->json(['error' => 'User is not a student'], 403);
            }

            // Check if user is enrolled via direct enrollment
            $directEnrollment = StudentCourse::where('user_id', $user->id)
                ->where('cours_id', $courseId)
                ->first();

            if ($directEnrollment) {
                // Update progress in student_courses table
                $course = \App\Models\Cours::findOrFail($courseId);
                $totalLessons = $course->lessons()->count();
                
                if ($totalLessons > 0) {
                    $newProgress = min(100, (($directEnrollment->progress ?? 0) + (100 / $totalLessons)));
                    $directEnrollment->update(['progress' => $newProgress]);
                }
                
                return response()->json([
                    'message' => 'Lesson completed and progress updated',
                    'progress' => $directEnrollment->progress
                ]);
            }

            // Check if user is enrolled via group
            $groupEnrollment = \App\Models\UserGroup::where('user_id', $user->id)
                ->whereHas('group', function ($query) use ($courseId) {
                    $query->where('cour_id', $courseId);
                })
                ->first();

            if ($groupEnrollment) {
                // Update progress in user_groups table
                $course = \App\Models\Cours::findOrFail($courseId);
                $totalLessons = $course->lessons()->count();
                
                if ($totalLessons > 0) {
                    $newProgress = min(100, (($groupEnrollment->progress ?? 0) + (100 / $totalLessons)));
                    $groupEnrollment->update(['progress' => $newProgress]);
                }
                
                return response()->json([
                    'message' => 'Lesson completed and progress updated',
                    'progress' => $groupEnrollment->progress
                ]);
            }

            return response()->json(['error' => 'You are not enrolled in this course'], 404);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get enrollment statistics for a student
     */
    public function getEnrollmentStats(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user->hasRole('student')) {
                return response()->json(['error' => 'User is not a student'], 403);
            }

            $courses = $user->getEnrolledCourses();
            
            $stats = [
                'total_enrolled' => $courses->count(),
                'active_courses' => $courses->where('status', 'active')->count(),
                'completed_courses' => $courses->where('status', 'completed')->count(),
                'direct_enrollments' => $courses->where('enrollment_type', 'direct')->count(),
                'group_enrollments' => $courses->where('enrollment_type', 'group')->count(),
                'average_progress' => $courses->avg('progress') ?? 0
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 