<?php

namespace App\Models;
use Verta;
use Illuminate\Database\Eloquent\Model;

class Factory extends Model
{
    protected $fillable = [
        'nameFac','telephoneFac','addressFac','logoFac','message','servicetype','serviceexpire',
        'state','description','website','bannerImg','esells_permission',
        'followers_count'
    ];
    public function getCreateddateinAttribute($value)
    {
        if($value != null) {
        $v = explode("-",$value);
        $y= $v[0];
        $m= $v[1];
        $d= $v[2];
            $newCu = Verta::GregorianToJalali($y, $m, $d);
        return implode("-",$newCu);
        }
    }
    public function getServiceexpireAttribute($value)
    {
        if($value != null) {
        $v = explode("-",$value);
        $y= $v[0];
        $m= $v[1];
        $d= $v[2];
            $newDate = Verta::GregorianToJalali($y, $m, $d);
        return implode("-",$newDate);
        }

    }
    public function followers()
    {
        return $this->hasMany(Followers::class);
    }
    public function message_groups()
    {
        return $this->hasMany(MessageGroup::class);
    }
    public function esells_settings()
    {
        return $this->hasOne(EsellsSettings::class);
    }
    public function file_storages()
    {
        return $this->hasOne(FileStorage::class);
    }
    public function events()
    {
        return $this->hasMany(Events::class);
    }
    public function factory_options()
    {
        return $this->hasMany(FactoryOptions::class);
    }
    public function order_loading()
    {
        return $this->hasMany(OrderLoading::class);
    }
    public function splitted_cubes()
    {
        return $this->hasMany(SplittedCube::class);
    }


}
