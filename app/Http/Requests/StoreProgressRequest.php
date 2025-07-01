<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'student';;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'required|exists:lessons,id',
            'progress_percentage' => 'required|integer|min:0|max:100',
            'is_completed' => 'sometimes|boolean',
            'completed_at' => 'nullable|date',
            'quiz_passed' => 'sometimes|boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
