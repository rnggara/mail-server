<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = ['code','name','subject','html','text','variables_schema'];

    protected $casts = [
        'variables_schema' => 'array',
    ];
}

