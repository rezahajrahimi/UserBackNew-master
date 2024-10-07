<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClWearhouse extends Model
{

    use HasFactory;
    protected $guarded = ['id','factoryId'];
    protected $visible = ['name'];

    protected $fillable = ['factoryId', 'name'];
    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factoryId');
    }
    // public function cl_wearhouse_axels()
    // {
    //     return $this->hasMany(ClWearhouseAxel::class);
    // }
}
