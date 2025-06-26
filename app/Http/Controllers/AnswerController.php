<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $answers = Answer::with('question')->get();

            return response()->json($answers);
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
            $answer = new Answer([
                'question_id' => $request->input('question_id'),
                'answer' => $request->input('answer'),
                'is_correct' => $request->input('is_correct'),
            ]);
            $answer->save();

            return response()->json($answer);
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
            $answer = Answer::with('question')->findOrFail($id);

            return response()->json($answer);
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
            $answer = Answer::findOrFail($id);
            $answer->update($request->all());

            return response()->json($answer);
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
            $answer = Answer::findOrFail($id);
            $answer->delete();

            return response()->json('Answer deleted');
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }

    }
}
