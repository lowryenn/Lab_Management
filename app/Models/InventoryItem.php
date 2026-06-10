<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'label_code',
        'name',
        'category',
        'description',
        'brand',
        'condition',
        'room_id',
        'item_type_id',
        'price',
        'purchase_date',
        'acquisition_year',
        'photo',
        'is_labeled',
        'qr_internal',
        'qr_kampus',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'replaced_from',
        'replaced_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'approved_at' => 'datetime',
            'is_labeled' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function conditionLogs()
    {
        return $this->hasMany(InventoryConditionLog::class);
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    public function replacedFromItem()
    {
        return $this->belongsTo(self::class, 'replaced_from');
    }

    public function replacedByItem()
    {
        return $this->belongsTo(self::class, 'replaced_by');
    }

    /**
     * Check if item is locked (approved by Kaprodi).
     */
    public function isLocked(): bool
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Get condition label in Indonesian.
     */
    public function getConditionLabelAttribute(): string
    {
        return match ($this->condition) {
            'baik' => 'Baik',
            'kurang_baik' => 'Kurang Baik',
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat' => 'Rusak Berat',
            default => $this->condition,
        };
    }

    /**
     * Get approval status label.
     */
    public function getApprovalLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'pending' => 'Menunggu Review',
            'approved' => 'Disetujui (Locked)',
            'rejected' => 'Ditolak',
            default => $this->approval_status,
        };
    }

    /**
     * Scope: only active items.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: only inventaris category.
     */
    public function scopeInventaris($query)
    {
        return $query->where('category', 'inventaris');
    }

    /**
     * Scope: only BHP category.
     */
    public function scopeBhpCategory($query)
    {
        return $query->where('category', 'bhp');
    }
}
