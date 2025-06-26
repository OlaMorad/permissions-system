<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'phone',
        'avatar'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
        ];
    }

    public static  function  create_user($request)
    {
       // حفظ الصورة إذا تم رفعها
    $avatarPath = null;
    if (isset($request['avatar'])) {
        $avatarPath = $request['avatar']->store('avatars', 'public');
    }

    return self::create([
        'name'     => $request['name'],
        'email'    => $request['email'],
        'password' => Hash::make($request['password']),
        'address'  => $request['address'],
        'phone'    => $request['phone'],
        'avatar'   => $avatarPath,
    ]);
    }



      public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
