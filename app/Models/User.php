<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'firstname',
        'name',
        'email',
        'password',
        'email_hash',
        'verification_key',
        'email_verified_at',
        'activated'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship: Events the user is attending.
     */


         public function events()
        {
            return $this->belongsToMany(Event::class, 'event_user')
                        ->withTimestamps()
                        ->withPivot('attended_at');
        }
        public function permissions()
        {
            return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id');
        }
    
        /**
         * Check if the user has a specific permission.
         *
         * @param string $permissionName
         * @return bool
         */
        public function hasPermission($permissionName)
        {
            return DB::table('user_permissions_view')
                ->where('user_id', $this->id)
                ->where('permission_name', $permissionName)
                ->exists();
        }
    
}
