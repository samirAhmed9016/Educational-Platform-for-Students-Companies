<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'quiz_id' => ['sometimes', 'exists:quizzes,id'],
            'question_text' => ['sometimes', 'string'],
            'type' => ['sometimes', 'in:multiple_choice,true_false,text'],
            'options' => ['nullable', 'array'],
            'options.*' => ['string'],
            'correct_answer' => ['sometimes', 'string'],
            'points' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('type', $this->question->type ?? null);
            $options = $this->input('options', $this->question->options ?? null);

            if ($type === 'multiple_choice' && (empty($options) || !is_array($options))) {
                $validator->errors()->add('options', 'Options are required for multiple choice questions and must be an array.');
            }

            if ($type !== 'multiple_choice' && $options) {
                $validator->errors()->add('options', 'Options should be null or omitted for non multiple choice questions.');
            }
        });
    }
}
