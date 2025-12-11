<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['email_id','filename','mime_type','size','storage_path'];

    public function email()
    {
        return $this->belongsTo(Email::class);
    }
}

