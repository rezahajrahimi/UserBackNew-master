<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EsellsSettings extends Model
{
    protected $fillable = ['show_cubes', 'show_clusters', 'factory_id', 'isPublic', 'whatsapp_number'];
    public function Factory()
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }
}
