@props(['dismissAfter' => 5000, 'iconSet' => 'tabler'])

@php
use Illuminate\Support\Str;

$types = [
    'success' => [
        'class' => 'alert-success',
        'icon_tabler' => 'ti ti-check',
        'icon_box' => 'bx bx-check-circle',
        'title' => 'Berhasil!',
        'autoDismiss' => true,
    ],
    'error' => [
        'class' => 'alert-danger',
        'icon_tabler' => 'ti ti-alert-circle',
        'icon_box' => 'bx bx-error-circle',
        'title' => 'Error!',
        'autoDismiss' => false,
    ],
    'warning' => [
        'class' => 'alert-warning',
        'icon_tabler' => 'ti ti-alert-triangle',
        'icon_box' => 'bx bx-error',
        'title' => 'Perhatian!',
        'autoDismiss' => false,
    ],
    'info' => [
        'class' => 'alert-info',
        'icon_tabler' => 'ti ti-info-circle',
        'icon_box' => 'bx bx-info-circle',
        'title' => 'Info',
        'autoDismiss' => true,
    ],
];
@endphp

@foreach ($types as $type => $config)
    @php
        $message = session($type);
    @endphp

    @if ($message && is_string($message))
        @php
            $displayMessage = $type === 'warning' ? Str::limit($message, 500) : $message;
            $iconClass = $iconSet === 'boxicons' ? $config['icon_box'] : $config['icon_tabler'];
        @endphp

        <div class="alert {{ $config['class'] }} alert-dismissible fade show" role="alert"
            @if ($config['autoDismiss'])
                data-autodismiss="{{ $dismissAfter }}"
            @endif
        >
            <div class="d-flex">
                <div>
                    <i class="{{ $iconClass }} icon alert-icon"></i>
                </div>
                <div>
                    <h4 class="alert-title">{{ $config['title'] }}</h4>
                    <div class="text-muted">{{ $displayMessage }}</div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach

@if (session('success') || session('info'))
<script>
document.querySelectorAll('[data-autodismiss]').forEach(function(el) {
    var delay = parseInt(el.getAttribute('data-autodismiss')) || 5000;
    var timer = null;
    var remaining = delay;
    var startTime = null;

    function startTimer() {
        startTime = Date.now();
        timer = setTimeout(function() { dismiss(el); }, remaining);
    }

    function dismiss(element) {
        element.classList.remove('show');
        setTimeout(function() { element.remove(); }, 300);
    }

    el.addEventListener('mouseenter', function() {
        clearTimeout(timer);
        remaining -= (Date.now() - startTime);
    });

    el.addEventListener('mouseleave', function() {
        startTimer();
    });

    startTimer();
});
</script>
@endif
