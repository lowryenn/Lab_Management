<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'room_id'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    /**
     * Total active inventory units under this item type (auto-count).
     */
    public function getActiveCountAttribute(): int
    {
        return $this->inventoryItems()->where('status', 'active')->count();
    }
}
