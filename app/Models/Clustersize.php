<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clustersize extends Model
{
    protected $fillable = [
        'clusterId','length','width','count',
        'sum','exist_number'
    ];
    public function clFinalStats()
    {
        return $this->hasMany(ClFinalStats::class,'cluster_size_id');
    }
}
