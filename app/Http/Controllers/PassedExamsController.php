<?php

namespace App\Http\Controllers;

use App\Models\PassedExams;
use App\Models\Certification;
use Illuminate\Http\Request;

class PassedExamsController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            // Bypass the UserScope and manually filter by user_id
            $exams = PassedExams::withoutGlobalScope('App\Models\Scopes\UserScope')
                ->where('user_id', $user->id)
                ->with('exam.course', 'certification')
                ->get();

            return response()->json($exams);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            // Debug logging
            \Illuminate\Support\Facades\Log::info('Creating passed exam:', [
                'user_id' => $user ? $user->id : 'null',
                'request_data' => $request->all()
            ]);
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $request->validate([
                'exam_id' => 'required|integer|exists:exams,id',
                'score' => 'required|numeric|min:0|max:100'
            ]);

            $exam = new PassedExams([
                'user_id' => $user->id,
                'exam_id' => $request->exam_id,
                'score' => $request->score,
                'passed_at' => now(),
            ]);
            $exam->save();
            
            \Illuminate\Support\Facades\Log::info('Passed exam created successfully:', [
                'passed_exam_id' => $exam->id,
                'user_id' => $exam->user_id,
                'exam_id' => $exam->exam_id,
                'score' => $exam->score
            ]);

            // Load exam with course to get required score
            $examWithCourse = $exam->load('exam.course.creator');
            $requiredScore = $examWithCourse->exam->course->required_score ?? 70;
            
            // Create certification if exam is passed
            if ($exam->score >= $requiredScore) {
                try {
                    $certification = new Certification([
                        'passed_exam_id' => $exam->id,
                        'student_name' => $user->name,
                        'course_name' => $examWithCourse->exam->course->name,
                        'instructor_name' => $examWithCourse->exam->course->creator->name ?? 'LearnUp Team',
                        'score' => $exam->score,
                        'required_score' => $requiredScore,
                        'issued_date' => now(),
                        'validity_period' => 'Permanent',
                        'achievement_description' => "A réussi l'évaluation finale avec un score de {$exam->score}% (score requis: {$requiredScore}%)"
                    ]);
                    $certification->save();
                    
                    \Illuminate\Support\Facades\Log::info('Certification created successfully:', [
                        'certification_id' => $certification->id,
                        'passed_exam_id' => $exam->id,
                        'certificate_number' => $certification->certificate_number
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error creating certification:', [
                        'error' => $e->getMessage(),
                        'passed_exam_id' => $exam->id
                    ]);
                }
            }
    
            return response()->json($examWithCourse->load('certification'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating passed exam:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $exam = PassedExams::with('student', 'exam.course', 'certification')->findOrFail($id);

            return response()->json($exam);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $exam = PassedExams::where('id', $id)->where('user_id', $user->id)->firstOrFail();
            
            $request->validate([
                'score' => 'required|numeric|min:0|max:100'
            ]);

            $exam->update([
                'score' => $request->score,
                'passed_at' => $request->passed_at ?? now(),
            ]);
    
            return response()->json($exam->load('exam', 'student'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $exam = PassedExams::where('id', $id)->where('user_id', $user->id)->firstOrFail();
            $exam->delete();

            return response()->json(['message' => 'Passed exam deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get user answers for a specific passed exam
     */
    public function getUserAnswers(Request $request, $id)
    {
        try {
            $user = $request->user();
            $passedExam = PassedExams::where('id', $id)->where('user_id', $user->id)->firstOrFail();
            
            $userAnswers = $passedExam->userAnswers()->with([
                'question.answers',
                'answer'
            ])->get();

            return response()->json($userAnswers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
