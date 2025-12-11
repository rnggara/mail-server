<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_email_returns_202_and_id()
    {
        Mail::fake();

        $payload = [
            'domain' => 'example.com',
            'from' => 'noreply@example.com',
            'to' => ['user@client.com'],
            'subject' => 'Selamat datang',
            'html' => '<p>Halo</p>',
            'text' => 'Halo',
        ];

        $res = $this->withHeaders([
            'X-Api-Key' => config('services.api.default_key'),
        ])->postJson('/api/emails', $payload);

        $res->assertStatus(202);
        $res->assertJsonStructure(['email_id']);
    }
}

