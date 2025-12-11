<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next)
    {
        $provided = $request->header('X-Api-Key');
        if (!$provided) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $default = config('services.api.default_key');
        $valid = false;

        if ($default && hash_equals($default, $provided)) {
            $valid = true;
        } else {
            $valid = ApiKey::query()->where('active', true)->get()->first(function ($key) use ($provided) {
                return Hash::check($provided, $key->key_hash);
            }) !== null;
        }

        if (!$valid) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}

