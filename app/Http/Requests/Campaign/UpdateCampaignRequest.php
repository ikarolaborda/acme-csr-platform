<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'category' => ['sometimes', 'string', 'in:environment,education,health,community,disaster_relief,poverty,other'],
            'goal_amount' => ['sometimes', 'numeric', 'min:100'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB max
            'impact_description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:draft,pending,active,completed,cancelled'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category.in' => 'Invalid category selected',
            'goal_amount.min' => 'Goal amount must be at least $100',
            'end_date.after' => 'End date must be after start date',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'The image must be a JPEG, PNG, JPG, or GIF file',
            'image.max' => 'The image size must not exceed 2MB',
            'status.in' => 'Invalid status',
        ];
    }
} 