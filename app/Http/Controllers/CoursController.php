<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use Illuminate\Http\Request;

class CoursController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Cours::with('speciality', 'lessons', 'exam.questions.answers', 'creator');
            
            // If creator_id is provided, filter by creator
            if ($request->has('creator_id')) {
                $query->where('creator_id', $request->creator_id);
            }
            
            $courses = $query->get();

            // Add student count, rating, and reviews count to each course
            $courses->each(function ($course) {
                $course->students_count = $course->getTotalStudentsCount();
                $course->rating = $course->getAverageRating();
                $course->total_reviews = $course->getReviewsCount();
            });

            return response()->json($courses);
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
            $course = new Cours([
                'name' => $request->input('name'),
                'cours_url' => $request->input('cours_url'),
                'speciality_id' => $request->input('speciality_id'),
                'creator_id' => $request->input('creator_id'),
                'description' => $request->input('description'),
                'image' => $request->input('image'),
                'price' => $request->input('price'),
                'discount' => $request->input('discount'),
                'is_accepted' => null,
            ]);
            $course->save();

            return response()->json($course);
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
            $course = Cours::with('speciality', 'lessons', 'exam.questions.answers', 'creator')->findOrFail($id);
            
            // Add student count, rating, and reviews count
            $course->students_count = $course->getTotalStudentsCount();
            $course->rating = $course->getAverageRating();
            $course->total_reviews = $course->getReviewsCount();
            
            // Debug logging
            \Illuminate\Support\Facades\Log::info('Course data being returned:', [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'speciality_id' => $course->speciality_id,
                'speciality_name' => $course->speciality ? $course->speciality->name : 'null',
                'creator_id' => $course->creator_id,
                'creator_name' => $course->creator ? $course->creator->name : 'null',
                'students_count' => $course->students_count,
                'rating' => $course->rating,
                'total_reviews' => $course->total_reviews,
            ]);

            return response()->json($course);
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
            $course = Cours::findOrFail($id);
            $course->update($request->all());

            return response()->json($course);
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
            $course = Cours::findOrFail($id);
            $course->delete();

            return response()->json('course deleted');
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }

    }

    public function getBySpeciality($specialityId)
    {
        try {
            $courses = Cours::bySpecialityId($specialityId)->with('speciality' ,'lessons', 'exam.questions.answers')->get();

            // Add student count, rating, and reviews count to each course
            $courses->each(function ($course) {
                $course->students_count = $course->getTotalStudentsCount();
                $course->rating = $course->getAverageRating();
                $course->total_reviews = $course->getReviewsCount();
            });

            return response()->json($courses);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }

    public function getByDiscipline($disciplineId)
    {
        try {
            $courses = Cours::byDisciplineId($disciplineId)->with('speciality', 'lessons', 'exam.questions.answers')->get();

            // Add student count, rating, and reviews count to each course
            $courses->each(function ($course) {
                $course->students_count = $course->getTotalStudentsCount();
                $course->rating = $course->getAverageRating();
                $course->total_reviews = $course->getReviewsCount();
            });

            return response()->json($courses);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }
    
    public function accept(string $id)
    {
        $course = Cours::findOrFail($id);
        $course->accept();

        return response()->json([
            'message' => 'Course accepted successfully!',
            'course' => $course,
        ], 202);
    }

    public function reject(string $id)
    {
        $course = Cours::findOrFail($id);
        $course->reject();

        return response()->json([
            'message' => 'Course rejected successfully!',
            'course' => $course,
        ], 202);
    }

    public function getByCreator($creatorId)
    {
        try {
            $courses = Cours::where('creator_id', $creatorId)
                ->with('speciality', 'lessons', 'exam.questions.answers', 'creator')
                ->get();

            // Add student count, rating, and reviews count to each course
            $courses->each(function ($course) {
                $course->students_count = $course->getTotalStudentsCount();
                $course->rating = $course->getAverageRating();
                $course->total_reviews = $course->getReviewsCount();
            });

            return response()->json($courses);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }

    /**
     * Get only accepted courses for public display
     */
    public function getAcceptedCourses(Request $request)
    {
        try {
            $query = Cours::with('speciality', 'lessons', 'exam.questions.answers', 'creator')
                ->where('is_accepted', 1);
            
            // If creator_id is provided, filter by creator
            if ($request->has('creator_id')) {
                $query->where('creator_id', $request->creator_id);
            }
            
            $courses = $query->get();

            // Add student count, rating, and reviews count to each course
            $courses->each(function ($course) {
                $course->students_count = $course->getTotalStudentsCount();
                $course->rating = $course->getAverageRating();
                $course->total_reviews = $course->getReviewsCount();
            });

            return response()->json($courses);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }
}
