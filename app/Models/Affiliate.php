<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Attributes\Fillable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['external_id','username', 'email', 'password', 'status'])]
class Affiliate extends Model
{
    protected $table = 'affiliates';
    public $incrementing = true;

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'affiliate_id');
    }
}
