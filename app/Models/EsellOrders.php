<?php
namespace App\Models;
use Verta;

use Illuminate\Database\Eloquent\Model;

class EsellOrders extends Model
{
    protected $fillable = [
        'invoice_id','customer_id','factory_id','status','total','shipmentId'
    ];
    public function getCreatedAtAttribute($value)
    {
    return verta($value)->formatDifference();
    }
}
