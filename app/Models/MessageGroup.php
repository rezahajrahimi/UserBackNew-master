<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageGroup extends Model
{
    protected $fillable = ['factory_id', 'group_name'];

    public function Factory()
    {
        return $this->belongsTo(User::class, 'factory_id');
    }
    public function member()
    {
        return $this->hasMany(MessageGroupMember::class);
    }
    public static function boot() {
        parent::boot();

        static::deleting(function($Group) { // before delete() method call this
             $Group->member()->delete();
             // do the rest of the cleanup...
        });
    }
}
