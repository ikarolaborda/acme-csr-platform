<?php

namespace App\Http\Requests\Donations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateDonationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'integer', 'exists:campaigns,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'],
            'currency' => ['nullable', 'string', 'size:3', 'in:USD,EUR,GBP,CAD'],
            'payment_method' => ['required', 'string', Rule::in(['credit_card', 'debit_card', 'paypal', 'bank_transfer'])],
            'is_anonymous' => ['boolean'],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'campaign_id.required' => 'Please select a campaign to donate to.',
            'campaign_id.exists' => 'Selected campaign does not exist.',
            'amount.required' => 'Donation amount is required.',
            'amount.min' => 'Minimum donation amount is $1.',
            'amount.max' => 'Maximum donation amount is $100,000.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'message.max' => 'Message cannot exceed 500 characters.',
        ];
    }
} 