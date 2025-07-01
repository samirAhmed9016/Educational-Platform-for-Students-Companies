<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyProfileRequest extends FormRequest
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
            'company_name' => 'required|string|max:255',
            'industry' => 'required|string|max:255',
            'company_description' => 'required|string',
            'website_url' => 'nullable|url',
            'contact_person_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'company_size' => 'required|in:1-10,10-50,50-200,200+',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }


    public function messages()
    {
        return [
            'company_name.required' => 'Company name is required.',
            'industry.required' => 'Industry is required.',
            'company_description.required' => 'Company description is required.',
        ];
    }
}
