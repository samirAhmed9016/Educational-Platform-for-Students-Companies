<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
            'code' => 'required|string|exists:verification_codes,code', // Ensure the OTP code exists in the verification_codes table
            'email' => 'required|email|exists:users,email'

        ];
    }


    public function messages()
    {
        return [
            'code.exists' => 'The OTP code is invalid or expired.',
        ];
    }
}
