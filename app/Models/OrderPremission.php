<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPremission extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id','order_view','order_add','order_del',
        'order_update'
    ];
}
