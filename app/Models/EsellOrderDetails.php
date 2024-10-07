<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EsellOrderDetails extends Model
{
    protected $fillable = [
        'esells_order_id','customer_id','cube_id','cube_price','cluster_id','cluster_price','cluster_size_id','cluster_size_count'
    ];
}
