<?php

namespace App\Models;
use Verta;

use Illuminate\Database\Eloquent\Model;

class EsellsOrderMessage extends Model
{
    protected $fillable = [
        'esell_order_id','customer_id','factory_id','message','status','type'
    ];
    public function getCreatedAtAttribute($value)
    {
        return verta(verta($value))->formatDifference();
    }
}
