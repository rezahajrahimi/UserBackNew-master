<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactoryOptions extends Model
{
    use HasFactory;
    protected $fillable = [
        'factory_id','max_upload_size','max_file_uploaded','max_event_saved'
    ];
    public function Factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }
}
