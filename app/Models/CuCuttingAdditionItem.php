<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuCuttingAdditionItem extends Model
{
    use HasFactory;
    protected $guarded = ['id','factory_id','cu_cutting_addition_id','cube_id'];
    protected $visible = ['quantity','cuCuttingAddition'];

    protected $fillable = ['factory_id', 'cube_id','cu_cutting_addition_id','quantity'];
    public function cuCuttingAddition()
    {
        return $this->belongsTo(CuCuttingAddition::class, 'cu_cutting_addition_id','id');
    }
    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id','id');
    }
    public function cube()
    {
        return $this->belongsTo(Cube::class,'cube_id');
    }
}
