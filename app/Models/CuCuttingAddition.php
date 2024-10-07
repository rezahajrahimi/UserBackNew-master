<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuCuttingAddition extends Model
{
    use HasFactory;
    protected $guarded = ['id','factory_id'];
    protected $visible = ['name','unit'];
    protected $fillable = ['factory_id', 'name','unit'];
    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }
    public function CuCuttingAdditionItem()
    {
        return $this->hasMany(CuCuttingAdditionItem::class,'cu_cutting_addition_id');
    }

}
