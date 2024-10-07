<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClFinalStats extends Model
{
    use HasFactory;
    protected $guarded = ['id','factory_id','cube_id','cluster_id','cluster_size_id'];
    protected $hidden = ['cube_id','cluster_id','cluster_size_id','factory_id'];
    protected $fillable = ['factory_id', 'cube_id','cluster_id','cluster_size_id','final_existence','description'];
    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }
    public function cube()
    {
        return $this->belongsTo(Cube::class,'cube_id');
    }
    public function cluster()
    {
        return $this->belongsTo(Clusters::class,'cluster_id');
    }
    public function clusterSize()
    {
        return $this->belongsTo(Clustersize::class,'cluster_size_id');
    }

}
