<?php

namespace App\Models;
use Verta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuCuttingTime extends Model
{
    use HasFactory;
    protected $guarded = ['id','factory_id','cube_id'];
    protected $visible = ['started_at','ended_at'];

    protected $fillable = ['factory_id', 'cube_id','saw_id','started_at','ended_at'];
    public function getStartedAtAttribute($value)
    {
        return verta($value)->format('Y-m-d h:i:s');
    }
    public function getEndedAtAttribute($value)
    {
        return verta($value)->format('Y-m-d h:i:s');
    }

    public function factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }
    public function saw()
    {
        return $this->belongsTo(CuSaw::class, 'saw_id');
    }
    public function cube()
    {
        return $this->belongsTo(Factory::class, 'cube_id');
    }
}
