<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BhpTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bhp_item_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'description',
        'batch_id',
        'user_id',
    ];

    public function bhpItem()
    {
        return $this->belongsTo(BhpItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
