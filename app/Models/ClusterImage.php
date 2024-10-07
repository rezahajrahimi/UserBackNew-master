<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClusterImage extends Model
{
    protected $fillable = ['clusters_id', 'imageSrc'];
    public function Clusters()
    {
        return $this->belongsTo(Clusters::class, 'clusters_id');
    }
}
