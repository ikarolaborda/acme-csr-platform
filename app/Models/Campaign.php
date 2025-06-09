<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'user_id',
        'category',
        'goal_amount',
        'current_amount',
        'donors_count',
        'start_date',
        'end_date',
        'status',
        'featured_image',
        'images',
        'documents',
        'is_featured',
        'views_count',
        'impact_description',
        'milestones',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'goal_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'images' => 'array',
        'documents' => 'array',
        'milestones' => 'array',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($campaign) {
            if (empty($campaign->slug)) {
                $campaign->slug = Str::slug($campaign->title);
            }
        });
    }

    /**
     * Get the user that created the campaign.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin that approved the campaign.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the donations for the campaign.
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Scope a query to only include active campaigns.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope a query to only include featured campaigns.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include campaigns by category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to search campaigns.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Calculate campaign progress percentage.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->goal_amount == 0) {
            return 0;
        }

        $percentage = ($this->current_amount / $this->goal_amount) * 100;
        return min(100, round($percentage, 2));
    }

    /**
     * Check if campaign is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->start_date <= now() 
            && $this->end_date >= now();
    }

    /**
     * Check if campaign has ended.
     */
    public function hasEnded(): bool
    {
        return $this->end_date < now();
    }

    /**
     * Check if campaign has reached its goal.
     */
    public function hasReachedGoal(): bool
    {
        return $this->current_amount >= $this->goal_amount;
    }

    /**
     * Get days remaining for the campaign.
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->hasEnded()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->end_date));
    }

    /**
     * Get featured image URL.
     */
    public function getFeaturedImageUrlAttribute(): string
    {
        return $this->featured_image 
            ? asset('storage/' . $this->featured_image) 
            : asset('images/default-campaign.jpg');
    }

    /**
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Update campaign totals.
     */
    public function updateTotals(): void
    {
        $donations = $this->donations()->where('status', 'completed');
        
        $this->update([
            'current_amount' => $donations->sum('amount'),
            'donors_count' => $donations->distinct('user_id')->count('user_id'),
        ]);
    }
} 