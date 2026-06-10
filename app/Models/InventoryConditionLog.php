<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryConditionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'condition_before',
        'condition_after',
        'description',
        'user_id',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
