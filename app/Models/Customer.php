<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $guarded = ['id','factory_id'];
    protected $visible = ['name','telephone','address'];
    protected $fillable = [
        'factory_id',
        'name',
        'telephone',
        'address',
    ];

    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id','id');
    }
}
