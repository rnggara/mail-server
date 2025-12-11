<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Domain;
use App\Models\Email;
use Illuminate\Support\Facades\Storage;

class EmailService
{
    public function createAndQueue(array $data): Email
    {
        $domain = null;
        if (!empty($data['domain'])) {
            $domain = Domain::firstOrCreate(['name' => $data['domain']]);
        }

        $email = Email::create([
            'domain_id' => $domain ? $domain->id : null,
            'from' => $domain ? $domain->default_from : config('mail.from.address'),
            'from_name' => $data['from_name'] ?? null,
            'to' => $data['to'],
            'cc' => $data['cc'] ?? [],
            'bcc' => $data['bcc'] ?? [],
            'subject' => $data['subject'],
            'html' => $data['html'] ?? null,
            'text' => $data['text'] ?? null,
            'reply_to' => $data['reply_to'] ?? null,
            'template_code' => $data['template_code'] ?? null,
            'variables' => $data['variables'] ?? [],
            'status' => 'queued',
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ]);

        foreach (($data['attachments'] ?? []) as $att) {
            $content = base64_decode($att['content_base64'] ?? '', true);
            $filename = $att['filename'];
            $path = 'mail_attachments/'.$email->id.'/'.$filename;
            if ($content !== false) {
                Storage::disk('local')->put($path, $content);
            }
            Attachment::create([
                'email_id' => $email->id,
                'filename' => $filename,
                'mime_type' => $att['mime'] ?? 'application/octet-stream',
                'size' => $content !== false ? strlen($content) : 0,
                'storage_path' => $path,
            ]);
        }

        dispatch(new \App\Jobs\SendEmailJob($email->id));

        return $email;
    }
}
