<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    protected $fillable = [
        'user_id','order_tel','state','city',
        'order_state','order_city','order_address'
    ];
}
