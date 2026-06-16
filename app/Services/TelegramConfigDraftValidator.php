<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Package;

class TelegramConfigDraftValidator
{
    public function validateDraftAgainstTemplate(array $draft): array
    {
        $errors = [];

        $fieldChecks = [
            'nama' => (string) ($draft['nama'] ?? ''),
            'no_hp' => (string) ($draft['no_hp'] ?? ''),
            'address' => (string) ($draft['address'] ?? ''),
            'coordinates' => (string) ($draft['coordinates'] ?? ''),
            'sn_ont' => (string) ($draft['sn_ont'] ?? ''),
            'pppoe_user' => (string) ($draft['pppoe_user'] ?? ''),
            'tanggal_pasang' => (string) ($draft['tanggal_pasang'] ?? ''),
        ];

        foreach ($fieldChecks as $field => $value) {
            $err = $this->validateFieldValue($field, $value);
            if ($err !== null) {
                $errors[] = $err;
            }
        }

        $areaId = (int) ($draft['area_id'] ?? 0);
        if ($areaId <= 0 || !Area::query()->whereKey($areaId)->exists()) {
            $errors[] = 'Area belum valid.';
        }

        $packageId = (int) ($draft['paket_id'] ?? 0);
        if ($packageId <= 0) {
            $errors[] = 'Paket belum dipilih.';
        } elseif ($areaId > 0) {
            $pkgOk = Package::query()
                ->whereKey($packageId)
                ->where('area_id', $areaId)
                ->where('is_active', true)
                ->exists();
            if (!$pkgOk) {
                $errors[] = 'Paket tidak cocok dengan area.';
            }
        }

        if (empty($draft['mikrotik_profile'])) {
            $errors[] = 'Profile MikroTik belum terisi.';
        }

        if (!isset($draft['harga']) || (int) $draft['harga'] <= 0) {
            $errors[] = 'Harga paket belum valid.';
        }

        return $errors;
    }

    public function validateFieldValue(string $field, string $value): ?string
    {
        if ($value === '') {
            return 'Input tidak boleh kosong.';
        }

        return match ($field) {
            'nama' => mb_strlen($value) >= 3 ? null : 'Nama terlalu pendek.',
            'no_hp' => strlen($value) >= 10 && strlen($value) <= 15
                ? null
                : 'Ngisi No hp Tong Asal...',
            'address' => mb_strlen($value) >= 5 ? null : 'Alamat terlalu singkat.',
            'coordinates' => preg_match('/^-?\d{1,3}(?:\.\d+)?\s*,\s*-?\d{1,3}(?:\.\d+)?$/', $value) ? null : 'Koordinat wajib format lat,lng.',
            'sn_ont' => preg_match('/^[A-Z0-9]{8,20}$/', $value) ? null : 'Ngisi SN ONT Nu Bener... (8-20, huruf/angka).',
            'pppoe_user' => preg_match('/^[A-Z0-9._-]{3,32}$/', $value) ? null : 'Ngisi Secretna PPPoE na teu valid.... (3-32).',
            'tanggal_pasang' => $this->isValidDate($value)
                ? null
                : 'Format tanggal pakai DD/MM/YYYY ya.',
            default => null,
        };
    }

    public function isValidDate(string $value): bool
    {
        return $this->normalizeDateValue($value) !== null;
    }

    public function normalizeDateValue(string $value): ?string
    {
        $raw = trim($value);
        if ($raw === '') {
            return null;
        }

        $ymd = \DateTime::createFromFormat('Y-m-d', $raw);
        if ($ymd instanceof \DateTime && $ymd->format('Y-m-d') === $raw) {
            return $raw;
        }

        $dmy = \DateTime::createFromFormat('d/m/Y', $raw);
        if ($dmy instanceof \DateTime && $dmy->format('d/m/Y') === $raw) {
            return $dmy->format('Y-m-d');
        }

        return null;
    }

    public function formatDateDisplay(string $value): string
    {
        $normalized = $this->normalizeDateValue($value);
        if ($normalized === null) {
            return $value !== '' ? $value : '-';
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $normalized);
        if (!$dt instanceof \DateTime) {
            return $normalized;
        }

        return $dt->format('d/m/Y');
    }
}
