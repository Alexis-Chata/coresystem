<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class F_tipo_documento extends Model
{
    use HasFactory;

    protected $fillable = ['tipo_documento', 'name'];
}
