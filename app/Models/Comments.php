<?php

namespace App\Models;
use Verta;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    protected $fillable = [
        'userId','parentId','itemId','rateNumber','itemType','comment','factoryId','status','hasChild'
    ];
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
}
