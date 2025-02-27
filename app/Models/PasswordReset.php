<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    protected $table = 'password_resets';

    protected $fillable = [
        'email_hash',
        'token',
        'created_at',
    ];

    public $timestamps = false; // No `updated_at` or `created_at` columns managed automatically
}
