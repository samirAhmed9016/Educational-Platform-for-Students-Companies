<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProgressRequest;
use App\Models\CourseUser;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Progress;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function store(StoreProgressRequest $request)
    {
        $user = auth()->user();

        $enrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $request->course_id)
            ->exists();

        if (!$enrolled) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        $isCompleted = $request->filled('is_completed')
            ? $request->is_completed
            : ($request->progress_percentage == 100);

        $completedAt = $isCompleted ? now() : null;

        $progress = Progress::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $request->course_id,
                'lesson_id' => $request->lesson_id,
            ],
            [
                'progress_percentage' => $request->progress_percentage,
                'is_completed' => $isCompleted,
                'completed_at' => $completedAt,
                'notes' => $request->notes,
            ]
        );

        $completedLessonsCount = Progress::where('user_id', $user->id)
            ->where('course_id', $request->course_id)
            ->where('quiz_passed', true)
            ->count();

        $totalLessonsCount = Lesson::where('course_id', $request->course_id)->count();

        if ($completedLessonsCount === $totalLessonsCount && $totalLessonsCount > 0) {
            CourseUser::updateOrCreate(
                ['user_id' => $user->id, 'course_id' => $request->course_id],
                ['completed_at' => now()]
            );
        }

        return response()->json([
            'message' => 'Progress updated successfully.',
            'progress' => $progress,
        ]);
    }



    public function showStudentProgress($course_id)
    {
        $user = auth()->user();

        $progress = Progress::where('user_id', $user->id)
            ->where('course_id', $course_id)
            ->get(['lesson_id', 'progress_percentage', 'is_completed', 'completed_at', 'notes']);

        return response()->json(['progress' => $progress]);
    }


    public function showEnrolledStudentsProgress($course_id)
    {
        $user = auth()->user();

        $course = $user->courses()->find($course_id);

        if (!$course) {
            return response()->json(['message' => 'Course not found or you are not the instructor.'], 403);
        }

        $lessonIds = $course->lessons()->pluck('id');

        $progress = Progress::whereIn('lesson_id', $lessonIds)
            ->with([
                'user:id,name,email',
                'lesson:id,title,course_id'
            ])
            ->get();

        return response()->json(['students_progress' => $progress]);
    }
}
