<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'procurement_draft_item_id',
        'status',
        'total_ordered',
        'total_received',
        'created_by',
    ];

    public function draftItem()
    {
        return $this->belongsTo(ProcurementDraftItem::class, 'procurement_draft_item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    /**
     * Get remaining quantity to receive.
     */
    public function getRemainingAttribute(): int
    {
        return $this->total_ordered - $this->total_received;
    }

    /**
     * Generate next PO number.
     */
    public static function generatePoNumber(): string
    {
        $year = date('Y');
        $lastPo = self::whereYear('created_at', $year)->orderByDesc('id')->first();
        $nextNumber = $lastPo ? (int) substr($lastPo->po_number, -3) + 1 : 1;
        return sprintf('PO-%s-%03d', $year, $nextNumber);
    }
}
