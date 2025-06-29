<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $lessons = Lesson::with('cours')->get();

            return response()->json($lessons);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request , $courseId)
    {
        try {
            $lesson = new Lesson([
                'cour_id' => $courseId,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'duration' => $request->input('duration'),
                'url_video' => $request->input('url_video'),
                'url_pdf' => $request->input('url_pdf'),    
            ]);
            $lesson->save();

            return response()->json($lesson);
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
            $lesson = Lesson::with('cours')->findOrFail($id);

            return response()->json($lesson);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $courseId, $id)
    {
        try {
            $lesson = Lesson::findOrFail($id);
            $lesson->update([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'duration' => $request->input('duration'),
                'url_video' => $request->input('url_video'),
                'url_pdf' => $request->input('url_pdf'),
            ]);

            return response()->json($lesson);
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
            $lesson = Lesson::findOrFail($id);
            $lesson->delete();

            return response()->json('Lesson deleted');
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }

    }

    public function getByCourse($courseId)
    {
        try {
            $lessons = Lesson::byCoursId($courseId)->with('cours')->get();

            return response()->json($lessons);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }
}
