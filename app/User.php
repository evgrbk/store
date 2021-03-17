<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laratrust\Traits\LaratrustUserTrait;

class User extends Authenticatable implements MustVerifyEmail
{
    use LaratrustUserTrait;
    use HasApiTokens;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone', 'date_of_birth', 'gender', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
    ];

    protected $appends = [
        'user_roles', 'role', 'permissions'
    ];

    /**
     * Set password
     *
     * @return void
     * @var string $value
     */
    public function setPasswordAttribute(string $value): void
    {
        if (!is_null($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function getRoleAttribute()
    {
        return optional($this->roles())->first();
    }

    public function getPermissionsAttribute()
    {
        return $this->allPermissions();
    }

//    public function roles()
//    {
//        return $this->roles();
//    }

    public function getUserRolesAttribute()
    {
//        return $this->attachRole();
    }
}
