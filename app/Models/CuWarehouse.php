<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuWarehouse extends Model
{
    use HasFactory;
    protected $guarded = ['id','factoryId'];
    protected $visible = ['name'];

    protected $fillable = ['factoryId', 'name','address','tel'];
    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factoryId');
    }

}
