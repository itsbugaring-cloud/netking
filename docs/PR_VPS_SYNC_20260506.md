# PR Notes: VPS Sync 2026-05-06

Branch: `codex/vps-sync-20260506`  
Base: `main`

## Tujuan

Menyelaraskan isi repository GitHub dengan kondisi kode terbaru di VPS produksi (`/var/www/netking.id`) secara **sanitized**.

## Scope yang masuk

1. Sinkronisasi kode backend Laravel dari VPS terbaru.
2. Penghapusan file legacy NMS ACS view yang sudah tidak dipakai.
3. Penghapusan file backup lama di `storage/backups/*` yang tidak dibutuhkan source repo.
4. Aset publik penting tetap dipertahankan (logo, QRIS, manifest, sandbox files) setelah perbaikan sync.

## Scope yang sengaja tidak masuk

1. `.env` / secret / token.
2. `vendor/`, `node_modules/`.
3. runtime logs/cache/session/view compiled.

## Validasi cepat

1. `https://netking.id/admin/login` -> `200`
2. `https://netking.id/hotspot/login` -> `200`
3. `https://netking.id/download/customer` -> `200`
4. Referensi route `admin.acs.show` pada view aktif NMS sudah dibersihkan.

## Risiko

1. Perubahan snapshot besar berpotensi membawa drift tak terduga bila ada edit manual di VPS yang belum terdokumentasi.
2. Deletion pada `storage/*` dan legacy view harus dipastikan memang tidak lagi diperlukan tim.

## Rekomendasi merge

1. Review diff folder ini terlebih dulu:
   - `resources/views/admin/nms/`
   - `storage/backups/`
   - `public/`
2. Merge saat traffic rendah.
3. Setelah merge, jalankan:

```bash
php artisan optimize:clear
```

4. Lakukan smoke test role:
   - admin: dashboard, pelanggan, tagihan, nms/devices
   - finance: pelanggan + tagihan + aksi pembayaran

