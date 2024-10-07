<?php

namespace App\Models;
use Verta;

use Illuminate\Database\Eloquent\Model;

class Followers extends Model
{
    protected $fillable = ['factory_id','user_id','status'];
    public function Factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function group()
    {
        return $this->hasMany(MessageGroupMember::class);
    }
    public function getCreatedAtAttribute($value)
    {
        return verta(verta($value))->formatDifference();
    }
}
