<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'draft_number',
        'room_id',
        'user_id',
        'status',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ProcurementDraftItem::class);
    }

    /**
     * Calculate total estimated price of approved items.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Calculate total approved price.
     */
    public function getApprovedTotalAttribute(): float
    {
        return $this->items->where('review_status', 'approved')->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Generate next draft number.
     */
    public static function generateDraftNumber(): string
    {
        $year = date('Y');
        $lastDraft = self::whereYear('created_at', $year)->orderByDesc('id')->first();
        $nextNumber = $lastDraft ? (int) substr($lastDraft->draft_number, -3) + 1 : 1;
        return sprintf('DRF-%s-%03d', $year, $nextNumber);
    }
}
