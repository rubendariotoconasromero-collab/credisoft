<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal',
        'email',
        'estado',
        'password',
        'id_rol',
        'ci',
        'telefono',
        'fecha_cambio_password',
        'dias_vigencia'
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
        'password' => 'hashed', // Laravel 10+ hashea automáticamente si usas esto
        'fecha_cambio_password' => 'date',
    ];

    // Relación: Un usuario pertenece a un rol
    public function rol()
    {
        // El segundo parámetro es la llave foránea en esta tabla
        return $this->belongsTo(Rol::class, 'id_rol');
    }
}