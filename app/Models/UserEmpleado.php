<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEmpleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'empleado_id',
        'tipo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
