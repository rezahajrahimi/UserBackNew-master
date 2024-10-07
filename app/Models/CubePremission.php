<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CubePremission extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id','cube_view','cube_add','cube_del',
        'cube_update','cube_cutting'
    ];
}
