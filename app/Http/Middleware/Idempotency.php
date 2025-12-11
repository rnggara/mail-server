<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class Idempotency
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('Idempotency-Key');
        if (!$key || $request->method() !== 'POST') {
            return $next($request);
        }

        $existing = IdempotencyKey::query()->where('key', $key)->first();
        if ($existing && $existing->expires_at->isFuture()) {
            if ($existing->email_id) {
                return response()->json(['email_id' => $existing->email_id, 'idempotent' => true], 200);
            }
            return response()->json(['idempotent' => true], 200);
        }

        $response = $next($request);

        if ($response->status() === 202) {
            $body = $response->getData(true);
            $emailId = $body['email_id'] ?? null;
            IdempotencyKey::updateOrCreate(
                ['key' => $key],
                ['email_id' => $emailId, 'expires_at' => Carbon::now()->addHours(24)]
            );
        }

        return $response;
    }
}

