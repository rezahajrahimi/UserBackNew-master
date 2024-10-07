<?php

namespace App\Models;
use Hekmatinasser\Verta\Verta;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'factoryId','customerName','orderNumber','status',
        'createddatein','completeDate','existence','count','user_name',
        'customer_id'
    ];
    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id','id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id','id');
    }
    public function getCreateddateinAttribute($value)
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
    public function order_images()
    {
        return $this->hasMany(OrderImages::class, 'order_id');
    }
    public function order_sizes()
    {
        return $this->hasMany(OrderSize::class, 'orderId');
    }
    public function order_size_names()
    {
        return $this->hasMany(OrderSize::class, 'orderId')->select('order_sizes.orderId'
        ,'order_sizes.clusterNameStone'
        ,'order_sizes.clusterNumber'

    );
        // return $this->hasMany('User')->select(array('id', 'username'));

    }
    public function order_loading()
    {
        return $this->hasMany(OrderLoading::class, 'order_id');
    }
}
