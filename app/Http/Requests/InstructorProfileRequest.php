<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstructorProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bio' => 'required|string',
            'skills' => 'required|string',
            'education_background' => 'required|string|max:255',
            'years_of_experience' => 'required|integer|min:0',
            'linkedin_url' => 'nullable|url',
            'portfolio_url' => 'nullable|url',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'bio.required' => 'Bio is required.',
            'skills.required' => 'Skills are required.',
            'years_of_experience.required' => 'Years of experience is required.',
        ];
    }
}
