<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactoAlternativo extends Model
{
    protected $table = 'contactos_alternativos';

    protected $fillable = [
        'contactable_type',
        'contactable_id',
        'nombre',
        'telefono',
        'email',
    ];
}
