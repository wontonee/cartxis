<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Cartxis\Core\Traits\HasPermissions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'role',
        'is_active',
        'profile_photo_path',
        'phone',
        'date_of_birth',
        'gender',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factory_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(\Cartxis\Shop\Models\Order::class);
    }

    /**
     * Get the customer record for the user.
     */
    public function customer()
    {
        return $this->hasOne(\Cartxis\Customer\Models\Customer::class);
    }

    /**
     * Get the addresses for the user.
     */
    public function addresses()
    {
        return $this->hasMany(\Cartxis\Customer\Models\CustomerAddress::class, 'customer_id');
    }

    /**
     * Get the wishlist items for the user (through customer).
     */
    public function wishlist()
    {
        return $this->hasManyThrough(
            \Cartxis\Customer\Models\Wishlist::class,
            \Cartxis\Customer\Models\Customer::class,
            'user_id', // Foreign key on customers table
            'customer_id', // Foreign key on wishlists table
            'id', // Local key on users table
            'id' // Local key on customers table
        );
    }
}
