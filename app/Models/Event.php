<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = ['post_id', 'date', 'key'];

    /**
     * Relationship: Users attending the event.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'event_user')
                    ->withTimestamps()
                    ->withPivot('attended_at');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
