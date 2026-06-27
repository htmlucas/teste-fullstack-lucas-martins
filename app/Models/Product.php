<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'price', 'description', 'category', 'image'])]
class Product extends Model
{
    protected $table = 'products';
    public $incrementing = true;

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
