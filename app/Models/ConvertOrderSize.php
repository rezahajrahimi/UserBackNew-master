<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConvertOrderSize extends Model
{
    protected $fillable = [
        'orderSizeId','lengthCs','widthCs','countCs','sumCs',
        'statusCs','orderId'
    ];
}
