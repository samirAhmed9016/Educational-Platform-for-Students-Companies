<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;


class StoreQuizRequest extends FormRequest
{
    public function authorize()
    {
        // Assuming middleware already checks role, so allow true here.
        return true;
    }

    public function rules()
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
            'type' => ['required', 'in:course,lesson'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'passing_score' => ['required', 'integer', 'between:0,100'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $lessonId = $this->input('lesson_id');
            $courseId = $this->input('course_id');

            if ($type === 'course' && $lessonId !== null) {
                $validator->errors()->add('lesson_id', 'Lesson ID must be null for course-level quizzes.');
            }

            if ($type === 'lesson') {
                if ($lessonId === null) {
                    $validator->errors()->add('lesson_id', 'Lesson ID is required for lesson-level quizzes.');
                } else {
                    // Optional: Verify lesson belongs to course
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
