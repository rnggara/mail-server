## Ringkasan
- Membangun layanan Laravel yang menyediakan API untuk mengirim email dari domain Anda, dengan autentikasi API Key, antrean (queue), pencatatan status, dan integrasi SMTP/penyedia email.

## Arsitektur Teknis
- Framework: Laravel 11, PHP ≥ 8.2, Composer.
- Mailer: `smtp` (default) atau integrasi penyedia (Mailgun/SendGrid/SES) via driver.
- Queue: `database` queue untuk portabilitas di Windows; dapat naik kelas ke Redis nanti.
- Database: MySQL/PostgreSQL untuk menyimpan email, lampiran, template, domain, API key, idempotency.
- Storage: `storage/app` untuk lampiran; dapat diganti ke S3/MinIO bila perlu.
- Config: `.env` untuk `MAIL_*`, kredensial DB, rate limit, dan konfigurasi domain.
- Multi-domain: tabel `domains` dan pengaturan default `from` per domain.

## Endpoint API
- POST `/api/emails`: kirim email (mengantrikan job, respons 202 berisi `email_id`).
- GET `/api/emails/{id}`: ambil status, metadata, dan error bila gagal.
- GET `/api/emails`: daftar + filter (status, tanggal, penerima, domain).
- POST `/api/templates`: buat/update template email (opsional, dengan variabel).
- POST `/api/webhooks/{provider}`: terima webhook bounce/spam/delivery dari penyedia.

## Keamanan
- Autentikasi: API Key per klien/domain, disimpan hashed; middleware verifikasi header `X-Api-Key`.
- Rate limit: throttling per API Key (mis. 60/min) dan per IP.
- Validasi: format email, ukuran lampiran, MIME whitelist, subject/body wajib.
- Idempotensi: header `Idempotency-Key` untuk mencegah duplikasi.
- Observabilitas: structured logging, correlation id, audit trail.
- Secrets: hanya di `.env`; tanpa logging kredensial.

## Skema Data
- `emails`: id, domain_id, from, to/cc/bcc (json), subject, html/text, reply_to, status, provider_message_id, error, requested_at, sent_at, retries, idempotency_key.
- `attachments`: id, email_id, filename, mime_type, size, storage_path.
- `templates`: id, code, name, subject, html, text, variables_schema.
- `domains`: id, name, default_from, bounce_webhook_enabled.
- `api_keys`: id, domain_id, key_hash, name, active, rate_limit.
- `idempotency_keys`: key, email_id, expires_at.

## Alur Pengiriman
- Klien memanggil POST `/api/emails` dengan API Key.
- Validasi input, cek idempotensi, simpan `emails` + `attachments`.
- Enqueue `SendEmailJob` (queue `emails`).
- Job merender template/variabel, mengirim via `Mail::send` SMTP/driver.
- Update status (sent/failed) dan isi `provider_message_id`.

## Konfigurasi Domain & Deliverability
- SPF: TXT di domain mengizinkan host/penyedia pengiriman.
- DKIM: aktivasi penandatanganan di penyedia; tambahkan TXT selector.
- DMARC: kebijakan `p=none/quarantine/reject` sesuai tahap.
- `from` dan `reply-to`: konsisten per domain; hindari spoofing.

## Penanganan Bounce/Webhook
- Endpoint webhook memetakan event `bounced`, `complained`, `delivered` ke status.
- Verifikasi signature webhook (Mailgun/SendGrid/SES) sebelum menerima.
- Simpan metadata event untuk audit dan metrik.

## Pengujian
- Feature test: POST `/api/emails` (valid/invalid, idempotensi, rate limit).
- Unit test: EmailService, renderer template.
- Mail/Queue fakes: `Mail::fake()`, `Queue::fake()` untuk skenario.
- Webhook test: signature verification dan update status.

## Langkah Implementasi
1. Inisialisasi proyek Laravel dan konfigurasi `.env` (`APP_KEY`, DB, `MAIL_*`).
2. Buat migrasi dan model: `emails`, `attachments`, `templates`, `domains`, `api_keys`, `idempotency_keys`.
3. Middleware: `ApiKeyAuth`, `Idempotency`, rate limit per key.
4. Service: `EmailService` (validasi, simpan, render, enqueue) + `TemplateRenderer`.
5. Job: `SendEmailJob` menggunakan `Mailable` (`TransactionalMailable`).
6. Controller + rute: `EmailController` (send/status/list), `TemplateController`, `WebhookController`.
7. Logging & audit: channel khusus `mail`, simpan event.
8. Dokumentasi API: OpenAPI/Swagger minimal untuk payload/response.
9. Test: unit + feature; jalankan di CI lokal.

## Contoh Payload POST `/api/emails`
```json
{
  "domain": "example.com",
  "from": "noreply@example.com",
  "to": ["user@client.com"],
  "subject": "Selamat datang",
  "html": "<p>Halo {{name}}</p>",
  "text": "Halo {{name}}",
  "template_code": "welcome",
  "variables": {"name": "Andi"},
  "attachments": [
    {"filename": "brosur.pdf", "mime": "application/pdf", "content_base64": "..."}
  ],
  "idempotency_key": "c4f1b2..."
}
```

## Prasyarat
- PHP ≥ 8.2, Composer, ekstensi `openssl`, `mbstring`, `pdo`.
- Database MySQL/PostgreSQL tersedia.
- Kredensial SMTP atau akun penyedia (Mailgun/SendGrid/SES).

## Pilihan Integrasi
- SMTP native: cepat mulai, butuh pengaturan SPF/DKIM di domain.
- Mailgun/SendGrid/SES: deliverability lebih baik, webhook siap, biaya langganan.

## Risiko & Mitigasi
- Deliverability rendah: aktifkan SPF/DKIM/DMARC, gunakan penyedia bereputasi.
- Lonjakan trafik: gunakan queue + backoff/retry, rate limit per key.
- Lampiran besar: batasi ukuran total, pindahkan ke storage dengan link unduhan.

## Output yang Diharapkan
- Repository Laravel dengan endpoint kirim email, autentikasi API Key, queue, pencatatan status, webhook dasar, dan dokumentasi payload.

## Konfirmasi
- Jika setuju, saya akan memulai dengan: SMTP default, queue `database`, MySQL, lampiran via base64, dan endpoint yang tercantum. Beri tahu bila Anda ingin memilih penyedia tertentu atau ada preferensi domain/nama pengirim.
