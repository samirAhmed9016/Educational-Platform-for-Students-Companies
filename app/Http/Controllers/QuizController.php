<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class QuizController extends Controller
{
    /**
     * Store a new quiz by instructor.
     */
    public function store(StoreQuizRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $instructor = Auth::user();

        $course = Course::where('id', $validated['course_id'])
            ->where('instructor_id', $instructor->id)
            ->first();

        if (! $course) {
            return response()->json([
                'message' => 'Unauthorized. You do not own the selected course.',
            ], 403);
        }

        if ($validated['type'] === 'lesson' && isset($validated['lesson_id'])) {
            $lessonBelongs = $course->lessons()->where('id', $validated['lesson_id'])->exists();

            if (! $lessonBelongs) {
                return response()->json([
                    'message' => 'The selected lesson does not belong to the course.',
                ], 422);
            }
        }

        $quiz = new Quiz();
        $quiz->course_id = $validated['course_id'];
        $quiz->lesson_id = $validated['lesson_id'] ?? null;
        $quiz->type = $validated['type'];
        $quiz->title = $validated['title'];
        $quiz->instructions = $validated['instructions'] ?? null;
        $quiz->duration_minutes = $validated['duration_minutes'] ?? null;
        $quiz->passing_score = $validated['passing_score'];
        $quiz->instructor_id = $instructor->id;

        $quiz->save();

        return response()->json([
            'message' => 'Quiz created successfully.',
            'quiz' => $quiz
        ], 201);
    }


    public function index(): JsonResponse
    {
        $instructor = Auth::user();

        $quizzes = Quiz::where('instructor_id', $instructor->id)
            ->with(['course', 'lesson'])
            ->get();

        return response()->json([
            'message' => 'Quizzes fetched successfully.',
            'quizzes' => $quizzes
        ]);
    }

    public function show(Quiz $quiz): JsonResponse
    {
        $instructor = Auth::user();

        if ($quiz->instructor_id !== $instructor->id) {
            return response()->json([
                'message' => 'Unauthorized access to this quiz.'
            ], 403);
        }

        return response()->json([
            'message' => 'Quiz retrieved successfully.',
            'quiz' => $quiz->load(['course', 'lesson', 'questions'])
        ]);
    }


    public function update(UpdateQuizRequest $request, Quiz $quiz): JsonResponse
    {
        $instructor = Auth::user();

        if ($quiz->instructor_id !== $instructor->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this quiz.'
            ], 403);
        }

        $validated = $request->validated();

        if (isset($validated['course_id'])) {
            $course = Course::where('id', $validated['course_id'])
                ->where('instructor_id', $instructor->id)
                ->first();

            if (! $course) {
                return response()->json([
                    'message' => 'Unauthorized. You do not own the selected course.'
                ], 403);
            }

            $quiz->course_id = $validated['course_id'];
        }

        if (isset($validated['lesson_id'])) {
            $lessonBelongs = Lesson::where('id', $validated['lesson_id'])
                ->where('course_id', $quiz->course_id)
                ->exists();

            if (! $lessonBelongs) {
                return response()->json([
                    'message' => 'The selected lesson does not belong to the course.'
                ], 422);
            }

            $quiz->lesson_id = $validated['lesson_id'];
        }

        $quiz->fill($validated);
        $quiz->save();

        return response()->json([
            'message' => 'Quiz updated successfully.',
            'quiz' => $quiz
        ]);
    }


    public function destroy(Quiz $quiz): JsonResponse
    {
        $instructor = Auth::user();

        if ($quiz->instructor_id !== $instructor->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this quiz.'
            ], 403);
        }

        $quiz->delete();

        return response()->json([
            'message' => 'Quiz deleted successfully.'
        ]);
    }


    public function listLessonQuizzes(): JsonResponse
    {
        $user = auth()->user();

        $enrolledCourseIds = Enrollment::where('user_id', $user->id)->pluck('course_id');

        $lessonQuizzes = Quiz::whereIn('course_id', $enrolledCourseIds)
            ->where('type', 'lesson')
            ->get()
            ->filter(function ($quiz) use ($user) {
                return Progress::where('user_id', $user->id)
                    ->where('lesson_id', $quiz->lesson_id)
                    ->where('is_completed', true)
                    ->exists();
            });

        return response()->json(['quizzes' => $lessonQuizzes->values()]);
    }

    public function listCourseQuizzes(): JsonResponse
    {
        $user = auth()->user();

        $enrolledCourseIds = Enrollment::where('user_id', $user->id)->pluck('course_id');

        $courseQuizzes = Quiz::whereIn('course_id', $enrolledCourseIds)
            ->where('type', 'course')
            ->get()
            ->filter(function ($quiz) use ($user) {
                $lessonQuizzes = Quiz::where('course_id', $quiz->course_id)
                    ->where('type', 'lesson')
                    ->get();

                foreach ($lessonQuizzes as $lessonQuiz) {
                    $submitted = QuizSubmission::where('user_id', $user->id)
                        ->where('quiz_id', $lessonQuiz->id)
                        ->exists();
                    if (!$submitted) return false;
                }

                return CourseUser::where('user_id', $user->id)
                    ->where('course_id', $quiz->course_id)
                    ->whereNotNull('completed_at')
                    ->exists();
            });

        return response()->json(['quizzes' => $courseQuizzes->values()]);
    }


    public function ShowQuiz(Quiz $quiz): JsonResponse
    {
        $user = auth()->user();

        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->exists();

        if (! $isEnrolled) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        if ($quiz->type === 'lesson') {
            $lessonCompleted = Progress::where('user_id', $user->id)
                ->where('lesson_id', $quiz->lesson_id)
                ->where('is_completed', true)
                ->exists();

            if (! $lessonCompleted) {
                return response()->json(['message' => 'You must complete the lesson to access this quiz.'], 403);
            }
        } elseif ($quiz->type === 'course') {

            $lessonQuizzes = Quiz::where('course_id', $quiz->course_id)
                ->where('type', 'lesson')
                ->get();

            foreach ($lessonQuizzes as $lessonQuiz) {
                $quizPassed = Progress::where('user_id', $user->id)
                    ->where('lesson_id', $lessonQuiz->lesson_id)
                    ->where('quiz_passed', true)
                    ->exists();

                if (! $quizPassed) {
                    return response()->json(['message' => 'You must pass all lesson quizzes to access this course quiz.'], 403);
                }
            }

            $courseCompleted = CourseUser::where('user_id', $user->id)
                ->where('course_id', $quiz->course_id)
                ->whereNotNull('completed_at')
                ->exists();

            if (! $courseCompleted) {
                return response()->json(['message' => 'You must complete the course to access this quiz.'], 403);
            }
        }

        $quiz->load('questions');

        return response()->json([
            'quiz' => new QuizResource($quiz)
        ]);
    }
}
