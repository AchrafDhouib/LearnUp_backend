<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use Illuminate\Http\Request;
class CertificationController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $certifications = Certification::with('passedExam')->get();

            return response()->json($certifications);
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
            $certification = new Certification([
                'passed_exam_id' => $request->input('passed_exam_id'),
            ]);
            $certification->save();

            return response()->json($certification);
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
            $certification = Certification::with('passedExam')->findOrFail($id);

            return response()->json($certification);
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
            $certification = Certification::findOrFail($id);
            $certification->update($request->all());

            return response()->json($certification);
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
            $certification = Certification::findOrFail($id);
            $certification->delete();

            return response()->json('Certification deleted');
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }

    }
}
