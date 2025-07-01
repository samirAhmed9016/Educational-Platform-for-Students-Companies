<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    /**
     * Store a new question for a quiz.
     */
    public function store(StoreQuestionRequest $request, Quiz $quiz): JsonResponse
    {
        $instructor = Auth::user();

        if ($quiz->instructor_id !== $instructor->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this quiz.',
            ], 403);
        }

        $validated = $request->validated();

        if ((int) $validated['quiz_id'] !== $quiz->id) {
            return response()->json([
                'message' => 'Quiz ID mismatch.',
            ], 422);
        }

        // Create the question
        $question = new Question();
        $question->quiz_id = $quiz->id;
        $question->question_text = $validated['question_text'];
        $question->type = $validated['type'];
        $question->options = $validated['options'] ?? null;
        $question->correct_answer = $validated['correct_answer'];
        $question->points = $validated['points'] ?? 1;

        $question->save();

        return response()->json([
            'message' => 'Question created successfully.',
            'question' => $question
        ], 201);
    }

    public function index(Quiz $quiz): JsonResponse
    {
        $instructor = Auth::user();

        if ($quiz->instructor_id !== $instructor->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this quiz.',
            ], 403);
        }

        $questions = $quiz->questions()->get();

        return response()->json([
            'quiz_id' => $quiz->id,
            'questions' => $questions,
        ]);
    }

    public function update(UpdateQuestionRequest $request, Question $question): JsonResponse
    {
        $instructor = Auth::user();

        if ($question->quiz->instructor_id !== $instructor->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this question.',
            ], 403);
        }

        $validated = $request->validated();

        // Optional: If changing quiz_id, check ownership and existence
        if (isset($validated['quiz_id']) && $validated['quiz_id'] !== $question->quiz_id) {
            $newQuiz = Quiz::where('id', $validated['quiz_id'])
                ->where('instructor_id', $instructor->id)
                ->first();

            if (! $newQuiz) {
                return response()->json([
                    'message' => 'The new quiz either does not exist or is not owned by you.',
                ], 422);
            }

            $question->quiz_id = $validated['quiz_id'];
        }

        if (isset($validated['question_text'])) {
            $question->question_text = $validated['question_text'];
        }

        if (isset($validated['type'])) {
            $question->type = $validated['type'];
        }

        if (array_key_exists('options', $validated)) {
            $question->options = $validated['options'];
        }

        if (isset($validated['correct_answer'])) {
            $question->correct_answer = $validated['correct_answer'];
        }

        if (isset($validated['points'])) {
            $question->points = $validated['points'];
        }

        $question->save();

        return response()->json([
            'message' => 'Question updated successfully.',
            'question' => $question,
        ]);
    }

    public function show(Question $question): JsonResponse
    {
        $instructor = Auth::user();

        if ($question->quiz->instructor_id !== $instructor->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this question.',
            ], 403);
        }

        return response()->json([
            'question' => $question,
        ]);
    }

    public function destroy(Question $question): JsonResponse
    {
        $instructor = Auth::user();

        if ($question->quiz->instructor_id !== $instructor->id) {
            return response()->json([
                'message' => 'Unauthorized. You do not own this question.',
            ], 403);
        }

        $question->delete();

        return response()->json([
            'message' => 'Question deleted successfully.',
        ]);
    }
}
