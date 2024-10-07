<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageGroupMember extends Model
{
    protected $fillable = ['group_id', 'user_id'];
    public function MessageGroup()
    {
        return $this->belongsTo(User::class, 'group_id');
    }
    public function followers()
    {
        return $this->belongsTo(Followers::class);
    }
}
