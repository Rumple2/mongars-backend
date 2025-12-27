<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoupleRequestRequest extends FormRequest
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
            'sender_id' => 'required|uuid|exists:users,id|different:receiver_id',
            'receiver_id' => 'required|uuid|exists:users,id|different:sender_id',
            'status' => 'in:PENDING,ACCEPTED,REJECTED,CANCELLED',
            'message' => 'nullable|string',
        ];
    }
}
