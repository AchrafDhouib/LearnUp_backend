<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class DisciplineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $diciplines = Discipline::with('specialities')->get();

            return response()->json($diciplines);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $dicipline = new Discipline([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'image' => $request->input('image'),
            ]);
            $dicipline->save();

            return response()->json($dicipline);
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
            $dicipline = Discipline::with('specialities')->findOrFail($id);

            return response()->json($dicipline);
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
            $dicipline = Discipline::findOrFail($id);
            $dicipline->update($request->all());

            return response()->json($dicipline);
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
            $dicipline = Discipline::findOrFail($id);
            $dicipline->delete();

            return response()->json('dicipline deleted');
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }
}
