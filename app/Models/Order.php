<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

#[Fillable(['external_id','affiliate_id', 'status', 'total', 'ordered_at'])]
class Order extends Model
{
    protected $table = 'orders';
    public $incrementing = true;

    use HasFactory,SoftDeletes;

    protected $casts = [
        'ordered_at' => 'datetime',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }
}
