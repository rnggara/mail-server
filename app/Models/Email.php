<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_id','from','from_name','to','cc','bcc','subject','html','text','reply_to','template_code','variables','status','provider_message_id','error','requested_at','sent_at','retries','idempotency_key'
    ];

    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'variables' => 'array',
        'requested_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
