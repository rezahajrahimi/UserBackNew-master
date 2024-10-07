<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CubeEsell extends Model
{
    protected $fillable = [
        'cube_id','show_price','price','alias_title','tiny_text','description','statistics'
    ];
}
