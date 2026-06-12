<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\IpamController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Isolir Landing Page
Route::view('/isolir', 'isolir')->name('isolir.page');

// Landing page
Route::get('/', function () {
    $buildMeta = static function (string $filename): array {
        $path = public_path('downloads/' . $filename);

        if (!file_exists($path)) {
            return [
                'available' => false,
                'updated_label' => null,
                'size_label' => null,
            ];
        }

        $bytes = filesize($path) ?: 0;
        $sizeMb = $bytes > 0 ? round($bytes / 1024 / 1024, 1) : 0;

        return [
            'available' => true,
            'updated_label' => \Carbon\Carbon::createFromTimestamp(filemtime($path))
                ->locale('id')
                ->translatedFormat('d M Y, H:i'),
            'size_label' => $sizeMb > 0 ? rtrim(rtrim(number_format($sizeMb, 1, '.', ''), '0'), '.') . ' MB' : null,
        ];
    };

    return view('landing', [
        'customerApk' => $buildMeta('netking-customer.apk'),
    ]);
})->name('landing');

Route::get('/download/customer', function () {
    $path = public_path('downloads/netking-customer.apk');
    abort_unless(file_exists($path), 404);

    return response()->download($path, 'netking-customer.apk', [
        'Content-Type' => 'application/vnd.android.package-archive',
    ]);
})->name('download.customer');

// Hotspot landing/status (captured from MikroTik redirect params)
Route::get('/hotspot/login', [\App\Http\Controllers\HotspotController::class, 'login'])->name('hotspot.login');
Route::get('/hotspot/status', [\App\Http\Controllers\HotspotController::class, 'status'])->name('hotspot.status');

// Public network status — used by landing page OLT badge (no auth required)
Route::get('/network-status', function () {
    $totalOlts  = \App\Models\Olt::count();
    $onlineOlts = \App\Models\Olt::where('status', 'active')->count();

    $totalOnts  = \App\Models\Ont::count();
    $onlineOnts = \App\Models\Ont::where('status', 'online')->count();

    return response()->json([
        'olt_total'  => $totalOlts,
        'olt_online' => $onlineOlts,
        'ont_total'  => $totalOnts,
        'ont_online' => $onlineOnts,
        'updated_at' => now()->format('H:i'),
    ])->header('Cache-Control', 'no-store');
})->name('network.status');

Route::get('/bayar', [\App\Http\Controllers\PaymentPageController::class, 'show'])->name('payment.public.root');
Route::get('/bayar/{customerCode}', [\App\Http\Controllers\PaymentPageController::class, 'show'])->name('payment.public');
Route::post('/bayar', [\App\Http\Controllers\PaymentPageController::class, 'submit'])->middleware('throttle:public_payment')->name('payment.public.submit');

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/', fn() => auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('admin.login')
    );

    // Guest routes (rate-limited to prevent brute force)
    Route::middleware(['guest', 'throttle:5,1'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    });

    // ── Base internal routes (Admin, Finance) ───────────────────────────────
    Route::middleware(['auth', 'role:admin,finance'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        // GET fallback: jika user buka /admin/logout langsung di browser → redirect ke login
        Route::get('/logout', fn() => redirect()->route('admin.login'))->name('logout.get');

        // Dashboard (scoped per role in controller)
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('/profile', fn() => view('admin.profile'))->name('profile');
        Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [\App\Http\Controllers\Admin\ProfileController::class, 'password'])->name('password.update');

        // UI Demo Component
        Route::get('/ui-demo', fn() => view('admin.ui-demo'))->name('ui-demo');
    });

    // ── Routes accessible by Admin and Finance ──────────────────────────────
    Route::middleware(['auth', 'role:admin,finance'])->group(function () {
        // Customers (read-only for finance, scoped per role in controller)
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->whereNumber('customer')->name('customers.show');
        Route::get('customers/{customer}/topology', [CustomerController::class, 'topology'])->whereNumber('customer')->name('customers.topology');
    });

    // ── Routes accessible by BOTH Admin and Finance ────────────────────────
    Route::middleware(['auth', 'role:admin,finance'])->group(function () {
        // [REMOVED] Invoice Management — replaced by Payment system

        // Billing Calendar
        Route::get('billing/calendar', [DashboardController::class, 'billingCalendar'])->name('billing.calendar');
        Route::get('billing/calendar/view', fn() => view('admin.billing.calendar'))->name('billing.calendar.view');

        // Payment Management
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/quick', [PaymentController::class, 'quickPayment'])->name('quick');
            Route::get('/review', [PaymentController::class, 'reviewIndex'])->name('review');
            Route::post('/{payment}/approve', [PaymentController::class, 'approve'])->name('approve');
            Route::post('/bulk-approve', [PaymentController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/{payment}/reject', [PaymentController::class, 'reject'])->name('reject');
            Route::patch('/{payment}/manual-date', [PaymentController::class, 'updateManualDate'])->name('manual-date');
            Route::patch('/manual-dates/bulk', [PaymentController::class, 'bulkUpdateManualDates'])->name('manual-dates.bulk');
            Route::delete('/bulk-delete', [PaymentController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
            Route::get('/manual/{customer}', [PaymentController::class, 'manualPaymentForm'])->name('manual');
            Route::post('/manual/{customer}', [PaymentController::class, 'manualPaymentStore'])->name('manual.store');
        });
    });

    // ── Admin operational routes (customer provisioning) ───────────────────
    Route::middleware(['auth', 'role:admin'])->group(function () {

        // Customers (write access for admin)
        Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->whereNumber('customer')->name('customers.edit');
        Route::match(['put', 'patch'], 'customers/{customer}', [CustomerController::class, 'update'])->whereNumber('customer')->name('customers.update');
        Route::post('customers/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulkDelete');
        Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->whereNumber('customer')->name('customers.toggle-status');
        Route::post('customers/{customer}/retry-provision', [CustomerController::class, 'retryProvision'])->whereNumber('customer')->name('customers.retry-provision');
        Route::post('customers/{customer}/enable-pppoe', [CustomerController::class, 'enablePppoe'])->whereNumber('customer')->name('customers.enable-pppoe');
        Route::post('customers/{customer}/reset-portal-password', [CustomerController::class, 'resetPortalPassword'])->whereNumber('customer')->name('customers.reset-portal-password');
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->whereNumber('customer')->name('customers.destroy');
        Route::post('customers/{customer}/devices', [CustomerController::class, 'storeDevice'])->whereNumber('customer')->name('customers.devices.store');
        Route::put('customers/{customer}/devices/{device}', [CustomerController::class, 'updateDevice'])->whereNumber('customer')->name('customers.devices.update');
        Route::delete('customers/{customer}/devices/{device}', [CustomerController::class, 'destroyDevice'])->whereNumber('customer')->name('customers.devices.destroy');
        // Import customers
        Route::get('customers/import', [CustomerController::class, 'import'])->name('customers.import');
        Route::post('customers/import', [CustomerController::class, 'importProcess'])->name('customers.import.process');

        // AJAX: Get PPPoE profiles directly from the area's MikroTik router
        Route::get('api/packages-by-area', function (\Illuminate\Http\Request $request) {
            $areaId = $request->input('area_id');
            if (!$areaId) {
                return response()->json([]);
            }

            $user = auth()->user();
            if ($user && $user->role === 'partner' && (int) $user->area_id !== (int) $areaId) {
                abort(403);
            }

            $area = \App\Models\Area::find($areaId);
            if (!$area || empty($area->router_ip)) {
                return response()->json([]);
            }

            // Connect to MikroTik and fetch real profiles
            $mk = \App\Services\MikroTikService::forArea($area);
            $test = $mk->testConnection();
            if (!$test['success']) {
                return response()->json(['error' => 'Router tidak bisa dihubungi: ' . ($test['error'] ?? 'Unknown')], 503);
            }

            $profilesResult = $mk->getPppoeProfiles();
            if (!$profilesResult['success']) {
                return response()->json(['error' => 'Gagal ambil profiles dari router'], 503);
            }

            // Load matching packages from DB for price info
            $dbPackages = \App\Models\Package::where('area_id', $areaId)
                ->where('is_active', true)
                ->get()
                ->keyBy('mikrotik_profile');

            $profiles = [];
            foreach ($profilesResult['data'] as $profile) {
                $name = $profile['name'] ?? null;
                if (!$name || $name === 'default' || $name === 'default-encryption') continue;

                // Parse rate-limit from MikroTik (e.g. "10M/5M" or "10000000/5000000")
                $rateLimit = $profile['rate-limit'] ?? '';
                $speedDown = 0;
                $speedUp = 0;
                if (preg_match('/(\d+)[Mm]\/(\d+)[Mm]/', $rateLimit, $m)) {
                    $speedDown = (int)$m[1];
                    $speedUp = (int)$m[2];
                } elseif (preg_match('/(\d+)\/(\d+)/', $rateLimit, $m)) {
                    $speedDown = intval($m[1] / 1000000);
                    $speedUp = intval($m[2] / 1000000);
                }

                // Match with DB package for price and ID
                $dbPkg = $dbPackages->get($name);

                $profiles[] = [
                    'id'               => $dbPkg->id ?? null,
                    'name'             => $name,
                    'mikrotik_profile' => $name,
                    'speed_down'       => $dbPkg->speed_down ?? $speedDown,
                    'speed_up'         => $dbPkg->speed_up ?? $speedUp,
                    'rate_limit'       => $rateLimit,
                    'price'            => $dbPkg->price ?? 0,
                    'local_address'    => $profile['local-address'] ?? '',
                    'remote_address'   => $profile['remote-address'] ?? '',
                    'from_router'      => true,
                ];
            }

            // Collect local addresses from MikroTik profiles + existing customers
            $localAddresses = [];
            foreach ($profilesResult['data'] as $profile) {
                $la = $profile['local-address'] ?? '';
                if ($la && $la !== '' && !in_array($la, $localAddresses)) {
                    $localAddresses[] = $la;
                }
            }
            $existingLAs = \App\Models\Customer::where('area_id', $areaId)
                ->whereNotNull('local_address')
                ->where('local_address', '!=', '')
                ->distinct()
                ->pluck('local_address')
                ->toArray();
            foreach ($existingLAs as $la) {
                if (!in_array($la, $localAddresses)) {
                    $localAddresses[] = $la;
                }
            }
            sort($localAddresses);

            return response()->json([
                'profiles' => $profiles,
                'local_addresses' => $localAddresses,
            ]);
        })->name('api.packages-by-area');

        // AJAX: Get ODPs for a given area (used by create/edit customer form)
        Route::get('api/odps-by-area', function (\Illuminate\Http\Request $request) {
            $areaId = $request->input('area_id');
            if (!$areaId) {
                return response()->json([]);
            }
            $odps = \App\Models\Odp::where('area_id', $areaId)
                ->orderBy('name')
                ->get(['id', 'name', 'max_capacity'])
                ->map(fn($o) => [
                    'id'         => $o->id,
                    'name'       => $o->name,
                    'port_count' => $o->max_capacity ?? 8,
                ]);
            return response()->json($odps);
        })->name('api.odps-by-area');

        // AJAX: Generate next PPPoE username for an area
        Route::get('api/next-pppoe-user', function (\Illuminate\Http\Request $request) {
            $areaId = $request->input('area_id');
            if (!$areaId) return response()->json(['error' => 'No area'], 400);

            $area = \App\Models\Area::find($areaId);
            if (!$area) return response()->json(['error' => 'Area not found'], 404);

            $prefix = $area->pppoe_prefix ?: 'N';

            // Find max number for this prefix (e.g., NCW-022 → 22)
            $maxNum = \App\Models\Customer::where('pppoe_user', 'LIKE', $prefix . '-%')
                ->selectRaw("MAX(CAST(SUBSTRING(pppoe_user, ?) AS UNSIGNED)) as max_num", [strlen($prefix) + 2])
                ->value('max_num');

            $nextNum = ($maxNum ?? 0) + 1;
            $nextUser = $prefix . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

            return response()->json([
                'username' => $nextUser,
                'prefix'   => $prefix,
                'next_num' => $nextNum,
            ]);
        })->name('api.next-pppoe-user');

        // AJAX: Live dashboard stats (auto-refresh every 30s)
        Route::get('api/dashboard-live', function () {
            $data = [
                'active_customers' => \App\Models\Customer::where('status', 'active')->count(),
                'total_customers'  => \App\Models\Customer::count(),
                'pending_payments' => \App\Models\Payment::where('status', 'pending')->count(),
            ];

            // OLT ONT stats
            $data['ont_total']  = \App\Models\Ont::count();
            $data['ont_online'] = \App\Models\Ont::where('status', 'online')->count();

            return response()->json($data);
        })->name('api.dashboard-live');

        // Simple Queue Management
        Route::get('queues', [\App\Http\Controllers\Admin\QueueController::class, 'index'])->name('queues.index');
        Route::get('queues/create', [\App\Http\Controllers\Admin\QueueController::class, 'create'])->name('queues.create');
        Route::post('queues', [\App\Http\Controllers\Admin\QueueController::class, 'store'])->name('queues.store');
        Route::put('queues', [\App\Http\Controllers\Admin\QueueController::class, 'update'])->name('queues.update');
        Route::delete('queues', [\App\Http\Controllers\Admin\QueueController::class, 'destroy'])->name('queues.destroy');
        Route::get('queues/sync', [\App\Http\Controllers\Admin\QueueController::class, 'sync'])->name('queues.sync');

        // MikroTik Management (scoped per role in controller)
        Route::prefix('pppoe')->name('pppoe.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PppoeController::class, 'index'])->name('index');
            Route::post('/disconnect', [\App\Http\Controllers\Admin\PppoeController::class, 'disconnect'])->name('disconnect');
            Route::post('/toggle', [\App\Http\Controllers\Admin\PppoeController::class, 'toggle'])->name('toggle');
            Route::post('/sync-customers', [\App\Http\Controllers\Admin\PppoeController::class, 'syncCustomers'])->name('sync-customers');
            Route::get('/traffic', [\App\Http\Controllers\Admin\PppoeController::class, 'traffic'])->name('traffic');
            Route::get('/pools', [\App\Http\Controllers\Admin\PppoeController::class, 'pools'])->name('pools');
            Route::post('/ping', [\App\Http\Controllers\Admin\PppoeController::class, 'ping'])->name('ping');
        });

        // Address List (Isolir) Management
        Route::get('address-list', [\App\Http\Controllers\Admin\AddressListController::class, 'index'])->name('address-list.index');
        Route::post('address-list/isolate', [\App\Http\Controllers\Admin\AddressListController::class, 'isolate'])->name('address-list.isolate');
        Route::post('address-list/deisolate', [\App\Http\Controllers\Admin\AddressListController::class, 'deisolate'])->name('address-list.deisolate');
        Route::post('address-list/bulk-isolate', [\App\Http\Controllers\Admin\AddressListController::class, 'bulkIsolate'])->name('address-list.bulk-isolate');
        Route::post('address-list/sync', [\App\Http\Controllers\Admin\AddressListController::class, 'sync'])->name('address-list.sync');

        // ── Kalkulator Redaman (Fiber + Wireless) ─────────────────────────
        Route::prefix('redaman')->name('redaman.')->group(function () {
            Route::get('/',         [\App\Http\Controllers\Admin\RedamanController::class, 'index'])->name('index');
            Route::post('/',        [\App\Http\Controllers\Admin\RedamanController::class, 'store'])->name('store');
            Route::get('/history',  [\App\Http\Controllers\Admin\RedamanController::class, 'historyData'])->name('history');
            Route::delete('/{id}',  [\App\Http\Controllers\Admin\RedamanController::class, 'destroy'])->name('destroy');
        });

        // ── Cek Sinyal ONT ────────────────────────────────────────────────
        Route::prefix('signal')->name('signal.')->group(function () {
            Route::get('/',                     [\App\Http\Controllers\Admin\RedamanController::class, 'signalChecker'])->name('index');
            Route::get('/customers',            [\App\Http\Controllers\Admin\RedamanController::class, 'signalCustomers'])->name('customers');
            Route::get('/check/{customer}',     [\App\Http\Controllers\Admin\RedamanController::class, 'signalCheck'])->name('check');
        });

    });

    // ── Admin-only routes ──────────────────────────────────────────────────
    Route::middleware(['auth', 'admin'])->group(function () {

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/telegram/test-token', [SettingsController::class, 'telegramTestToken'])->name('settings.telegram.test-token');
        Route::post('/settings/telegram/set-webhook', [SettingsController::class, 'telegramSetWebhook'])->name('settings.telegram.set-webhook');
        Route::post('/settings/telegram/webhook-info', [SettingsController::class, 'telegramWebhookInfo'])->name('settings.telegram.webhook-info');
        Route::post('/settings/telegram/send-test', [SettingsController::class, 'telegramSendTestMessage'])->name('settings.telegram.send-test');
        Route::get('/telegram/requests', [\App\Http\Controllers\Admin\TelegramRequestController::class, 'index'])->name('telegram.requests.index');
        Route::get('/telegram/requests/{ref}', [\App\Http\Controllers\Admin\TelegramRequestController::class, 'show'])->name('telegram.requests.show');
        Route::get('/telegram/requests/{ref}/photo', [\App\Http\Controllers\Admin\TelegramRequestController::class, 'photo'])->name('telegram.requests.photo');
        Route::post('/telegram/requests/{ref}/approve', [\App\Http\Controllers\Admin\TelegramRequestController::class, 'approve'])->name('telegram.requests.approve');
        Route::post('/telegram/requests/{ref}/create-customer', [\App\Http\Controllers\Admin\TelegramRequestController::class, 'createCustomer'])->name('telegram.requests.create-customer');
        Route::post('/telegram/requests/{ref}/push-mikrotik', [\App\Http\Controllers\Admin\TelegramRequestController::class, 'pushMikrotik'])->name('telegram.requests.push-mikrotik');
        Route::post('/telegram/requests/{ref}/mark-online', [\App\Http\Controllers\Admin\TelegramRequestController::class, 'markOnline'])->name('telegram.requests.mark-online');
        Route::post('/telegram/requests/{ref}/reject', [\App\Http\Controllers\Admin\TelegramRequestController::class, 'reject'])->name('telegram.requests.reject');

        // Areas Management
        Route::resource('areas', AreaController::class);
        Route::post('areas/test-router', [AreaController::class, 'testRouter'])->name('areas.test-router');

        // Admin User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('reset-password');
        });

        // Bulk update billing start date for existing customers (CSV/XLSX)
        Route::get('customers/import-billing-start/template', [CustomerController::class, 'downloadBillingStartTemplate'])
            ->name('customers.import-billing-template');
        Route::post('customers/import-billing-start', [CustomerController::class, 'importBillingStartDates'])
            ->name('customers.import-billing-start');

        // Export customers to Excel
        Route::get('customers/export-excel', [CustomerController::class, 'exportExcel'])
            ->name('customers.export-excel');

        // [REMOVED] Commission Management — feature removed

        // Package Management
        Route::resource('packages', PackageController::class);
        Route::post('packages/sync-mikrotik', [PackageController::class, 'syncFromMikrotik'])->name('packages.sync-mikrotik');

        // Voucher Management
        Route::prefix('vouchers')->name('vouchers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\VoucherController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\VoucherController::class, 'create'])->name('create');
            Route::post('/generate', [\App\Http\Controllers\Admin\VoucherController::class, 'generate'])->name('generate');
            Route::get('/{voucher}', [\App\Http\Controllers\Admin\VoucherController::class, 'show'])->name('show');
            Route::delete('/{voucher}', [\App\Http\Controllers\Admin\VoucherController::class, 'destroy'])->name('destroy');
        });

        // [REMOVED] WhatsApp Gateway — feature removed

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/revenue', [\App\Http\Controllers\Admin\ReportController::class, 'revenue'])->name('revenue');
            Route::get('/billing', [\App\Http\Controllers\Admin\ReportController::class, 'billing'])->name('billing');
            Route::get('/payments', [\App\Http\Controllers\Admin\ReportController::class, 'paymentReport'])->name('payments');
            Route::get('/export-revenue', [\App\Http\Controllers\Admin\ReportController::class, 'exportRevenue'])->name('export-revenue');
            Route::get('/export-billing', [\App\Http\Controllers\Admin\ReportController::class, 'exportBilling'])->name('export-billing');
            Route::get('/export-payments', [\App\Http\Controllers\Admin\ReportController::class, 'exportPayments'])->name('export-payments');
        });

        // OLT Devices & ONT Inventory
        Route::get('olts/monitor', [\App\Http\Controllers\Admin\OltController::class, 'monitor'])->name('olts.monitor');
        Route::resource('olts', \App\Http\Controllers\Admin\OltController::class);
        Route::post('olts/{olt}/sync', [\App\Http\Controllers\Admin\OltController::class, 'sync'])->name('olts.sync');
        Route::post('olts/{olt}/sync-now', [\App\Http\Controllers\Admin\OltController::class, 'syncNow'])->name('olts.sync-now');
        Route::get('olts/{olt}/sync-status', [\App\Http\Controllers\Admin\OltController::class, 'syncStatus'])->name('olts.sync-status');
        Route::post('olts/{olt}/auto-link', [\App\Http\Controllers\Admin\OltController::class, 'autoLinkCustomers'])->name('olts.auto-link');
        Route::post('onts/{ont}/link-customer', [\App\Http\Controllers\Admin\OltController::class, 'linkCustomer'])->name('olts.link-customer');
        Route::post('onts/{ont}/reboot', [\App\Http\Controllers\Admin\OltController::class, 'rebootOnt'])->name('olts.reboot-ont');
        Route::post('onts/{ont}/wan-config', [\App\Http\Controllers\Admin\OltController::class, 'setWanConfig'])->name('olts.wan-config');
        Route::post('onts/{ont}/set-profile', [\App\Http\Controllers\Admin\OltController::class, 'setProfile'])->name('olts.set-profile');
        Route::post('olts/{olt}/onts/bulk-unlink', [\App\Http\Controllers\Admin\OltController::class, 'bulkUnlinkOnts'])->name('olts.onts.bulk-unlink');

        // ── Notifications & Activity Log ──
        Route::get('notifications/recent', [\App\Http\Controllers\Admin\NotificationController::class, 'recent'])->name('notifications.recent');
        Route::post('notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
        Route::post('notifications/{notification}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('notifications.read');
        Route::get('activity-log', [\App\Http\Controllers\Admin\NotificationController::class, 'activityLog'])->name('activity-log');

        // ── Inventory Module ──────────────────────────────────────────────────
        Route::prefix('inventory')->name('inventory.')->group(function () {

            // Dashboard
            Route::get('/', [\App\Http\Controllers\Admin\Inventory\DashboardController::class, 'index'])->name('dashboard');

            // Lokasi (Locations)
            Route::resource('lokasi', \App\Http\Controllers\Admin\Inventory\LokasiController::class);

            // Kategori (Categories — inline CRUD, no show page)
            Route::get('kategori', [\App\Http\Controllers\Admin\Inventory\KategoriController::class, 'index'])->name('kategori.index');
            Route::post('kategori', [\App\Http\Controllers\Admin\Inventory\KategoriController::class, 'store'])->name('kategori.store');
            Route::put('kategori/{invKategori}', [\App\Http\Controllers\Admin\Inventory\KategoriController::class, 'update'])->name('kategori.update');
            Route::delete('kategori/{invKategori}', [\App\Http\Controllers\Admin\Inventory\KategoriController::class, 'destroy'])->name('kategori.destroy');

            // Master Barang (Item Catalog)
            Route::resource('master-barang', \App\Http\Controllers\Admin\Inventory\MasterBarangController::class)
                ->parameters(['master-barang' => 'invMasterBarang']);

            // Unit / SN (Serialized Items — ONT, router, etc.)
            Route::resource('units', \App\Http\Controllers\Admin\Inventory\UnitController::class)
                ->parameters(['units' => 'invUnit']);
            Route::post('units/{invUnit}/mutasi',  [\App\Http\Controllers\Admin\Inventory\UnitController::class, 'mutasi'])->name('units.mutasi');
            Route::post('units/{invUnit}/pasang',  [\App\Http\Controllers\Admin\Inventory\UnitController::class, 'pasang'])->name('units.pasang');
            Route::post('units/{invUnit}/retur',   [\App\Http\Controllers\Admin\Inventory\UnitController::class, 'retur'])->name('units.retur');

            // Kabel (Cable reels)
            Route::resource('kabel', \App\Http\Controllers\Admin\Inventory\KabelController::class)
                ->parameters(['kabel' => 'invKabel'])
                ->except(['show']);
            Route::get('kabel/{invKabel}',         [\App\Http\Controllers\Admin\Inventory\KabelController::class, 'show'])->name('kabel.show');
            Route::post('kabel/{invKabel}/potong',  [\App\Http\Controllers\Admin\Inventory\KabelController::class, 'potong'])->name('kabel.potong');

            // Stok Qty (Non-serial bulk stock)
            Route::get('qty',                       [\App\Http\Controllers\Admin\Inventory\QtyController::class, 'index'])->name('qty.index');
            Route::post('qty',                      [\App\Http\Controllers\Admin\Inventory\QtyController::class, 'store'])->name('qty.store');
            Route::post('qty/{invQty}/tambah',      [\App\Http\Controllers\Admin\Inventory\QtyController::class, 'tambah'])->name('qty.tambah');
            Route::post('qty/{invQty}/kurangi',     [\App\Http\Controllers\Admin\Inventory\QtyController::class, 'kurangi'])->name('qty.kurangi');
            Route::post('qty/{invQty}/adjust',      [\App\Http\Controllers\Admin\Inventory\QtyController::class, 'adjust'])->name('qty.adjust');
            Route::delete('qty/{invQty}',           [\App\Http\Controllers\Admin\Inventory\QtyController::class, 'destroy'])->name('qty.destroy');

            // History / Transaction Log
            Route::get('history', [\App\Http\Controllers\Admin\Inventory\LogTransaksiController::class, 'index'])->name('history.index');
        });

        // ── System Dashboard (MikroTik health monitoring) ───────────────────
        Route::get('system-dashboard', [\App\Http\Controllers\Admin\SystemDashboardController::class, 'index'])->name('system-dashboard');
        Route::get('system-dashboard/data', [\App\Http\Controllers\Admin\SystemDashboardController::class, 'data'])->name('system-dashboard.data');

        // ── PPPoE Profile CRUD ────────────────────────────────────────────────
        Route::get('ppp-profiles', [\App\Http\Controllers\Admin\PppProfileController::class, 'index'])->name('ppp-profiles.index');
        Route::post('ppp-profiles', [\App\Http\Controllers\Admin\PppProfileController::class, 'store'])->name('ppp-profiles.store');
        Route::put('ppp-profiles', [\App\Http\Controllers\Admin\PppProfileController::class, 'update'])->name('ppp-profiles.update');
        Route::delete('ppp-profiles', [\App\Http\Controllers\Admin\PppProfileController::class, 'destroy'])->name('ppp-profiles.destroy');

        // ── Router Backups ────────────────────────────────────────────────────
        Route::get('backups', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [\App\Http\Controllers\Admin\BackupController::class, 'store'])->name('backups.store');
        Route::get('backups/{backup}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');
        Route::delete('backups/{backup}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('backups.destroy');

        // ── IPAM Module ───────────────────────────────────────────────────────
        Route::prefix('ipam')->name('ipam.')->group(function () {
            Route::get('/', [IpamController::class, 'dashboard'])->name('dashboard');
            Route::get('/routers', [IpamController::class, 'routers'])->name('routers.index');
            Route::post('/routers', [IpamController::class, 'storeRouter'])->name('routers.store');
            Route::get('/routers/export-csv', [IpamController::class, 'exportCsv'])->name('routers.export');
            Route::get('/routers/{router}', [IpamController::class, 'routerDetail'])->name('routers.show');
            Route::post('/routers/{router}/scan', [IpamController::class, 'scanRouter'])->name('routers.scan');
            Route::post('/routers/scan-all', [IpamController::class, 'scanAll'])->name('routers.scanAll');
            Route::post('/routers/{router}/map-olt', [IpamController::class, 'mapOlt'])->name('routers.mapOlt');
            Route::post('/routers/auto-map', [IpamController::class, 'autoMap'])->name('routers.autoMap');
            Route::delete('/routers/{router}', [IpamController::class, 'destroyRouter'])->name('routers.destroy');
            Route::get('/olts', [IpamController::class, 'olts'])->name('olts.index');
            Route::post('/olts', [IpamController::class, 'storeOlt'])->name('olts.store');
            Route::put('/olts/{olt}', [IpamController::class, 'updateOlt'])->name('olts.update');
            Route::delete('/olts/{olt}', [IpamController::class, 'destroyOlt'])->name('olts.destroy');
            Route::post('/olts/import-bookmarks', [IpamController::class, 'importBookmarks'])->name('olts.importBookmarks');
            Route::get('/subnets', [IpamController::class, 'subnets'])->name('subnets.index');
            Route::post('/subnets', [IpamController::class, 'storeSubnet'])->name('subnets.store');
            Route::put('/subnets/{subnet}', [IpamController::class, 'updateSubnet'])->name('subnets.update');
            Route::delete('/subnets/{subnet}', [IpamController::class, 'destroySubnet'])->name('subnets.destroy');
            Route::get('/subnets/utilization', [IpamController::class, 'subnetUtilization'])->name('subnets.utilization');
            Route::get('/subnets/suggestions', [IpamController::class, 'subnetSuggestions'])->name('subnets.suggestions');
            Route::get('/audit-log', [IpamController::class, 'auditLog'])->name('auditLog');
            Route::get('/settings', [IpamController::class, 'settings'])->name('settings');
            Route::post('/settings', [IpamController::class, 'updateSettings'])->name('settings.update');
        });
    });
});

Route::prefix('customer')->name('customer.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [\App\Http\Controllers\Customer\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Customer\AuthController::class, 'login'])->name('login.post');
    });

    Route::middleware('auth:customer')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Customer\AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [\App\Http\Controllers\Customer\DashboardController::class, 'index'])->name('dashboard');
        // [REMOVED] Customer invoice routes — replaced by Payment system
        Route::get('/profile', [\App\Http\Controllers\Customer\ProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile/password', [\App\Http\Controllers\Customer\ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::put('/profile/contact', [\App\Http\Controllers\Customer\ProfileController::class, 'updateContact'])->name('profile.contact');
    });
});

// [SECURITY PATCH 2026-03-21] Debug route removed — was unauthenticated, exposed OLT telnet access.

// [SECURITY PATCH 2026-04-01] Flux demo route removed — unauthenticated, no production value.
