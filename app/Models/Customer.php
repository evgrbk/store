<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laratrust\Traits\LaratrustUserTrait;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Customer extends Authenticatable implements MustVerifyEmail
{
    use LaratrustUserTrait;
    use HasApiTokens;

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone',
        'dob',
        'is_male',
        'is_active',
        'addresses',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_male' => 'boolean',
        'is_active' => 'boolean',
        'addresses' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'email_verified_at',
        'dob',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
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

    /**
     * Customer active property
     *
     * @return void
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Favorite relation
     *
     * @return HasMany
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(CustomerFavorite::class);
    }

}
