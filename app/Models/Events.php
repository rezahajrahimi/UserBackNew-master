<?php

namespace App\Models;
use Verta;

use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    protected $fillable = ['type', 'user_id', 'factory_id', 'sharingLinks', 'item_type', 'status', 'details'];

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
    public function Factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
