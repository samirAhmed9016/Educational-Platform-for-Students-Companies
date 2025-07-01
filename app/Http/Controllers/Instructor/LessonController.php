<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function store(StoreLessonRequest $request, $course_id): JsonResponse
    {
        $user = Auth::user();

        if ($user->role != 'instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $course = Course::where('id', $course_id)
            ->where('instructor_id', $user->id)
            ->first();

        if (!$course) {
            return response()->json(['error' => 'Course not found or unauthorized'], 404);
        }

        $lesson = $course->lessons()->create($request->validated());

        return response()->json($lesson, 201);
    }
    public function index($course_id): JsonResponse
    {
        $user = Auth::user();

        if ($user->role != 'instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $course = Course::where('id', $course_id)
            ->where('instructor_id', $user->id)
            ->first();

        if (!$course) {
            return response()->json(['error' => 'Course not found or unauthorized'], 404);
        }

        $lessons = $course->lessons()->get();

        return response()->json($lessons);
    }

    public function show($course_id, $lesson_id): JsonResponse
    {
        $user = Auth::user();

        if ($user->role != 'instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lesson = Lesson::where('id', $lesson_id)
            ->where('course_id', $course_id)
            ->whereHas('course', function ($query) use ($user) {
                $query->where('instructor_id', $user->id);
            })
            ->first();

        if (!$lesson) {
            return response()->json(['error' => 'Lesson not found or unauthorized'], 404);
        }

        return response()->json($lesson);
    }


    public function update(UpdateLessonRequest $request, $course_id, $lesson_id): JsonResponse
    {
        $user = Auth::user();

        if ($user->role != 'instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lesson = Lesson::where('id', $lesson_id)
            ->where('course_id', $course_id)
            ->whereHas('course', function ($query) use ($user) {
                $query->where('instructor_id', $user->id);
            })
            ->first();

        if (!$lesson) {
            return response()->json(['error' => 'Lesson not found or unauthorized'], 404);
        }


        tap($lesson, function ($lesson) use ($request) {
            if ($request->has('title')) {
                $lesson->title = $request->input('title');
            }
            if ($request->has('content')) {
                $lesson->content = $request->input('content');
            }
            if ($request->has('order')) {
                $lesson->order = $request->input('order');
            }
        })->save();

        return response()->json($lesson);
    }


    public function destroy($course_id, $lesson_id): JsonResponse
    {
        $user = Auth::user();

        if ($user->role != 'instructor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lesson = Lesson::where('id', $lesson_id)
            ->where('course_id', $course_id)
            ->whereHas('course', function ($query) use ($user) {
                $query->where('instructor_id', $user->id);
            })
            ->first();

        if (!$lesson) {
            return response()->json(['error' => 'Lesson not found or unauthorized'], 404);
        }

        $lesson->delete();

        return response()->json(['message' => 'Lesson deleted successfully']);
    }
}
