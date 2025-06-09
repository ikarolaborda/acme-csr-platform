<?php

namespace App\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCampaignRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:campaigns,slug'],
            'description' => ['required', 'string', 'min:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'category' => [
                'required',
                Rule::in(['environment', 'education', 'health', 'community', 'disaster_relief', 'poverty', 'other'])
            ],
            'goal_amount' => ['required', 'numeric', 'min:100', 'max:1000000'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
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
            'title.required' => 'Campaign title is required.',
            'description.required' => 'Campaign description is required.',
            'description.min' => 'Campaign description must be at least 100 characters.',
            'category.required' => 'Please select a campaign category.',
            'category.in' => 'Invalid campaign category selected.',
            'goal_amount.required' => 'Goal amount is required.',
            'goal_amount.min' => 'Goal amount must be at least $100.',
            'goal_amount.max' => 'Goal amount cannot exceed $1,000,000.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'end_date.required' => 'End date is required.',
            'end_date.after' => 'End date must be after the start date.',
        ];
    }
} 