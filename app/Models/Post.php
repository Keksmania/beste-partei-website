<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'thumbnail',
    ];

    public function event()
    {
        return $this->hasOne(Event::class);
    }
}