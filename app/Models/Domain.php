<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'default_from', 'bounce_webhook_enabled'];

    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }
}

