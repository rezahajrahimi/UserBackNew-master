<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClWearhouseAxel extends Model
{
    use HasFactory;
    protected $guarded = ['id','factory_id','cl_Wearhouse_Id','clusterId'];
    protected $visible = ['row','col'];

    protected $fillable = ['factory_id', 'cl_Wearhouse_Id','row','col','clusters_id'];
    public function clWarehouse()
    {
        return $this->belongsTo(ClWearhouse::class, 'cl_Wearhouse_Id','id');
    }
    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id','id');
    }
    public function clusters()
    {
        return $this->belongsTo(Cluster::class,'id');
    }

}
