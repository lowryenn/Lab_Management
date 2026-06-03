<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'maintenance_date',
        'condition_after',
        'description',
        'bhp_item_id',
        'bhp_qty_used',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
        ];
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function bhpItem()
    {
        return $this->belongsTo(BhpItem::class, 'bhp_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
