<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSearchHistoryRequest extends FormRequest
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
            'user_id' => 'required|uuid|exists:users,id',
            'search_query' => 'required|string|max:255',
            'result_user_id' => 'nullable|uuid|exists:users,id',
            'result_status' => 'nullable|in:FOUND,NOT_FOUND,NOT_REGISTERED',
            'searched_at' => 'nullable|date',
        ];
    }
}
