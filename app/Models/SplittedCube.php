<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Hekmatinasser\Verta\Verta;

class SplittedCube extends Model
{
    use HasFactory;
    protected $fillable = ['factory_id', 'cube_id', 'weight', 'height', 'length', 'width', 'splitted_at', 'is_active'];
    public function factory()
    {
        return $this->belongsTo(Comment::class, 'factory_id');
    }
    public function cube()
    {
        return $this->belongsTo(Cube::class, 'cube_id');
    }
}
