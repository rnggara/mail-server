<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionalMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public ?string $htmlBody;
    public ?string $textBody;

    public function __construct(string $subjectLine, ?string $htmlBody, ?string $textBody)
    {
        $this->subjectLine = $subjectLine;
        $this->htmlBody = $htmlBody;
        $this->textBody = $textBody;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectLine);
        if ($this->htmlBody) {
            $mail->html($this->htmlBody);
        }
        if ($this->textBody) {
            $mail->text('mail.plain', ['text' => $this->textBody]);
        }
        return $mail;
    }
}

