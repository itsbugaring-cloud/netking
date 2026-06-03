# Telegram Bot Sandbox (Temporary Testing)

Folder ini **terpisah dari flow produksi Laravel**.  
Tujuannya untuk testing format partner + foto SN ONT tanpa menyentuh database produksi.

## Isi Folder

- `webhook.php`  
  Endpoint webhook Telegram (standalone PHP).
- `set-webhook.php`  
  Helper untuk set webhook ke Telegram.
- `config.sample.php`  
  Template konfigurasi lokal.
- `storage/`  
  Menyimpan request testing (JSON) untuk audit sementara.

## Cara Pakai Cepat

1. Copy config:

```bash
cp config.sample.php config.local.php
```

2. Isi `config.local.php`:
- `bot_token`
- `admin_chat_id`
- `webhook_secret` (bebas, untuk keamanan basic)

3. Arahkan web server ke file `webhook.php` ini, contoh URL:

`https://netking.id/telegram-sandbox/webhook.php?k=<webhook_secret>`

4. Set webhook:

```bash
php set-webhook.php "https://netking.id/telegram-sandbox/webhook.php?k=<webhook_secret>"
```

## Catatan Aman

- Mode ini **tidak push ke MikroTik**.
- Mode ini **tidak push ke OLT**.
- Mode ini hanya parse format + simpan payload testing + kirim notifikasi ringkas.

