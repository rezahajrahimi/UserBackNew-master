<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClusterEsell extends Model
{
    protected $fillable = [
        'cluster_id','show_price','price','alias_title','tiny_text','description','statistics','rate'
    ];
}
