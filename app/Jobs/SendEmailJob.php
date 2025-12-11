<?php

namespace App\Jobs;

use App\Mail\TransactionalMailable;
use App\Models\Email;
use App\Services\TemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $emailId;

    public function __construct(int $emailId)
    {
        $this->emailId = $emailId;
    }

    public function handle(TemplateRenderer $renderer): void
    {
        $email = Email::findOrFail($this->emailId);

        $rendered = $renderer->render($email->template_code, $email->variables ?? [], $email->html, $email->text, $email->subject);

        $mailable = new TransactionalMailable($rendered['subject'], $rendered['html'], $rendered['text']);

        try {
            $to = $email->to ?? [];
            foreach ($to as $recipient) {
                Mail::to($recipient)->send($mailable);
            }
            $email->status = 'sent';
            $email->sent_at = now();
            $email->save();
            Log::channel('stack')->info('Email sent', ['email_id' => $email->id]);
        } catch (\Throwable $e) {
            $email->status = 'failed';
            $email->error = $e->getMessage();
            $email->save();
            Log::channel('stack')->error('Email send failed', ['email_id' => $email->id, 'error' => $e->getMessage()]);
        }
    }
}

