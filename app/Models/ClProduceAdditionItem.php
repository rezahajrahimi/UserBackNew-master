<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClProduceAdditionItem extends Model
{
    use HasFactory;
    protected $guarded = ['id','factory_id','cu_cutting_addition_id','cube_id'];
    protected $visible = ['quantity','clProduceAddition'];

    protected $fillable = ['factory_id', 'cluster_id','cl_produce_addition_id','quantity'];
    public function clProduceAddition()
    {
        return $this->belongsTo(ClProduceAddition::class, 'cl_produce_addition_id','id');
    }
    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id','id');
    }
    public function cluster()
    {
        return $this->belongsTo(Cube::class,'cluster_id');
    }
}
