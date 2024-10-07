<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSize extends Model
{
    protected $fillable = [
        'orderId','length','width','count',
        'sum','clusterSizeId','clusterNumber','clusterNameStone','hasConvert',
        'outlying','widthOut','lenghtOut','status','ordered_number'
    ];
    public function order()
    {
        return $this->belongsTo(Order::class, 'orderId');
    }

}
