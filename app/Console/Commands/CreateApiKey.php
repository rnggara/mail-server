<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\Domain;
use App\Models\ApiKey;

class CreateApiKey extends Command
{
    protected $signature = 'api:key-create {domain?} {--name=} {--rate=60} {--from=} {--key=}';

    protected $description = 'Membuat API key baru, opsional terasosiasi dengan domain';

    public function handle(): int
    {
        $domainName = $this->argument('domain');
        $name = $this->option('name') ?: 'Default Key';
        $rate = (int)($this->option('rate') ?: 60);
        $from = $this->option('from');
        $providedKey = $this->option('key');

        $domain = null;
        if ($domainName) {
            $domain = Domain::firstOrCreate(
                ['name' => $domainName],
                ['default_from' => $from, 'bounce_webhook_enabled' => false]
            );
        }

        $plain = $providedKey ?: Str::random(40);
        $hash = Hash::make($plain);

        $key = ApiKey::create([
            'domain_id' => $domain ? $domain->id : null,
            'name' => $name,
            'key_hash' => $hash,
            'active' => true,
            'rate_limit_per_minute' => $rate,
        ]);

        $this->line('API Key berhasil dibuat');
        if ($domain) {
            $this->line('Domain: '.$domain->name);
        }
        $this->line('Nama: '.$name);
        $this->line('Rate limit/menit: '.$rate);
        $this->line('Plaintext API Key (simpan rahasia ini):');
        $this->info($plain);

        return self::SUCCESS;
    }
}
