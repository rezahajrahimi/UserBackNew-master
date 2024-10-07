<?php

namespace App\Models;
use Hekmatinasser\Verta\Verta;

use Illuminate\Database\Eloquent\Model;

class Cube extends Model
{
    protected $fillable = [
        'factoryId', 'nameCube', 'cubeNumber', 'minerNumber',
        'nameMiner', 'minerDegree', 'truckNumber', 'minerFactorId',
        'cubeDegree', 'cubeColorDegree', 'weight', 'length', 'imageThumb',
        'width', 'height', 'timeinsert', 'cuttingtime', 'isActive', 'sharingLiks', 'hasImage', 'noimage', 'warehouse',
        'show_in_esells','cuWarehouseId','bought_price'
    ];
    public function getTimeinsertAttribute($value)
    {
        if($value != null) {
        $v = explode("-",$value);
        $y= $v[0];
        $m= $v[1];
        $d= $v[2];
        $newDate = Verta::GregorianToJalali($y,$m,$d);
        return implode("-",$newDate);
        }

    }
    public function getCuttingtimeAttribute($value)
    {
        if($value != null) {
        $v = explode("-",$value);
        $y= $v[0];
        $m= $v[1];
        $d= $v[2];
        $newCu = Verta::GregorianToJalali($y,$m,$d);
        return implode("-",$newCu);
        }
    }
    public function cuWarehouseId()
    {
        return $this->belongsTo(CuWarehouse::class,'cuWarehouseId');
    }
    public function cuSaw()
    {
        return $this->hasOne(CuSaw::class,'factory_id','factoryId');
    }
    public function cuCuttingTime()
    {
        return $this->hasOne(CuCuttingTime::class,'cube_id');
    }
    public function cuCuttingAddition()
    {
        return $this->hasMany(CuCuttingAdditionItem::class,'cube_id');
    }
    public function clFinalStats()
    {
        return $this->hasMany(ClFinalStats::class,'cube_id');
    }
    public function splitted_Cube()
    {
        return $this->hasMany(SplittedCube::class,'cube_id');
    }

}
