<?php

namespace App\Http\Controllers;

use App\Models\PassedExams;
use Illuminate\Http\Request;

class PassedExamsController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $exams = PassedExams::with('student', 'exam', 'certification')->get();

            return response()->json($exams);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($user_id, $exam_id)
    {
        try {
            $exam = new PassedExams([
                'user_id' => $user_id,
                'exam_id' => $exam_id,
                'score' => 0,
            ]);
            $exam->save();
    
            return response()->json($exam);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $exam = PassedExams::with('student', 'exam', 'certification')->findOrFail($id);

            return response()->json($exam);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, $score)
    {
        try {
            $exam = PassedExams::findOrFail($id);
            $exam->update([
                'score' => $score,
            ]);
    
            return response()->json($exam);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        try {
            $exam = PassedExams::findOrFail($id);
            $exam->delete();

            return response()->json('Passed exam deleted');
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }

    }
}
