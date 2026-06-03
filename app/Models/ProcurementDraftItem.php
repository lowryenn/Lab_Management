<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementDraftItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_draft_id',
        'name',
        'price',
        'quantity',
        'link',
        'replaces_inventory_id',
        'review_status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function draft()
    {
        return $this->belongsTo(ProcurementDraft::class, 'procurement_draft_id');
    }

    public function replacesInventory()
    {
        return $this->belongsTo(InventoryItem::class, 'replaces_inventory_id');
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    /**
     * Get subtotal.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }
}
