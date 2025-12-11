<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Services\EmailService;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function send(Request $request, EmailService $service)
    {
        $validated = $request->validate([
            'domain' => ['nullable','string'],
            'from_name' => ['nullable','string','max:255'],
            'to' => ['required','array','min:1'],
            'to.*' => ['email'],
            'cc' => ['array'],
            'cc.*' => ['email'],
            'bcc' => ['array'],
            'bcc.*' => ['email'],
            'subject' => ['required','string','max:255'],
            'html' => ['nullable','string'],
            'text' => ['nullable','string'],
            'reply_to' => ['nullable','email'],
            'template_code' => ['nullable','string'],
            'variables' => ['array'],
            'attachments' => ['array'],
            'attachments.*.filename' => ['required','string'],
            'attachments.*.mime' => ['nullable','string'],
            'attachments.*.content_base64' => ['required','string'],
            'idempotency_key' => ['nullable','string'],
        ]);

        $email = $service->createAndQueue($validated);
        return response()->json(['email_id' => $email->id], 202);
    }

    public function show(int $id)
    {
        $email = Email::findOrFail($id);
        return response()->json($email);
    }

    public function index(Request $request)
    {
        $query = Email::query();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('domain')) $query->whereHas('domain', fn($q) => $q->where('name', $request->domain));
        return response()->json($query->orderByDesc('id')->paginate(20));
    }
}
