<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Student submits a review
     */
    public function store(StoreReviewRequest $request, $course_id)
    {
        $user = Auth::user();
        $course_id = $request->course_id;

        $enrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course_id)
            ->exists();

        if (!$enrolled) {
            return response()->json(['message' => 'You must be enrolled to review this course.'], 403);
        }


        if (Review::where('user_id', $user->id)->where('course_id', $course_id)->exists()) {
            return response()->json(['message' => 'You already submitted a review.'], 400);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'course_id' => $course_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Review submitted.', 'data' => $review], 201);

        // echo 'samir';
    }

    /**
     * Student updates their own review
     */
    public function update(UpdateReviewRequest $request, $id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $review->update($request->validated());

        return response()->json(['message' => 'Review updated.', 'data' => $review]);
    }

    /**
     * Student deletes their own review
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted.']);
    }

    /**
     * Public view of reviews for a course
     */
    public function index($courseId)
    {
        $course = Course::with('reviews.user')->findOrFail($courseId);

        return response()->json([
            'course' => $course->title,
            'reviews' => $course->reviews,
            'average_rating' => round($course->reviews->avg('rating'), 1)
        ]);
    }

    /**
     * Instructor views reviews of their own course
     */
    public function instructorIndex($courseId)
    {
        $instructor = Auth::user();

        $course = Course::where('id', $courseId)
            ->where('instructor_id', $instructor->id)
            ->with('reviews.user')
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Unauthorized or course not found.'], 403);
        }

        return response()->json([
            'course' => $course->title,
            'reviews' => $course->reviews,
        ]);
    }
}
