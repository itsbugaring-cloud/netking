---
inclusion: manual
---

# Deployment Guide — Netking

## Server Info

- **Proxmox VM ID**: 103
- **Hostname**: netking-web
- **IP**: 10.10.10.103
- **OS**: Ubuntu 22.04 LTS
- **Panel**: CyberPanel
- **SSH User**: netking
- **SSH Command**: `ssh netking@10.10.10.103`

## Project Path

- **Git repo**: `/home/netking.id/release-clean`
- **Public HTML (symlink)**: `/home/netking.id/public_html`
- **Artisan**: `/home/netking.id/release-clean/artisan`

## Deployment Steps

```bash
# 1. SSH ke server
ssh netking@10.10.10.103

# 2. Masuk ke project directory
cd /home/netking.id/release-clean

# 3. Pull latest changes (pakai sudo karena ownership issue)
sudo git fetch origin
sudo git merge origin/main

# 4. Clear Laravel caches
sudo php artisan view:clear
sudo php artisan config:clear
sudo php artisan cache:clear
```

## Important Notes

- Repo owned by user lain (bukan `netking`), jadi **semua git command harus pakai `sudo`**
- Safe directory sudah di-set: `sudo git config --global --add safe.directory /home/netking.id/release-clean`
- Untuk Blade template changes, cukup `php artisan view:clear` — tidak perlu restart service
- Untuk config changes, tambah `php artisan config:clear`
- Untuk migration, tambah `sudo php artisan migrate --force`

## Git Workflow

1. Push ke branch baru dari local (jangan langsung ke main)
2. Di server: `sudo git fetch origin` lalu `sudo git merge origin/<branch-name>`
3. Atau merge ke main dulu via GitHub PR, lalu di server: `sudo git pull origin main`

## Troubleshooting

- **"dubious ownership"** → `sudo git config --global --add safe.directory /home/netking.id/release-clean`
- **"Permission denied" di .git/FETCH_HEAD** → Pakai `sudo` untuk semua git commands
- **"Could not open input file: artisan"** → Pastikan sudah `cd /home/netking.id/release-clean` (bukan `public_html`)
