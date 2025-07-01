<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;



class CourseController extends Controller
{
    /**
     * Show all courses for the authenticated instructor.
     */
    public function index()
    {
        $courses = Course::where('instructor_id', Auth::id())->get();

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }

    /**
     * Store a newly created course.
     */
    public function store(StoreCourseRequest $request)
    {
        $validated = $request->validated();

        $course = new Course($validated);
        $course->instructor_id = Auth::id();

        if ($request->hasFile('thumbnail')) {
            $course->thumbnail = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course->save();

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully.',
            'data' => $course
        ], 201);
    }

    /**
     * Update the specified course if it belongs to the instructor.
     */
    public function update(UpdateCourseRequest $request, $id)
    {


        $course = Course::findOrFail($id);

        if ($course->instructor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this course.'
            ], 403);
        }

        $validated = $request->validated();

        $course->fill($validated);

        if ($request->hasFile('thumbnail')) {
            $course->thumbnail = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course->save();

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully.',
            'data' => $course
        ]);
    }

    /**
     * Delete the specified course if it belongs to the instructor.
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        if ($course->instructor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this course.'
            ], 403);
        }

        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully.'
        ]);
    }

    /**
     * Public: List all published courses.
     */
    public function publicIndex()
    {
        $courses = Course::all();

        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }

    /**
     * Public: Show a single course.
     */
    public function show($id)
    {
        $course = Course::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $course
        ]);
    }
}
