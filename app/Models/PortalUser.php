<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'portal_users';

    protected $fillable = [
        'unidad_id',
        'tipo',
        'nombre',
        'email',
        'password',
        'must_change_password',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
        ];
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class);
    }
}
