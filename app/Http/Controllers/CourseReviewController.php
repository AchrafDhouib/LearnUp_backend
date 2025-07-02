<?php

namespace App\Http\Controllers;

use App\Models\CourseReview;
use App\Models\Cours;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseReviewController extends Controller
{
    /**
     * Get reviews for a specific course
     */
    public function getCourseReviews($courseId)
    {
        try {
            $course = Cours::findOrFail($courseId);
            
            $reviews = $course->reviews()
                ->with('user:id,name,first_name,last_name,avatar')
                ->orderBy('created_at', 'desc')
                ->get();

            $averageRating = $course->getAverageRating();
            $totalReviews = $course->getReviewsCount();
            
            $stats = [
                'average_rating' => is_numeric($averageRating) ? (float) $averageRating : 0.0,
                'total_reviews' => (int) $totalReviews,
            ];

            return response()->json([
                'reviews' => $reviews,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a review for a course
     */
    public function createReview(Request $request, $courseId)
    {
        try {
            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $user = $request->user();
            
            if (!$user->hasRole('student')) {
                return response()->json(['error' => 'Only students can review courses'], 403);
            }

            // Check if user already reviewed this course
            $existingReview = CourseReview::where('user_id', $user->id)
                ->where('cours_id', $courseId)
                ->first();

            if ($existingReview) {
                // Update existing review
                $existingReview->update([
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                ]);

                return response()->json([
                    'message' => 'Review updated successfully',
                    'review' => $existingReview->load('user:id,name,first_name,last_name,avatar')
                ]);
            }

            // Create new review
            $review = CourseReview::create([
                'user_id' => $user->id,
                'cours_id' => $courseId,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_approved' => true
            ]);

            return response()->json([
                'message' => 'Review submitted successfully',
                'review' => $review->load('user:id,name,first_name,last_name,avatar')
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a review
     */
    public function updateReview(Request $request, $courseId, $reviewId)
    {
        try {
            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $user = $request->user();
            
            $review = CourseReview::where('id', $reviewId)
                ->where('user_id', $user->id)
                ->where('cours_id', $courseId)
                ->first();

            if (!$review) {
                return response()->json(['error' => 'Review not found'], 404);
            }

            $review->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            return response()->json([
                'message' => 'Review updated successfully',
                'review' => $review->load('user:id,name,first_name,last_name,avatar')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a review
     */
    public function deleteReview(Request $request, $courseId, $reviewId)
    {
        try {
            $user = $request->user();
            
            $review = CourseReview::where('id', $reviewId)
                ->where('user_id', $user->id)
                ->where('cours_id', $courseId)
                ->first();

            if (!$review) {
                return response()->json(['error' => 'Review not found'], 404);
            }

            $review->delete();

            return response()->json([
                'message' => 'Review deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Approve/Reject a review (admin/teacher only)
     */
    public function moderateReview(Request $request, $reviewId)
    {
        try {
            $request->validate([
                'is_approved' => 'required|boolean',
            ]);

            $user = $request->user();
            
            if (!$user->hasRole(['admin', 'teacher'])) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $review = CourseReview::findOrFail($reviewId);
            $review->update(['is_approved' => $request->is_approved]);

            return response()->json([
                'message' => $request->is_approved ? 'Review approved' : 'Review rejected',
                'review' => $review
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 