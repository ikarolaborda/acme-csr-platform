<?php

namespace App\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('campaigns', 'slug')->ignore($this->route('id'))],
            'description' => ['sometimes', 'string', 'min:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'category' => [
                'sometimes',
                Rule::in(['environment', 'education', 'health', 'community', 'disaster_relief', 'poverty', 'other'])
            ],
            'goal_amount' => ['sometimes', 'numeric', 'min:100', 'max:1000000'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'status' => [
                'sometimes',
                Rule::in(['draft', 'pending', 'active', 'completed', 'cancelled'])
            ],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['string', 'max:255'],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => ['string', 'max:255'],
            'impact_description' => ['nullable', 'string'],
            'milestones' => ['nullable', 'array'],
            'milestones.*.title' => ['required_with:milestones', 'string', 'max:255'],
            'milestones.*.description' => ['required_with:milestones', 'string'],
            'milestones.*.target_amount' => ['required_with:milestones', 'numeric', 'min:0'],
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
            'title.string' => 'Campaign title must be a valid string.',
            'description.min' => 'Campaign description must be at least 100 characters.',
            'category.in' => 'Invalid campaign category selected.',
            'goal_amount.min' => 'Goal amount must be at least $100.',
            'goal_amount.max' => 'Goal amount cannot exceed $1,000,000.',
            'end_date.after' => 'End date must be after the start date.',
            'status.in' => 'Invalid campaign status.',
        ];
    }
} 