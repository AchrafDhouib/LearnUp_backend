<?php

namespace App\Http\Controllers;

use App\Models\Exams;
use Illuminate\Http\Request;

class ExamsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $exams = Exams::with('course', 'questions')->get();

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
            $exam = new Exams([
                'description' => $request->input('description'),
                'cour_id' => $request->input('cour_id'),
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
            $exam = Exams::with('course', 'questions')->findOrFail($id);

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
            $exam = Exams::findOrFail($id);
            $exam->update($request->all());

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
            $exam = Exams::findOrFail($id);
            $exam->delete();

            return response()->json('Exam deleted');
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }

    }
}
