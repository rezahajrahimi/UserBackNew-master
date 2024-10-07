<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rundata extends Model
{
    protected $table = 'rundatas';

    protected $fillable = [
        'runmode','runtime','rundistance','hraverage',
        'runcalorie','ygraph','xgraph','gpsdistance','daterun'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
