<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    // Define the table name if not following Laravel's naming convention
    protected $table = 'events';

    // Fillable fields
    protected $fillable = ['name', 'date', 'description','image', 'thumbnail'];

 
}
