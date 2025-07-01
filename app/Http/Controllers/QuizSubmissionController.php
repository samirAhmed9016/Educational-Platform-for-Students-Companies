<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitQuizRequest;
use App\Models\Certificate;
use App\Models\CourseUser;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizSubmissionController extends Controller
{
    public function submit(SubmitQuizRequest $request, Quiz $quiz): JsonResponse
    {
        $user = Auth::user();

        // Check enrollment
        $enrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->exists();

        if (! $enrolled) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        $validated = $request->validated();
        $submittedAnswers = $validated['answers']; // ['question_id' => 'user_answer']

        // Validate questions exist for this quiz
        $questions = $quiz->questions;
        $totalScore = 0;
        $correctCount = 0;

        foreach ($questions as $question) {
            $submitted = $submittedAnswers[$question->id] ?? null;

            if ($submitted === null) {
                continue; // Unanswered
            }

            if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                if ($submitted === $question->correct_answer) {
                    $correctCount++;
                    $totalScore++;
                }
            }
        }

        $score = $questions->count() > 0 ? round(($totalScore / $questions->count()) * 100, 2) : 0;
        $passed = $score >= $quiz->passing_score;

        // Prevent duplicate submissions
        $existing = QuizSubmission::where('quiz_id', $quiz->id)->where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json(['message' => 'You have already submitted this quiz.'], 422);
        }

        // Save submission
        $submission = QuizSubmission::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'answers' => $submittedAnswers,
            'score' => $score,
            'passed' => $passed,
            'submitted_at' => now(),
        ]);

        // If passed and it's a lesson quiz → update progress table
        if ($quiz->type === 'lesson' && $passed && $quiz->lesson_id) {
            $progress = Progress::firstOrNew([
                'user_id' => $user->id,
                'course_id' => $quiz->course_id,
                'lesson_id' => $quiz->lesson_id,
            ]);

            $progress->quiz_passed = true;

            // Mark lesson as completed if not already done
            if (! $progress->is_completed) {
                $progress->is_completed = true;
                $progress->completed_at = now();
            }

            $progress->save();
        }

        // Check if all lessons in course are completed
        $completedLessons = Progress::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->where('is_completed', true)
            ->count();

        $totalLessons = Lesson::where('course_id', $quiz->course_id)->count();

        if ($completedLessons === $totalLessons && $totalLessons > 0) {
            CourseUser::updateOrCreate(
                ['user_id' => $user->id, 'course_id' => $quiz->course_id],
                ['completed_at' => now()]
            );
        }

        return response()->json([
            'message' => 'Quiz submitted successfully.',
            'submission' => $submission,
        ]);
    }
}
