<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'capacity',
    ];

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function bhpItems()
    {
        return $this->hasMany(BhpItem::class);
    }

    public function procurementDrafts()
    {
        return $this->hasMany(ProcurementDraft::class);
    }
}
