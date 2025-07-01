<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizRequest extends FormRequest
{
    public function authorize()
    {
        return true;  // Assume user auth done via middleware
    }

    public function rules()
    {
        return [
            'quiz_id' => ['required', 'exists:quizzes,id'],
            'answers' => ['required', 'array'],
            'answers.*' => ['string'], // Can be enhanced per question type if needed
        ];
    }
}
