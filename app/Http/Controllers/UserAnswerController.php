<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserAnswer;

class UserAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $userAnswers = UserAnswer::with('passedExam', 'question', 'answer', 'user')->get();

            return response()->json($userAnswers);
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
            
            $userAnswer = new UserAnswer([
                'passed_exam_id' => $request->input('passed_exam_id'),
                'question_id' => $request->input('question_id'),
                'answer_id' => $request->input('answer_id'),
                'user_id' => $user->id,
            ]);
            $userAnswer->save();

            return response()->json($userAnswer->load('question', 'answer'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $userAnswer = UserAnswer::with('passedExam', 'question', 'answer', 'user')->findOrFail($id);

            return response()->json($userAnswer);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $userAnswer = UserAnswer::findOrFail($id);
            $userAnswer->update($request->all());

            return response()->json($userAnswer);
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
            $userAnswer = UserAnswer::findOrFail($id);
            $userAnswer->delete();

            return response()->json('UserAnswer deleted');
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }

    }

    /**
     * Get user answers for a specific passed exam
     */
    public function getByPassedExam($passedExamId)
    {
        try {
            $userAnswers = UserAnswer::with([
                'question.answers',
                'answer'
            ])
            ->where('passed_exam_id', $passedExamId)
            ->get();

            return response()->json($userAnswers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
