<?php

namespace App\Http\Controllers;

use App\Models\Speciality;
use Illuminate\Http\Request;

class SpecialityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $specialities = Speciality::with('discipline', 'courses')->get();

            return response()->json($specialities);
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
            $speciality = new Speciality([
                'name' => $request->input('name'),
                'discipline_id' => $request->input('discipline_id'),
                'description' => $request->input('description'),
                'image' => $request->input('image'),
            ]);
            $speciality->save();

            return response()->json($speciality);
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
            $speciality = Speciality::with('discipline', 'courses')->findOrFail($id);

            return response()->json($speciality);
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
            $speciality = Speciality::findOrFail($id);
            $speciality->update($request->all());

            return response()->json($speciality);
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
            $speciality = Speciality::findOrFail($id);
            $speciality->delete();

            return response()->json('Speciality deleted');
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }

    }
}
