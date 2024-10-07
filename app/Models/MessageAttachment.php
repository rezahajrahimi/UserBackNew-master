<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageAttachment extends Model
{
    protected $fillable = ['message_id', 'cluster_id'];
    public function Messages()
    {
        return $this->belongsTo(Messages::class, 'message_id');
    }
    public function Clusters()
    {
        return $this->belongsTo(Clusters::class, 'cluster_id');
    }

}
