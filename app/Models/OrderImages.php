<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderImages extends Model
{
    use HasFactory;
    protected $guarded = ['id','order_loading_id','order_id'];
    protected $fillable = [
        'factory_id','order_id','order_loading_id','image_url'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function order_loading()
    {
        return $this->belongsTo(OrderLoading::class, 'order_loading_id');
    }

}
