<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollRequest;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{

    public function store(EnrollRequest $request)
    {
        $user = auth()->user();
        $courseId = $request->course_id;


        if (Enrollment::where('user_id', $user->id)->where('course_id', $courseId)->exists()) {
            return response()->json(['message' => 'You are already enrolled in this course.'], 409);
        }



        // Create enrollment
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'enrolled_at' => now(),
        ]);

        return response()->json(['message' => 'Enrolled successfully.']);
    }


    public function myEnrollments()
    {
        $user = auth()->user();
        $courses = $user->enrollments()->with('course')->get();

        return response()->json([
            'enrollments' => $courses
        ]);
    }




    public function studentsInCourse($courseId)
    {

        $instructor = auth()->user();

        $course = $instructor->courses()->where('courses.id', $courseId)->first();

        if (!$course) {
            return response()->json(['message' => 'Course not found or unauthorized.'], 403);
        }


        $enrollments = $course->enrollments()->with('user:id,name,email')->get();

        return response()->json([
            'course' => $course->title,
            'students' => $enrollments->pluck('user')
        ]);
    }
}
