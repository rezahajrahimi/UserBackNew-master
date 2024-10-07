<?php

namespace App\Models;
use Verta;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $fillable = ['factory_id', 'user_id', 'message_text', 'status', 'type'];
    public function getCreatedAtAttribute($value)
    {
        return verta(verta($value))->formatDifference();
    }
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function Factory()
    {
        return $this->belongsTo(User::class, 'factory_id');
    }
    public function MessageAttachment()
    {
        return $this->hasMany(MessageAttachment::class);
    }
}
