<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileViewRequest extends FormRequest
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
            'viewer_id' => 'required|uuid|exists:users,id|different:viewed_user_id',
            'viewed_user_id' => 'required|uuid|exists:users,id|different:viewer_id',
            'viewed_at' => 'nullable|date',
            'ip_address' => 'nullable|string|max:45',
        ];
    }
}
