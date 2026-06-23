@props(['dismissAfter' => 5000, 'iconSet' => 'tabler'])

@php
use Illuminate\Support\Str;

$viewErrors = (($errors ?? null) instanceof \Illuminate\Support\ViewErrorBag)
    ? $errors
    : new \Illuminate\Support\ViewErrorBag();

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
    /* Toast container fixed in top-right corner */
    /* Toast styles removed in favor of SweetAlert2 */
    /* Banner styles (remains in-flow) */
    .nk-banner {
        position: relative;
        padding: 16px 18px 16px 20px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        border: 1px solid #e5e7eb !important;
        border-radius: 18px !important;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%) !important;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
        color: #0f172a !important;
        overflow: hidden;
        margin-bottom: 18px;
    }

    .nk-banner::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        border-radius: 18px 0 0 18px;
        background: var(--nk-tone, #3b82f6);
    }

    .nk-banner[data-tone="success"] { --nk-tone: #10b981; border-color: rgba(16, 185, 129, 0.2) !important; }
    .nk-banner[data-tone="danger"]  { --nk-tone: #ef4444; border-color: rgba(239, 68, 68, 0.2) !important; }
    .nk-banner[data-tone="warning"] { --nk-tone: #f59e0b; border-color: rgba(245, 158, 11, 0.2) !important; }
    .nk-banner[data-tone="info"]    { --nk-tone: #3b82f6; border-color: rgba(59, 130, 246, 0.2) !important; }

    .nk-banner-main {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        min-width: 0;
    }

    .nk-banner-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.15rem;
        color: var(--nk-tone, #3b82f6);
        background: color-mix(in srgb, var(--nk-tone, #3b82f6) 10%, white);
        border: 1px solid color-mix(in srgb, var(--nk-tone, #3b82f6) 18%, #dbeafe);
    }

    .nk-banner-kicker {
        font-size: .69rem;
        line-height: 1;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 7px;
    }

    .nk-banner-title {
        font-size: .95rem;
        line-height: 1.3;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 4px;
    }

    .nk-banner-message {
        font-size: .82rem;
        line-height: 1.5;
        color: #475569;
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
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .nk-banner-link:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 18px rgba(37, 99, 235, 0.08);
    }

    .nk-banner-dismiss {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        line-height: 1;
        padding: 0 0 4px 0;
        transition: all 0.2s ease;
    }

    .nk-banner-dismiss:hover {
        color: #475569;
        border-color: #cbd5e1;
    }

    @media (max-width: 768px) {
        .nk-toast-container {
            top: 16px;
            right: 16px;
            width: calc(100vw - 32px);
        }
        .nk-banner {
            flex-direction: column;
        }
    }
</style>
@endonce

<!-- System Banner (rendered in layout flow) -->
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
            <button type="button" class="nk-banner-dismiss" onclick="this.closest('.nk-banner').remove()" aria-label="Tutup">&times;</button>
        </div>
    </div>
@endif

<!-- Floating Toasts Container -->
<!-- SweetAlert2 Toasts -->
@if($viewErrors->any() || !empty($flashItems))
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swal === 'undefined') return;
    
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: {{ $dismissAfter ?? 5000 }},
        timerProgressBar: true,
        background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#ffffff',
        color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f8fafc' : '#0f172a',
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    @if($viewErrors->any())
        Toast.fire({
            icon: 'error',
            title: 'Perlu diperbaiki',
            html: `
                <ul class="text-start mb-0 ps-3" style="font-size: 0.85rem; margin-top: 4px;">
                    @foreach($viewErrors->all() as $error)
                        <li>{{ addslashes($error) }}</li>
                    @endforeach
                </ul>
            `
        });
    @endif

    @if(!empty($flashItems))
        @foreach ($flashItems as $item)
            @php
                $icon = 'info';
                if ($item['tone'] === 'success') $icon = 'success';
                if ($item['tone'] === 'danger') $icon = 'error';
                if ($item['tone'] === 'warning') $icon = 'warning';
            @endphp
            Toast.fire({
                icon: '{{ $icon }}',
                title: '{!! addslashes($item['title']) !!}',
                html: '<span style="font-size: 0.85rem;">{!! addslashes(strip_tags($item['message'])) !!}</span>'
            });
        @endforeach
    @endif
});
</script>
@endif
