<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $casts = [
        'user_roles' => 'array',
        'user_permissions' => 'array',
    ];
}
