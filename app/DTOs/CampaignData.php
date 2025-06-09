<?php

namespace App\DTOs;

class CampaignData
{
    public function __construct(
        public readonly ?int $user_id,
        public readonly string $title,
        public readonly ?string $slug,
        public readonly string $description,
        public readonly ?string $short_description,
        public readonly string $category,
        public readonly ?string $featured_image,
        public readonly float $goal_amount,
        public readonly ?float $current_amount,
        public readonly ?int $donor_count,
        public readonly ?int $views_count,
        public readonly string $start_date,
        public readonly string $end_date,
        public readonly ?string $status,
        public readonly ?string $impact_description,
    ) {}

    /**
     * Create a new instance from an array.
     */
    public static function from(array $data): self
    {
        return new self(
            user_id: $data['user_id'] ?? null,
            title: $data['title'],
            slug: $data['slug'] ?? null,
            description: $data['description'],
            short_description: $data['short_description'] ?? null,
            category: $data['category'],
            featured_image: $data['featured_image'] ?? null,
            goal_amount: (float) $data['goal_amount'],
            current_amount: (float) ($data['current_amount'] ?? 0),
            donor_count: (int) ($data['donor_count'] ?? 0),
            views_count: (int) ($data['views_count'] ?? 0),
            start_date: $data['start_date'],
            end_date: $data['end_date'],
            status: $data['status'] ?? 'draft',
            impact_description: $data['impact_description'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'user_id' => $this->user_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'category' => $this->category,
            'featured_image' => $this->featured_image,
            'goal_amount' => $this->goal_amount,
            'current_amount' => $this->current_amount,
            'donor_count' => $this->donor_count,
            'views_count' => $this->views_count,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'impact_description' => $this->impact_description,
        ], fn($value) => !is_null($value));
    }
} 