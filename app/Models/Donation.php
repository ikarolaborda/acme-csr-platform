<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Donation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'donation_number',
        'user_id',
        'campaign_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_id',
        'payment_details',
        'is_anonymous',
        'message',
        'paid_at',
        'failed_at',
        'failure_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'is_anonymous' => 'boolean',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($donation) {
            if (empty($donation->donation_number)) {
                $donation->donation_number = static::generateDonationNumber();
            }
        });

        static::updated(function ($donation) {
            if ($donation->isDirty('status') && $donation->status === 'completed') {
                $donation->campaign->updateTotals();
                $donation->user->increment('total_donated', $donation->amount);
            }
        });
    }

    /**
     * Generate a unique donation number.
     */
    public static function generateDonationNumber(): string
    {
        do {
            $number = 'DON-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (static::where('donation_number', $number)->exists());

        return $number;
    }

    /**
     * Get the user that made the donation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the campaign the donation is for.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Scope a query to only include completed donations.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include pending donations.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include donations by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Mark donation as completed.
     */
    public function markAsCompleted(string $transactionId, array $paymentDetails = []): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'payment_details' => $paymentDetails,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark donation as failed.
     */
    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'failed_at' => now(),
        ]);
    }

    /**
     * Check if donation is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if donation is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get donor display name.
     */
    public function getDonorNameAttribute(): string
    {
        return $this->is_anonymous ? 'Anonymous Donor' : $this->user->name;
    }
} 