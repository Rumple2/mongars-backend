<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
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
            'plan' => 'required|in:FREE,PREMIUM',
            'started_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:started_at',
            'is_active' => 'boolean',
            'payment_method' => 'nullable|string|max:50',
            'amount' => 'nullable|numeric',
            'currency' => 'nullable|string|size:3',
        ];
    }
}
