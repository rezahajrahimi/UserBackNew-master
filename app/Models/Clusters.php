<?php

namespace App\Models;
use Hekmatinasser\Verta\Verta;

use Illuminate\Database\Eloquent\Model;

class Clusters extends Model
{
    // protected $hidden = ['clWearhouseId'];


    protected $fillable = ['factoryId', 'clusterNumber', 'clusterNameStone', 'clusterDegree', 'count', 'createddatein', 'existence', 'hasImage', 'imageThumb', 'sharingLinks', 'ClusterTypeStones', 'warehouse', 'show_in_esells','clWearhouseId','finished_price','finished_price_unit','type'];
    public function getCreateddateinAttribute($value)
    {
        if ($value != null) {
            $v = explode('-', $value);
            $y = $v[0];
            $m = $v[1];
            $d = $v[2];
            $newCu = Verta::GregorianToJalali($y, $m, $d);
            return implode('-', $newCu);
        }
    }
    public function getCreatedAtAttribute($value)
    {
        return verta(verta($value))->formatDifference();
    }
    public function cluster_images()
    {
        return $this->hasMany(ClusterImage::class);
    }

    public function clWarehouseAxel()
    {
        return $this->hasOne(ClWearhouseAxel::class,'clusterId');
    }
    public function clWarehouseId()
    {
        return $this->belongsTo(ClWearhouse::class,'clWearhouseId');
    }
    public function clFinalStats()
    {
        return $this->hasOne(ClFinalStats::class,'cluster_id');
    }
}


