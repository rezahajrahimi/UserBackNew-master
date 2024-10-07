<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class socialnet extends Model
{
    protected $fillable = [
        'factoryId','instagram','twitter','youtube','telegram','facebook','linkedin'
    ];
}
