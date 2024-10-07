<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLoading extends Model
{
    use HasFactory;
    protected $guarded = ['factory_id','order_id'];
    protected $fillable = [
        'factory_id','order_id','driver_name','truck_number','driver_phone',
        'loading_date','cargo_weight','description',
        'shipping_address','has_image'
    ];
    /**
     * Get the user that owns the OrderLoading
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }
    /**
     * Get all of the comments for the OrderLoading
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function order_images()
    {
        return $this->hasMany(OrderImages::class, 'order_loading_id');
    }
}
