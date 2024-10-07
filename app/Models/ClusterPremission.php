<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClusterPremission extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id','cluster_view','cluster_add','cluster_del',
        'cluster_update'
    ];
}
