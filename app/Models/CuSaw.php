<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuSaw extends Model
{
    use HasFactory;
    protected $guarded = ['id','factory_id'];
    protected $visible = ['name'];

    protected $fillable = ['factory_id', 'name'];
    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }
}
