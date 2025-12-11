<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('default_from')->nullable();
            $table->boolean('bounce_webhook_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('key_hash');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('rate_limit_per_minute')->default(60);
            $table->timestamps();
            $table->index(['domain_id', 'active']);
        });

        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->string('from')->nullable();
            $table->json('to');
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject');
            $table->longText('html')->nullable();
            $table->longText('text')->nullable();
            $table->string('reply_to')->nullable();
            $table->string('template_code')->nullable();
            $table->json('variables')->nullable();
            $table->string('status')->default('queued');
            $table->string('provider_message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('retries')->default(0);
            $table->string('idempotency_key')->nullable()->index();
            $table->timestamps();
            $table->index(['status', 'requested_at']);
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('emails')->cascadeOnDelete();
            $table->string('filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('storage_path');
            $table->timestamps();
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('subject');
            $table->longText('html')->nullable();
            $table->longText('text')->nullable();
            $table->json('variables_schema')->nullable();
            $table->timestamps();
        });

        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->foreignId('email_id')->nullable()->constrained('emails')->cascadeOnDelete();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('emails');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('domains');
    }
};

