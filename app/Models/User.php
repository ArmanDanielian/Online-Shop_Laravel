<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    public const TYPE_BUYER= 0;
    public const TYPE_SELLER= 1;
    public const TYPE_ADMIN = 2;
    public const SLUG_BUYER = 'buyer';
    public const SLUG_SELLER = 'seller';
    public const SLUG_ADMIN = 'admin';
    public const TYPE_SLUGS = [
        self::TYPE_BUYER => self::SLUG_BUYER,
        self::TYPE_SELLER => self::SLUG_SELLER,
        self::TYPE_ADMIN => self::SLUG_ADMIN
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'type',
        'gender',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'first_name' => $this->first_name
        ];
    }

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }

    public function carts()
    {
        return $this->hasMany(CartItem::class);
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, Shop::class);
    }

    public function isSeller(): bool
    {
        return  $this->type === self::TYPE_SELLER;
    }

    public function isBuyer(): bool
    {
        return  $this->type === self::TYPE_BUYER;
    }

    public function isAdmin(): bool
    {
        return $this->type === self::TYPE_ADMIN;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
