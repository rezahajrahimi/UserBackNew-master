<?php

namespace App\Models;
use Verta;
use Illuminate\Database\Eloquent\Model;

class ClusterLog extends Model
{
    protected $fillable = [
        'factoryId','clusterId','userName','oprType','oprText'
    ];
    public function getCreatedAtAttribute($value)
    {
        return verta(verta($value))->formatDifference();
    }
}
