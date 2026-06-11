@props(['dismissAfter' => 5000, 'iconSet' => 'tabler'])

@php
use Illuminate\Support\Str;

$types = [
    'success' => [
        'class' => 'success',
        'icon_tabler' => 'ti ti-circle-check',
        'icon_box' => 'bx bx-check-circle',
        'title' => 'Berhasil',
        'autoDismiss' => true,
    ],
    'error' => [
        'class' => 'danger',
        'icon_tabler' => 'ti ti-alert-circle',
        'icon_box' => 'bx bx-error-circle',
        'title' => 'Terjadi kendala',
        'autoDismiss' => false,
    ],
    'warning' => [
        'class' => 'warning',
        'icon_tabler' => 'ti ti-alert-triangle',
        'icon_box' => 'bx bx-error',
        'title' => 'Perlu perhatian',
        'autoDismiss' => false,
    ],
    'info' => [
        'class' => 'info',
        'icon_tabler' => 'ti ti-info-circle',
        'icon_box' => 'bx bx-info-circle',
        'title' => 'Info',
        'autoDismiss' => true,
    ],
    'status' => [
        'class' => 'success',
        'icon_tabler' => 'ti ti-circle-check',
        'icon_box' => 'bx bx-check-circle',
        'title' => 'Status',
        'autoDismiss' => true,
    ],
];

$banner = session('banner');
if (is_string($banner) && trim($banner) !== '') {
    $banner = [
        'type' => 'info',
        'title' => 'Pemberitahuan',
        'message' => $banner,
    ];
}

$flashItems = [];
foreach ($types as $type => $config) {
    $message = session($type);
    if (!$message || !is_string($message)) {
        continue;
    }

    $flashItems[] = [
        'tone' => $config['class'],
        'title' => $config['title'],
        'message' => $type === 'warning' ? Str::limit($message, 600) : $message,
        'icon' => $iconSet === 'boxicons' ? $config['icon_box'] : $config['icon_tabler'],
        'autoDismiss' => $config['autoDismiss'],
    ];
}
@endphp

@once
<style>
    .nk-message-stack {
        display: grid;
        gap: 14px;
        margin-bottom: 18px;
    }

    .nk-banner,
    .nk-flash-card,
    .nk-inline-alert,
    .alert:not(.nk-plain-alert) {
        position: relative;
        border: 1px solid #e5e7eb !important;
        border-radius: 18px !important;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%) !important;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
        color: #0f172a !important;
        overflow: hidden;
    }

    .nk-banner::before,
    .nk-flash-card::before,
    .nk-inline-alert::before,
    .alert:not(.nk-plain-alert)::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        border-radius: 18px 0 0 18px;
        background: var(--nk-tone, #3b82f6);
    }

    .nk-banner {
        padding: 16px 18px 16px 20px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .nk-banner-main {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        min-width: 0;
    }

    .nk-banner-icon,
    .nk-flash-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.15rem;
        color: var(--nk-tone, #3b82f6);
        background: color-mix(in srgb, var(--nk-tone, #3b82f6) 12%, white);
        border: 1px solid color-mix(in srgb, var(--nk-tone, #3b82f6) 18%, #dbeafe);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
    }

    .nk-banner-kicker,
    .nk-flash-kicker {
        font-size: .69rem;
        line-height: 1;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 7px;
    }

    .nk-banner-title,
    .nk-flash-title {
        font-size: .98rem;
        line-height: 1.3;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 4px;
    }

    .nk-banner-message,
    .nk-flash-message,
    .nk-inline-alert .alert-message,
    .alert:not(.nk-plain-alert) .alert-title + div,
    .alert:not(.nk-plain-alert) .text-muted,
    .alert:not(.nk-plain-alert) ul,
    .alert:not(.nk-plain-alert) li {
        font-size: .84rem !important;
        line-height: 1.52;
        color: #475569 !important;
        margin: 0;
    }

    .nk-banner-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        flex-shrink: 0;
    }

    .nk-banner-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 36px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid color-mix(in srgb, var(--nk-tone, #3b82f6) 18%, #dbeafe);
        background: color-mix(in srgb, var(--nk-tone, #3b82f6) 8%, white);
        color: color-mix(in srgb, var(--nk-tone, #3b82f6) 82%, #0f172a);
        text-decoration: none;
        font-size: .8rem;
        font-weight: 700;
        transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .nk-banner-link:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 18px rgba(37, 99, 235, 0.08);
        color: color-mix(in srgb, var(--nk-tone, #3b82f6) 90%, #0f172a);
    }

    .nk-banner-dismiss,
    .nk-flash-dismiss,
    .alert:not(.nk-plain-alert) .btn-close {
        width: 32px;
        height: 32px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: rgba(255,255,255,.84);
        color: #64748b;
        opacity: 1;
        box-shadow: none;
        flex-shrink: 0;
    }

    .nk-flash-grid {
        display: grid;
        gap: 12px;
    }

    .nk-flash-card {
        padding: 14px 16px 14px 18px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .nk-flash-body {
        flex: 1;
        min-width: 0;
    }

    .nk-flash-card[data-tone="success"] { --nk-tone: #22c55e; border-color: #ccefd8 !important; }
    .nk-flash-card[data-tone="danger"]  { --nk-tone: #ef4444; border-color: #fecaca !important; }
    .nk-flash-card[data-tone="warning"] { --nk-tone: #f59e0b; border-color: #fde68a !important; }
    .nk-flash-card[data-tone="info"]    { --nk-tone: #3b82f6; border-color: #bfdbfe !important; }

    .nk-banner[data-tone="success"] { --nk-tone: #22c55e; border-color: #ccefd8 !important; }
    .nk-banner[data-tone="danger"]  { --nk-tone: #ef4444; border-color: #fecaca !important; }
    .nk-banner[data-tone="warning"] { --nk-tone: #f59e0b; border-color: #fde68a !important; }
    .nk-banner[data-tone="info"]    { --nk-tone: #3b82f6; border-color: #bfdbfe !important; }

    .alert:not(.nk-plain-alert) {
        padding: 14px 16px 14px 18px !important;
    }

    .alert:not(.nk-plain-alert) .d-flex {
        gap: 12px;
        align-items: flex-start;
    }

    .alert:not(.nk-plain-alert) .alert-title {
        font-size: .78rem !important;
        line-height: 1;
        font-weight: 800 !important;
        letter-spacing: .1em;
        text-transform: uppercase;
        margin: 3px 0 7px !important;
        color: #0f172a !important;
    }

    .alert:not(.nk-plain-alert) .alert-icon,
    .alert:not(.nk-plain-alert) .icon,
    .alert:not(.nk-plain-alert) > i:first-child {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem !important;
        background: color-mix(in srgb, var(--nk-tone, #3b82f6) 10%, white);
        color: var(--nk-tone, #3b82f6) !important;
        border: 1px solid color-mix(in srgb, var(--nk-tone, #3b82f6) 18%, #dbeafe);
        margin-right: 0 !important;
    }

    .alert-success { --nk-tone: #22c55e; }
    .alert-danger  { --nk-tone: #ef4444; }
    .alert-warning { --nk-tone: #f59e0b; }
    .alert-info    { --nk-tone: #3b82f6; }

    @media (max-width: 768px) {
        .nk-banner {
            padding: 14px 14px 14px 16px;
            flex-direction: column;
        }

        .nk-banner-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .nk-banner-link {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endonce

<div class="nk-message-stack">
    @if(is_array($banner) && trim((string) ($banner['message'] ?? '')) !== '')
        @php
            $bannerType = $banner['type'] ?? 'info';
            $bannerTone = in_array($bannerType, ['success', 'danger', 'warning', 'info']) ? $bannerType : 'info';
            $bannerTitle = trim((string) ($banner['title'] ?? 'Pemberitahuan'));
            $bannerMessage = trim((string) ($banner['message'] ?? ''));
            $bannerActionUrl = trim((string) ($banner['action_url'] ?? ''));
            $bannerActionLabel = trim((string) ($banner['action_label'] ?? 'Lihat detail'));
            $bannerIcons = [
                'success' => $iconSet === 'boxicons' ? 'bx bx-check-shield' : 'ti ti-shield-check',
                'danger' => $iconSet === 'boxicons' ? 'bx bx-error-alt' : 'ti ti-alert-octagon',
                'warning' => $iconSet === 'boxicons' ? 'bx bx-bell' : 'ti ti-bell-ringing',
                'info' => $iconSet === 'boxicons' ? 'bx bx-radio-circle-marked' : 'ti ti-radar-2',
            ];
        @endphp
        <div class="nk-banner" data-tone="{{ $bannerTone }}">
            <div class="nk-banner-main">
                <div class="nk-banner-icon"><i class="{{ $bannerIcons[$bannerTone] }}"></i></div>
                <div>
                    <div class="nk-banner-kicker">System Banner</div>
                    <h3 class="nk-banner-title">{{ $bannerTitle }}</h3>
                    <p class="nk-banner-message">{{ $bannerMessage }}</p>
                </div>
            </div>
            <div class="nk-banner-actions">
                @if($bannerActionUrl !== '')
                    <a class="nk-banner-link" href="{{ $bannerActionUrl }}">{{ $bannerActionLabel }}</a>
                @endif
                <button type="button" class="nk-banner-dismiss" onclick="this.closest('.nk-banner').remove()" aria-label="Tutup"></button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div>
                    <i class="{{ $iconSet === 'boxicons' ? 'bx bx-error-circle' : 'ti ti-alert-circle' }} icon alert-icon"></i>
                </div>
                <div>
                    <h4 class="alert-title">Perlu diperbaiki</h4>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!empty($flashItems))
        <div class="nk-flash-grid">
            @foreach ($flashItems as $item)
                <div class="nk-flash-card" data-tone="{{ $item['tone'] }}"
                    @if ($item['autoDismiss']) data-autodismiss="{{ $dismissAfter }}" @endif>
                    <div class="nk-flash-icon"><i class="{{ $item['icon'] }}"></i></div>
                    <div class="nk-flash-body">
                        <div class="nk-flash-kicker">Flash Message</div>
                        <h4 class="nk-flash-title">{{ $item['title'] }}</h4>
                        <div class="nk-flash-message">{{ $item['message'] }}</div>
                    </div>
                    <button type="button" class="nk-flash-dismiss btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="this.closest('.nk-flash-card').remove()"></button>
                </div>
            @endforeach
        </div>
    @endif
</div>

@if ($errors->any() || !empty($flashItems))
<script>
document.querySelectorAll('[data-autodismiss]').forEach(function(el) {
    if (el.dataset.dismissBound === '1') return;
    el.dataset.dismissBound = '1';

    var delay = parseInt(el.getAttribute('data-autodismiss'), 10) || 5000;
    var timer = null;
    var remaining = delay;
    var startedAt = null;

    function hideCard() {
        el.style.transition = 'opacity .22s ease, transform .22s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-4px)';
        setTimeout(function() { el.remove(); }, 220);
    }

    function startTimer() {
        startedAt = Date.now();
        timer = setTimeout(hideCard, remaining);
    }

    el.addEventListener('mouseenter', function() {
        clearTimeout(timer);
        if (startedAt) remaining -= (Date.now() - startedAt);
    });

    el.addEventListener('mouseleave', function() {
        startTimer();
    });

    startTimer();
});
</script>
@endif
