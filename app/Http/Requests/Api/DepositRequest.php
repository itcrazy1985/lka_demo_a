<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
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
            'agent_payment_type_id' => ['required', 'exists:banks,id'],
            'amount' => ['required', 'integer', 'min:1000'],
            'image' => ['required', 'file', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'refrence_no' => ['required', 'digits:6'],
        ];
    }
}