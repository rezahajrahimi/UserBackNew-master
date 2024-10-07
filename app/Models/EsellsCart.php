<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EsellsCart extends Model
{
    protected $fillable = [
        'customer_id','cube_id','cluster_id','count'
    ];
}

