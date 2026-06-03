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
        'condition',
        'room_id',
        'price',
        'purchase_date',
        'photo',
        'is_labeled',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'is_labeled' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    /**
     * Get condition label in Indonesian.
     */
    public function getConditionLabelAttribute(): string
    {
        return match ($this->condition) {
            'baik' => 'Baik (Normal)',
            'kurang_baik' => 'Kurang Baik (Butuh Perbaikan Ringan)',
            'rusak_berat' => 'Rusak Berat (Tidak Bisa Digunakan)',
            default => $this->condition,
        };
    }
}
