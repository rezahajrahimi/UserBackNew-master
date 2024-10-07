<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderShipments extends Model
{
    protected $fillable = [
        'customer_id','state','city','address',
        'phone','mobile','email'
    ];
}
