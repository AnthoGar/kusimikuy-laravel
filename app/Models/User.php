<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    public function comedor(): HasOne
    {
        return $this->hasOne(ComedorPopular::class);
    }
    public function lotesDonados(): HasMany
    {
        return $this->hasMany(LoteAlimento::class, 'donor_id');
    }
    public function traslados(): BelongsToMany
    {
        return $this->belongsToMany(Traslado::class, 'traslado_user')
            ->withTimestamps();
    }
    public function entregasRecibidas(): HasMany
    {
        return $this->hasMany(Entrega::class, 'received_by');
    }
    public function cancelacionesSolicitadas(): HasMany
    {
        return $this->hasMany(SolicitudCancelacion::class, 'requested_by');
    }
    public function cancelacionesRevisadas(): HasMany
    {
        return $this->hasMany(SolicitudCancelacion::class, 'reviewed_by');
    }
}
