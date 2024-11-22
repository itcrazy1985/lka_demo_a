<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlayerRequest extends FormRequest
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
            'user_name' => ['required', 'string', 'unique:users,user_name'],
            'name' => ['required', 'string'],
            'phone' => ['required', 'regex:/^[0-9]+$/'],
            'password' => 'required|min:6',
            'amount' => 'nullable|numeric',
            'payment_type_id' => ['required'],
            'account_name' => ['required', 'string'],
            'account_number' => ['required', 'regex:/^[0-9]+$/']
        ];
    }
}
