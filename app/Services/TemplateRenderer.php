<?php

namespace App\Services;

use App\Models\Template;

class TemplateRenderer
{
    public function render(?string $templateCode, array $variables, ?string $html, ?string $text, string $subject): array
    {
        if ($templateCode) {
            $template = Template::where('code', $templateCode)->first();
            if ($template) {
                $subject = $template->subject;
                $html = $template->html;
                $text = $template->text;
            }
        }

        $render = function (?string $content) use ($variables) {
            if ($content === null) return null;
            $out = $content;
            foreach ($variables as $k => $v) {
                $out = str_replace('{{'.$k.'}}', (string)$v, $out);
            }
            return $out;
        };

        return [
            'subject' => $render($subject) ?? $subject,
            'html' => $render($html),
            'text' => $render($text),
        ];
    }
}

