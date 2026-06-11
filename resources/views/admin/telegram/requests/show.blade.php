@extends('layouts.app')
@section('title', 'Detail Telegram Request')

@section('styles')
<style>
  .tg-show-page .tg-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(320px, 1fr);
    gap: 1rem;
  }
  .tg-show-page .tg-card {
    border: 1px solid var(--border);
    border-radius: 16px;
    background: var(--surface);
    overflow: hidden;
  }
  .tg-show-page .tg-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: 1rem 1.1rem;
    border-bottom: 1px solid var(--border);
  }
  .tg-show-page .tg-card-title {
    margin: 0;
    font-size: .95rem;
    font-weight: 650;
    color: var(--txt);
  }
  .tg-show-page .tg-card-body {
    padding: 1rem 1.1rem;
  }
  .tg-show-page .tg-summary {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .85rem;
  }
  .tg-show-page .tg-summary-item {
    padding: .85rem .9rem;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--surface-2);
  }
  .tg-show-page .tg-summary-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--txt-3);
    font-weight: 700;
    margin-bottom: .35rem;
  }
  .tg-show-page .tg-summary-value {
    color: var(--txt);
    font-weight: 600;
    overflow-wrap: anywhere;
  }
  .tg-show-page .tg-data-table th,
  .tg-show-page .tg-data-table td {
    padding: .75rem 0;
    border-bottom: 1px solid var(--border);
    vertical-align: top;
  }
  .tg-show-page .tg-data-table th {
    width: 32%;
    font-size: .78rem;
    color: var(--txt-3);
    font-weight: 600;
  }
  .tg-show-page .tg-data-table td {
    color: var(--txt);
    font-size: .84rem;
  }
  .tg-show-page .tg-data-table tr:last-child th,
  .tg-show-page .tg-data-table tr:last-child td {
    border-bottom: 0;
  }
  .tg-show-page .tg-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .65rem;
  }
  .tg-show-page .tg-actions form {
    margin: 0;
  }
  .tg-show-page .tg-history {
    display: flex;
    flex-direction: column;
    gap: .75rem;
  }
  .tg-show-page .tg-history-item {
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--surface-2);
    padding: .85rem .95rem;
  }
  .tg-show-page .tg-history-title {
    font-weight: 700;
    color: var(--txt);
    margin-bottom: .2rem;
  }
  .tg-show-page .tg-history-note {
    color: var(--txt-2);
    font-size: .83rem;
    margin-bottom: .35rem;
  }
  .tg-show-page .tg-history-meta {
    color: var(--txt-3);
    font-size: .75rem;
  }
  .tg-show-page .tg-status {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .25rem .6rem;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: var(--surface);
    font-size: .78rem;
    font-weight: 600;
    text-transform: lowercase;
  }
  .tg-show-page .tg-status::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #94a3b8;
  }
  .tg-show-page .tg-status.st-online::before { background:#16a34a; }
  .tg-show-page .tg-status.st-diterima::before { background:#2563eb; }
  .tg-show-page .tg-status.st-menunggu_push_olt::before { background:#f59e0b; }
  .tg-show-page .tg-status.st-menunggu_pppoe_up::before { background:#f97316; }
  .tg-show-page .tg-status.st-rejected::before { background:#ef4444; }
  .tg-show-page .tg-status.st-failed_mikrotik::before { background:#dc2626; }
  .tg-show-page code {
    background: color-mix(in srgb, var(--blue) 8%, var(--surface));
    color: color-mix(in srgb, var(--blue) 80%, var(--txt));
    border: 1px solid color-mix(in srgb, var(--blue) 18%, var(--border));
    padding: 2px 7px;
    border-radius: 6px;
    font-size: .78rem;
    font-weight: 600;
  }
  @media (max-width: 992px) {
    .tg-show-page .tg-grid,
    .tg-show-page .tg-summary {
      grid-template-columns: 1fr;
    }
  }
</style>
@endsection

@section('content')
<div class="ms-page tg-show-page">
  <div class="ms-page-head">
    <div>
      <div class="ms-page-kicker"><i class='bx bxl-telegram'></i> Automasi Bot</div>
      <h1 class="ms-page-title">Detail Request</h1>
      <div class="mt-1 d-flex align-items-center gap-2">
        <code>{{ $ref }}</code>
        <span class="tg-status st-{{ data_get($payload, 'status', '-') }}">{{ data_get($payload, 'status', '-') }}</span>
      </div>
    </div>
    <div class="ms-page-actions">
      <a href="{{ route('admin.telegram.requests.index') }}" class="ms-btn-secondary"><i class='bx bx-arrow-back'></i> Kembali</a>
    </div>
  </div>

  <div class="tg-grid">
    <div class="d-flex flex-column gap-3">
      <div class="tg-card">
        <div class="tg-card-head">
          <h2 class="tg-card-title">Ringkasan Request</h2>
        </div>
        <div class="tg-card-body">
          <div class="tg-summary">
            <div class="tg-summary-item">
              <div class="tg-summary-label">Area</div>
              <div class="tg-summary-value">{{ data_get($payload, 'draft.area_name', '-') }}</div>
            </div>
            <div class="tg-summary-item">
              <div class="tg-summary-label">Pelanggan</div>
              <div class="tg-summary-value">{{ data_get($payload, 'draft.nama', '-') }}</div>
            </div>
            <div class="tg-summary-item">
              <div class="tg-summary-label">PPPoE User</div>
              <div class="tg-summary-value"><code>{{ data_get($payload, 'draft.pppoe_user', '-') }}</code></div>
            </div>
            <div class="tg-summary-item">
              <div class="tg-summary-label">Paket</div>
              <div class="tg-summary-value">{{ data_get($payload, 'draft.paket_kode', '-') }} ({{ data_get($payload, 'draft.mikrotik_profile', '-') }})</div>
            </div>
            <div class="tg-summary-item" style="grid-column: 1 / -1;">
              <div class="tg-summary-label">Alamat</div>
              <div class="tg-summary-value">{{ data_get($payload, 'draft.address', data_get($payload, 'draft.lokasi', '-')) }}</div>
            </div>
            <div class="tg-summary-item" style="grid-column: 1 / -1;">
              <div class="tg-summary-label">Koordinat</div>
              <div class="tg-summary-value">
                {{ data_get($payload, 'draft.coordinates', '-') }}
                @if(data_get($payload, 'draft.latitude') !== null && data_get($payload, 'draft.longitude') !== null)
                  <a href="https://www.google.com/maps?q={{ data_get($payload, 'draft.latitude') }},{{ data_get($payload, 'draft.longitude') }}" target="_blank" rel="noopener" style="margin-left:8px;font-weight:600;">Buka Maps</a>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="tg-card">
        <div class="tg-card-head">
          <h2 class="tg-card-title">Data Draft</h2>
        </div>
        <div class="tg-card-body">
          <table class="table tg-data-table mb-0">
            <tbody>
              <tr><th style="width:32%;">Area</th><td>{{ data_get($payload, 'draft.area_name', '-') }}</td></tr>
              <tr><th>Nama</th><td>{{ data_get($payload, 'draft.nama', '-') }}</td></tr>
              <tr><th>No HP</th><td>{{ data_get($payload, 'draft.no_hp', '-') }}</td></tr>
              <tr><th>Alamat</th><td>{{ data_get($payload, 'draft.address', data_get($payload, 'draft.lokasi', '-')) }}</td></tr>
              <tr><th>Koordinat</th><td>{{ data_get($payload, 'draft.coordinates', '-') }}</td></tr>
              <tr><th>SN ONT</th><td>{{ data_get($payload, 'draft.sn_ont', '-') }}</td></tr>
              <tr><th>PPPoE User</th><td><code>{{ data_get($payload, 'draft.pppoe_user', '-') }}</code></td></tr>
              <tr><th>Paket</th><td>{{ data_get($payload, 'draft.paket_kode', '-') }} ({{ data_get($payload, 'draft.mikrotik_profile', '-') }})</td></tr>
              <tr><th>Harga</th><td>Rp {{ number_format((float) data_get($payload, 'draft.harga', 0), 0, ',', '.') }}</td></tr>
              <tr><th>Tanggal Pasang</th><td>{{ data_get($payload, 'draft.tanggal_pasang', '-') }}</td></tr>
              <tr><th>Foto SN</th><td><code>{{ data_get($payload, 'draft.photo_file_id', '-') }}</code></td></tr>
              <tr><th>Customer ID</th><td>{{ data_get($payload, 'customer_id', '-') }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="tg-card">
        <div class="tg-card-head">
          <h2 class="tg-card-title">Aksi Website</h2>
        </div>
        <div class="tg-card-body">
          <div class="tg-actions">
          <form method="POST" action="{{ route('admin.telegram.requests.approve', $ref) }}">@csrf <button class="ms-btn" type="submit">Approve</button></form>
          <form method="POST" action="{{ route('admin.telegram.requests.create-customer', $ref) }}">@csrf <button class="ms-btn-secondary" type="submit">Buat Customer + Link ONT</button></form>
          <form method="POST" action="{{ route('admin.telegram.requests.push-mikrotik', $ref) }}">@csrf <button class="ms-btn-secondary" type="submit">Push MikroTik</button></form>
          <form method="POST" action="{{ route('admin.telegram.requests.mark-online', $ref) }}">@csrf <button class="ms-btn-secondary" type="submit">Mark Online</button></form>
          <form method="POST" action="{{ route('admin.telegram.requests.reject', $ref) }}">@csrf <button class="ms-btn-ghost" type="submit">Reject</button></form>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex flex-column gap-3">
      <div class="tg-card">
        <div class="tg-card-head">
          <h2 class="tg-card-title">Dibuat Oleh Bot</h2>
        </div>
        <div class="tg-card-body">
          <div><strong>Nama:</strong> {{ data_get($payload, 'from.name', '-') }}</div>
          <div><strong>Username:</strong> {{ '@' . (data_get($payload, 'from.username', '-') ?: '-') }}</div>
          <div><strong>Telegram ID:</strong> {{ data_get($payload, 'from.id', '-') }}</div>
          <div><strong>Chat ID:</strong> {{ data_get($payload, 'chat_id', '-') }}</div>
          <div><strong>Waktu Submit:</strong> {{ data_get($payload, 'submitted_at', '-') }}</div>
        </div>
      </div>

      <div class="tg-card">
        <div class="tg-card-head">
          <h2 class="tg-card-title">Foto SN</h2>
        </div>
        <div class="tg-card-body">
          @if($hasSnPhoto)
            <a href="{{ route('admin.telegram.requests.photo', $ref) }}" target="_blank" rel="noopener">
              <img src="{{ route('admin.telegram.requests.photo', $ref) }}" alt="Foto SN" style="width:100%; border-radius:14px; border:1px solid var(--border); background:var(--surface-2);">
            </a>
            <div class="mt-2" style="font-size:.78rem; color:var(--txt-3);">
              Foto SN ini tersimpan di request bot dan bisa dibuka lagi dari halaman ini.
            </div>
          @else
            <div style="font-size:.84rem; color:var(--txt-2);">
              Preview foto belum tersedia dari website, tapi file ID-nya tetap tersimpan di request bot.
            </div>
            <div class="mt-2"><code>{{ data_get($payload, 'draft.photo_file_id', '-') }}</code></div>
          @endif
        </div>
      </div>

      @if(data_get($payload, 'draft.latitude') !== null && data_get($payload, 'draft.longitude') !== null)
      <div class="tg-card">
        <div class="tg-card-head">
          <h2 class="tg-card-title">Maps Lokasi</h2>
        </div>
        <div class="tg-card-body">
          <iframe
            src="https://www.google.com/maps?q={{ data_get($payload, 'draft.latitude') }},{{ data_get($payload, 'draft.longitude') }}&z=18&output=embed"
            style="width:100%;height:260px;border:1px solid var(--border);border-radius:14px;background:var(--surface-2);"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
      @endif

      <div class="tg-card">
        <div class="tg-card-head">
          <h2 class="tg-card-title">History Konfig</h2>
        </div>
        <div class="tg-card-body">
          <div class="tg-history">
          @forelse((array) data_get($payload, 'history', []) as $h)
            <div class="tg-history-item">
              <div class="tg-history-title">{{ $h['status'] ?? '-' }}</div>
              <div class="tg-history-note">{{ $h['note'] ?? '-' }}</div>
              <div class="tg-history-meta">{{ $h['at'] ?? '-' }} • {{ $h['by'] ?? '-' }}</div>
            </div>
          @empty
            <div class="text-muted">Belum ada history.</div>
          @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
