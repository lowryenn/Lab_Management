<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'qr_code',
        'action',
        'scanned_by',
        'ip_address',
        'user_agent',
        'location',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
