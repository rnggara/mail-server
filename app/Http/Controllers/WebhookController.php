<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(string $provider, Request $request)
    {
        Log::info('Webhook received', ['provider' => $provider, 'payload' => $request->all()]);
        return response()->json(['ok' => true]);
    }
}

