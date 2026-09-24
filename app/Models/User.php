<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

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

    /**
     * Verifica si el rol del usuario tiene asignado un permiso (por nombre).
     * Fuente única de verdad para el control de acceso a módulos.
     */
    public function tienePermiso($nombre): bool
    {
        return $this->permisosActivos()->contains($nombre);
    }

    public function accesoActivo(): bool
    {
        return DB::table('users')->join('rol', 'rol.id', '=', 'users.id_rol')
            ->where('users.id', $this->id)->where('users.estado', 1)
            ->where('rol.estado', 1)->exists();
    }

    public function permisosActivos(): \Illuminate\Support\Collection
    {
        return DB::table('users')
            ->join('rol', 'rol.id', '=', 'users.id_rol')
            ->join('permiso_rol', 'permiso_rol.id_rol', '=', 'rol.id')
            ->join('permiso', 'permiso.id', '=', 'permiso_rol.id_permiso')
            ->where('users.id', $this->id)->where('users.estado', 1)
            ->where('rol.estado', 1)->where('permiso.estado', 1)
            ->distinct()->pluck('permiso.nombre');
    }

    /**
     * Nombre del rol del usuario (para mostrar en la interfaz).
     */
    public function getRoleNameAttribute(): ?string
    {
        return optional($this->rol)->nombre;
    }
}
