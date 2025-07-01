<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;


class UpdateQuizRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'course_id' => ['sometimes', 'exists:courses,id'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
            'type' => ['sometimes', 'in:course,lesson'],
            'title' => ['sometimes', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'passing_score' => ['sometimes', 'integer', 'between:0,100'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('type', $this->quiz->type ?? null);
            $lessonId = $this->input('lesson_id', $this->quiz->lesson_id ?? null);
            $courseId = $this->input('course_id', $this->quiz->course_id ?? null);

            if ($type === 'course' && $lessonId !== null) {
                $validator->errors()->add('lesson_id', 'Lesson ID must be null for course-level quizzes.');
            }

            if ($type === 'lesson') {
                if ($lessonId === null) {
                    $validator->errors()->add('lesson_id', 'Lesson ID is required for lesson-level quizzes.');
                } else {
                    $lessonBelongsToCourse = DB::table('lessons')
                        ->where('id', $lessonId)
                        ->where('course_id', $courseId)
                        ->exists();

                    if (! $lessonBelongsToCourse) {
                        $validator->errors()->add('lesson_id', 'The lesson must belong to the selected course.');
                    }
                }
            }
        });
    }
}
