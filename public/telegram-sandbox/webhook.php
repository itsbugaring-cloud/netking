<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');

const BOT_VERSION = 'ux-v3';
const STEPS = ['NAMA', 'NO_HP', 'SN_ONT', 'PPPOE_USER', 'PPPOE_PASS', 'PAKET_KODE', 'HARGA', 'TANGGAL_PASANG'];
const REQUIRED = ['NAMA', 'NO_HP', 'SN_ONT', 'PPPOE_USER', 'PAKET_KODE', 'HARGA', 'TANGGAL_PASANG'];
const PACKAGE_CODES = ['6M', '8M', '10M', '15M', '20M'];
const DEFAULT_PPPOE_PASS = 'netking';

$cfgFile = __DIR__ . '/config.local.php';
if (!file_exists($cfgFile)) {
    http_response_code(500);
    echo 'missing config.local.php';
    exit;
}

$cfg = require $cfgFile;
$token = (string) ($cfg['bot_token'] ?? '');
$adminChatId = (string) ($cfg['admin_chat_id'] ?? '');
$secret = (string) ($cfg['webhook_secret'] ?? '');

if ($secret !== '' && (string) ($_GET['k'] ?? '') !== $secret) {
    http_response_code(403);
    echo 'forbidden';
    exit;
}

$raw = file_get_contents('php://input') ?: '';
if ($raw === '') {
    echo 'ok';
    exit;
}

$update = json_decode($raw, true);
if (!is_array($update)) {
    echo 'ok';
    exit;
}
storeRawPayload($update);

if (isset($update['callback_query']) && is_array($update['callback_query'])) {
    handleCallback($token, $adminChatId, $update['callback_query']);
    echo 'ok';
    exit;
}

$message = $update['message'] ?? $update['edited_message'] ?? null;
if (!is_array($message)) {
    echo 'ok';
    exit;
}

$chatId = (string) ($message['chat']['id'] ?? '');
if ($chatId === '' || $token === '') {
    echo 'ok';
    exit;
}

clearTrackedBotReplies($token, $chatId);

$text = trim((string) ($message['text'] ?? $message['caption'] ?? ''));
$photos = $message['photo'] ?? [];

if (!empty($photos) && $text === '') {
    $draft = loadDraft($chatId);
    $draft['photo'] = largestPhoto($photos);
    $draft['updated_at'] = date('c');
    saveDraft($chatId, $draft);

    sendMessage($token, $chatId, "✅ Foto SN diterima. Berikut preview terbaru ya:", mainKeyboard());
    showDraft($token, $chatId, true);
    echo 'ok';
    exit;
}

if (str_starts_with(strtolower($text), '/start')) {
    clearState($chatId);
    sendMessage($token, $chatId, startGreeting($message['from'] ?? [], $chatId), mainKeyboard());
    echo 'ok';
    exit;
}

if ($text === '/menu' || $text === '/help') {
    sendMessage($token, $chatId, helpMessage(), mainKeyboard());
    echo 'ok';
    exit;
}

if ($text === '/template' || $text === 'Template Format' || $text === '📝 Template Format') {
    sendMessage($token, $chatId, templateMessage(), inputInline());
    echo 'ok';
    exit;
}

if ($text === '/draft' || $text === 'Lihat Draft' || $text === '📋 Lihat Draft') {
    showDraft($token, $chatId, true);
    echo 'ok';
    exit;
}

if ($text === '/submit' || $text === 'Submit Test' || $text === '✅ Submit Test') {
    submitDraft($token, $adminChatId, $chatId);
    echo 'ok';
    exit;
}

if ($text === '/reset' || $text === 'Reset Draft' || $text === '♻️ Reset Draft') {
    clearDraft($chatId);
    clearState($chatId);
    sendMessage($token, $chatId, "♻️ Draft dan input state berhasil direset.", mainKeyboard());
    echo 'ok';
    exit;
}

if ($text === '/cancel') {
    clearState($chatId);
    sendMessage($token, $chatId, "❌ Input dibatalkan.", mainKeyboard());
    echo 'ok';
    exit;
}

if ($text === 'Panduan Kode PPPoE' || $text === '🧭 Panduan Kode PPPoE') {
    sendMessage($token, $chatId, pppoeGuideMessage(), mainKeyboard());
    echo 'ok';
    exit;
}

if ($text === 'Kirim Foto SN' || $text === '📷 Kirim Foto SN') {
    sendMessage($token, $chatId, "📷 Silakan kirim foto label SN ONT sekarang.", mainKeyboard());
    echo 'ok';
    exit;
}

if ($text === 'Input Data Pelanggan' || $text === '🧾 Input Data Pelanggan') {
    startWizard($token, $chatId);
    echo 'ok';
    exit;
}

$state = loadState($chatId);
if (($state['mode'] ?? '') === 'wizard') {
    handleWizardText($token, $chatId, $text, $state);
    echo 'ok';
    exit;
}

if ($text !== '') {
    [$parsed, $errors] = parseBlockFormat($text);
    if (!empty($errors)) {
        sendMessage($token, $chatId, "⚠️ Format belum valid:\n- " . implode("\n- ", $errors), inputInline());
        echo 'ok';
        exit;
    }

    $draft = loadDraft($chatId);
    $draft['parsed'] = $parsed;
    $draft['updated_at'] = date('c');
    saveDraft($chatId, $draft);

    showDraft($token, $chatId, true);
}

echo 'ok';

function startGreeting(array $from, string $chatId): string
{
    $name = trim((string) ($from['first_name'] ?? 'Partner'));
    $username = trim((string) ($from['username'] ?? ''));
    $usernameText = $username === '' ? '(belum ada username)' : "@{$username}";

    return "🚀 NETKING Automation Bot (" . BOT_VERSION . ")\n\n"
        . "👋 Halo {$name}\n"
        . "🔹 Username: {$usernameText}\n"
        . "🔹 Chat ID: {$chatId}\n\n"
        . "🧪 Mode TESTING aktif (aman, belum push MikroTik/OLT).\n"
        . "👉 Klik *🧾 Input Data Pelanggan* untuk mulai input satu-per-satu.\n\n"
        . "Ketik /menu untuk daftar perintah.";
}

function helpMessage(): string
{
    return "📚 MENU COMMAND\n\n"
        . "/start - buka menu utama\n"
        . "/menu - bantuan command\n"
        . "/template - contoh format final\n"
        . "/draft - lihat hasil draft akhir\n"
        . "/submit - submit draft testing\n"
        . "/reset - reset draft\n"
        . "/cancel - batalkan input berjalan";
}

function templateMessage(): string
{
    return "📝 FORMAT FINAL\n\n"
        . "NAMA: PAK ENDE\n"
        . "NO_HP: 085942003799\n"
        . "SN_ONT: CDTCAFD3A1F7\n"
        . "PPPOE_USER: NPL-064\n"
        . "PPPOE_PASS: netking\n"
        . "PAKET_KODE: 10M\n"
        . "HARGA: 150000\n"
        . "TANGGAL_PASANG: 2026-04-22";
}

function pppoeGuideMessage(): string
{
    return "🧭 PANDUAN KODE PPPoE (TEST)\n\n"
        . "- NPL-xxx : Area NPL\n"
        . "- TSM-xxx : Area Tasikmalaya\n"
        . "- MJL-xxx : Area Majalaya\n"
        . "- KWB-xxx : Area Karawang Batujaya\n"
        . "- KWS-xxx : Area Karawang Kalangsuria\n\n"
        . "Contoh: NPL-064";
}

function mainKeyboard(): array
{
    return [
        'keyboard' => [
            [['text' => '🧾 Input Data Pelanggan'], ['text' => '📷 Kirim Foto SN']],
            [['text' => '📋 Lihat Draft'], ['text' => '✅ Submit Test']],
            [['text' => '🧭 Panduan Kode PPPoE'], ['text' => '♻️ Reset Draft']],
        ],
        'resize_keyboard' => true,
        'one_time_keyboard' => false,
    ];
}

function inputInline(): array
{
    return [
        'inline_keyboard' => [
            [
                ['text' => '📝 Lihat Template', 'callback_data' => 'show_template'],
                ['text' => '🧭 Panduan Kode PPPoE', 'callback_data' => 'show_pppoe_guide'],
            ],
            [
                ['text' => '🧾 Input Satu per Satu', 'callback_data' => 'start_wizard'],
            ],
        ],
    ];
}

function wizardInline(int $step): array
{
    $field = STEPS[$step] ?? '';
    $buttons = [];

    if ($field === 'SN_ONT') {
        $buttons[] = [['text' => '📷 Upload Foto SN', 'callback_data' => 'send_photo_hint']];
    }
    if ($field === 'PPPOE_USER') {
        $buttons[] = [['text' => '🧭 Panduan Kode PPPoE', 'callback_data' => 'show_pppoe_guide']];
    }
    if ($field === 'PPPOE_PASS') {
        $buttons[] = [['text' => '🔐 Gunakan netking', 'callback_data' => 'set_default_pass']];
    }
    if ($field === 'PAKET_KODE') {
        $buttons[] = [
            ['text' => '6M', 'callback_data' => 'set_pkg:6M'],
            ['text' => '8M', 'callback_data' => 'set_pkg:8M'],
            ['text' => '10M', 'callback_data' => 'set_pkg:10M'],
        ];
        $buttons[] = [
            ['text' => '15M', 'callback_data' => 'set_pkg:15M'],
            ['text' => '20M', 'callback_data' => 'set_pkg:20M'],
        ];
    }
    if ($field === 'HARGA') {
        $buttons[] = [
            ['text' => '100000', 'callback_data' => 'set_price:100000'],
            ['text' => '125000', 'callback_data' => 'set_price:125000'],
            ['text' => '150000', 'callback_data' => 'set_price:150000'],
        ];
    }
    if ($field === 'TANGGAL_PASANG') {
        $buttons[] = [
            ['text' => 'Hari ini', 'callback_data' => 'set_date:today'],
            ['text' => 'Kemarin', 'callback_data' => 'set_date:yesterday'],
        ];
    }

    $buttons[] = [['text' => '❌ Batal Input', 'callback_data' => 'cancel_wizard']];
    return ['inline_keyboard' => $buttons];
}

function actionInline(): array
{
    return [
        'inline_keyboard' => [
            [
                ['text' => '✅ Submit Test', 'callback_data' => 'submit_test'],
                ['text' => '♻️ Reset Draft', 'callback_data' => 'reset_draft'],
            ],
            [
                ['text' => '📷 Upload Foto SN', 'callback_data' => 'send_photo_hint'],
            ],
        ],
    ];
}

function startWizard(string $token, string $chatId): void
{
    setState($chatId, ['mode' => 'wizard', 'step' => 0]);
    promptStep($token, $chatId, 0);
}

function promptStep(string $token, string $chatId, int $step): void
{
    $field = STEPS[$step] ?? null;
    if ($field === null) {
        clearState($chatId);
        sendMessage($token, $chatId, "✅ Input selesai.", mainKeyboard());
        return;
    }

    loadingUx($token, $chatId, 'Menyiapkan input');

    $msg = "✍️ Masukkan {$field}:";
    if ($field === 'PPPOE_PASS') {
        $msg = "🔐 PPPOE_PASS default selalu `" . DEFAULT_PPPOE_PASS . "`.\nKlik tombol *Gunakan netking*.";
    } elseif ($field === 'TANGGAL_PASANG') {
        $msg .= "\nFormat: YYYY-MM-DD (contoh 2026-04-22)";
    }

    sendMessage($token, $chatId, $msg, wizardInline($step));
}

function handleWizardText(string $token, string $chatId, string $text, array $state): void
{
    $step = (int) ($state['step'] ?? 0);
    $field = STEPS[$step] ?? null;
    if ($field === null) {
        clearState($chatId);
        sendMessage($token, $chatId, "⚠️ State input tidak valid.", mainKeyboard());
        return;
    }

    if ($field === 'PPPOE_PASS') {
        setDraftValue($chatId, 'PPPOE_PASS', DEFAULT_PPPOE_PASS);
        goNextStep($token, $chatId, $step);
        return;
    }

    if ($text === '') {
        sendMessage($token, $chatId, "⚠️ Input {$field} tidak boleh kosong.", wizardInline($step));
        return;
    }

    setDraftValue($chatId, $field, normalizeByField($field, $text));
    goNextStep($token, $chatId, $step);
}

function goNextStep(string $token, string $chatId, int $currentStep): void
{
    $next = $currentStep + 1;
    if ($next >= count(STEPS)) {
        clearState($chatId);
        showDraft($token, $chatId, true);
        return;
    }

    setState($chatId, ['mode' => 'wizard', 'step' => $next]);
    promptStep($token, $chatId, $next);
}

function normalizeByField(string $field, string $value): string
{
    $value = trim($value);
    if ($field === 'SN_ONT') {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $value));
    }
    if ($field === 'PPPOE_USER') {
        return strtoupper($value);
    }
    if ($field === 'PAKET_KODE') {
        return strtoupper($value);
    }
    if ($field === 'HARGA') {
        return (string) preg_replace('/\D+/', '', $value);
    }
    if ($field === 'TANGGAL_PASANG') {
        return normalizeDate($value) ?? $value;
    }
    return $value;
}

function parseBlockFormat(string $text): array
{
    $map = [];
    $lines = preg_split('/\R/u', $text) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || !preg_match('/^([A-Za-z0-9_ ]+)\s*[:;]\s*(.+)$/u', $line, $m)) {
            continue;
        }
        $key = strtoupper(str_replace(' ', '_', trim($m[1])));
        $val = trim($m[2]);
        $aliases = [
            'NOHP' => 'NO_HP',
            'NO_TELP' => 'NO_HP',
            'PHONE' => 'NO_HP',
            'PASSWORD_PPPOE' => 'PPPOE_PASS',
            'PPPOE_PASSWORD' => 'PPPOE_PASS',
            'TGL_IKR&PS' => 'TANGGAL_PASANG',
            'TGL_PASANG' => 'TANGGAL_PASANG',
            'TGL_AKTIF' => 'TANGGAL_PASANG',
        ];
        if (isset($aliases[$key])) {
            $key = $aliases[$key];
        }
        $map[$key] = normalizeByField($key, $val);
    }
    $map['PPPOE_PASS'] = DEFAULT_PPPOE_PASS;
    return validateDraftMap($map);
}

function validateDraftMap(array $map): array
{
    $errors = [];
    foreach (REQUIRED as $key) {
        if (!isset($map[$key]) || trim((string) $map[$key]) === '') {
            $errors[] = "{$key} wajib diisi";
        }
    }

    $map['PPPOE_PASS'] = DEFAULT_PPPOE_PASS;

    if (!empty($map['SN_ONT'])) {
        $len = strlen((string) $map['SN_ONT']);
        if ($len < 8 || $len > 30) {
            $errors[] = 'SN_ONT panjang harus 8-30 karakter';
        }
    }

    if (!empty($map['PAKET_KODE']) && !in_array(strtoupper((string) $map['PAKET_KODE']), PACKAGE_CODES, true)) {
        $errors[] = 'PAKET_KODE harus: 6M/8M/10M/15M/20M';
    }

    if (!empty($map['HARGA']) && ((int) $map['HARGA']) <= 0) {
        $errors[] = 'HARGA tidak valid';
    }

    if (!empty($map['TANGGAL_PASANG'])) {
        $normalized = normalizeDate((string) $map['TANGGAL_PASANG']);
        if ($normalized === null) {
            $errors[] = 'TANGGAL_PASANG tidak valid, pakai YYYY-MM-DD';
        } else {
            $map['TANGGAL_PASANG'] = $normalized;
        }
    }

    return [$map, array_values(array_unique($errors))];
}

function showDraft(string $token, string $chatId, bool $withAction): void
{
    $draft = loadDraft($chatId);
    $parsed = $draft['parsed'] ?? [];
    [$parsed, $errors] = validateDraftMap($parsed);

    $draft['parsed'] = $parsed;
    saveDraft($chatId, $draft);

    loadingUx($token, $chatId, 'Menyusun draft');

    if (!empty($errors)) {
        sendMessage($token, $chatId, "⚠️ Draft belum lengkap:\n- " . implode("\n- ", $errors), mainKeyboard());
        return;
    }

    $summary = draftSummaryText($parsed, !empty($draft['photo']), true);

    if (!empty($draft['photo']['file_id'])) {
        sendPhoto(
            $token,
            $chatId,
            (string) $draft['photo']['file_id'],
            $summary,
            $withAction ? actionInline() : null
        );
        if ($withAction) {
            sendMessage($token, $chatId, "👇 Kalau sudah cocok, klik *✅ Submit Test*.", actionInline());
        }
        return;
    }

    $msg = $summary . "\n\n⚠️ Lanjutkan: kirim foto SN dulu, lalu Submit Test.";
    sendMessage($token, $chatId, $msg, $withAction ? actionInline() : mainKeyboard());
}

function submitDraft(string $token, string $adminChatId, string $chatId): void
{
    $draft = loadDraft($chatId);
    [$parsed, $errors] = validateDraftMap($draft['parsed'] ?? []);
    if (!empty($errors)) {
        sendMessage($token, $chatId, "⚠️ Draft belum valid:\n- " . implode("\n- ", $errors), mainKeyboard());
        return;
    }

    if (empty($draft['photo'])) {
        sendMessage($token, $chatId, "⚠️ Foto SN belum ada. Kirim foto SN dulu baru submit.", actionInline());
        return;
    }

    loadingUx($token, $chatId, 'Mengirim ke admin');

    $draft['parsed'] = $parsed;
    $ref = saveFinalRequest($chatId, $draft);
    sendMessage(
        $token,
        $chatId,
        "✅ Submit test berhasil.\n📌 Ref: {$ref}\n📣 Notifikasi admin sudah dikirim.",
        mainKeyboard()
    );

    if ($adminChatId !== '') {
        $adminText = "🚨 REQUEST BARU (TEST MODE)\n"
            . "Ref: {$ref}\n"
            . "Chat: {$chatId}\n"
            . "Nama: {$parsed['NAMA']}\n"
            . "No HP: {$parsed['NO_HP']}\n"
            . "PPPOE_USER: {$parsed['PPPOE_USER']}\n"
            . "PPPOE_PASS: " . DEFAULT_PPPOE_PASS . "\n"
            . "SN ONT: {$parsed['SN_ONT']}\n"
            . "PAKET_KODE: {$parsed['PAKET_KODE']}\n"
            . "Harga: Rp " . number_format((int) $parsed['HARGA'], 0, ',', '.') . "\n"
            . "Tanggal Pasang: {$parsed['TANGGAL_PASANG']}\n"
            . "Foto SN: ✅ Ada\n\n"
            . "Status: menunggu verifikasi admin.";

        if (!empty($draft['photo']['file_id'])) {
            sendPhoto($token, $adminChatId, (string) $draft['photo']['file_id'], $adminText, null, false);
        } else {
            sendMessage($token, $adminChatId, $adminText, null, false);
        }
    }
}

function handleCallback(string $token, string $adminChatId, array $cb): void
{
    $callbackId = (string) ($cb['id'] ?? '');
    $data = (string) ($cb['data'] ?? '');
    $chatId = (string) ($cb['message']['chat']['id'] ?? '');

    if ($callbackId !== '') {
        answerCallback($token, $callbackId, 'OK');
    }
    if ($chatId === '') {
        return;
    }

    clearTrackedBotReplies($token, $chatId);

    if ($data === 'start_wizard') {
        startWizard($token, $chatId);
        return;
    }
    if ($data === 'show_template') {
        sendMessage($token, $chatId, templateMessage(), inputInline());
        return;
    }
    if ($data === 'show_pppoe_guide') {
        sendMessage($token, $chatId, pppoeGuideMessage(), mainKeyboard());
        return;
    }
    if ($data === 'send_photo_hint') {
        sendMessage($token, $chatId, "📷 Silakan kirim foto label SN ONT sekarang.", mainKeyboard());
        return;
    }
    if ($data === 'cancel_wizard') {
        clearState($chatId);
        sendMessage($token, $chatId, "❌ Input dibatalkan.", mainKeyboard());
        return;
    }
    if ($data === 'set_default_pass') {
        setDraftValue($chatId, 'PPPOE_PASS', DEFAULT_PPPOE_PASS);
        $state = loadState($chatId);
        goNextStep($token, $chatId, (int) ($state['step'] ?? 0));
        return;
    }
    if (str_starts_with($data, 'set_pkg:')) {
        setDraftValue($chatId, 'PAKET_KODE', strtoupper(substr($data, 8)));
        $state = loadState($chatId);
        goNextStep($token, $chatId, (int) ($state['step'] ?? 0));
        return;
    }
    if (str_starts_with($data, 'set_price:')) {
        setDraftValue($chatId, 'HARGA', (string) preg_replace('/\D+/', '', substr($data, 10)));
        $state = loadState($chatId);
        goNextStep($token, $chatId, (int) ($state['step'] ?? 0));
        return;
    }
    if (str_starts_with($data, 'set_date:')) {
        $dateCmd = substr($data, 9);
        $date = $dateCmd === 'yesterday' ? date('Y-m-d', strtotime('-1 day')) : date('Y-m-d');
        setDraftValue($chatId, 'TANGGAL_PASANG', $date);
        $state = loadState($chatId);
        goNextStep($token, $chatId, (int) ($state['step'] ?? 0));
        return;
    }
    if ($data === 'submit_test') {
        submitDraft($token, $adminChatId, $chatId);
        return;
    }
    if ($data === 'reset_draft') {
        clearDraft($chatId);
        clearState($chatId);
        sendMessage($token, $chatId, "♻️ Draft sudah direset.", mainKeyboard());
    }
}

function sendMessage(string $token, string $chatId, string $text, ?array $markup = null, bool $trackReply = true): array
{
    $payload = [
        'chat_id' => $chatId,
        'text' => $text,
    ];
    if ($markup !== null) {
        $payload['reply_markup'] = json_encode($markup);
    }
    $res = tgApi($token, 'sendMessage', $payload);
    if ($trackReply) {
        trackBotReplyMessage($chatId, (int) ($res['result']['message_id'] ?? 0));
    }
    return $res;
}

function sendChatAction(string $token, string $chatId, string $action = 'typing'): void
{
    $url = "https://api.telegram.org/bot{$token}/sendChatAction";
    $payload = [
        'chat_id' => $chatId,
        'action' => $action,
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_TIMEOUT => 8,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function typingUx(string $token, string $chatId, int $pulses = 2, int $delayMs = 320): void
{
    for ($i = 0; $i < $pulses; $i++) {
        sendChatAction($token, $chatId, 'typing');
        usleep($delayMs * 1000);
    }
}

function loadingUx(string $token, string $chatId, string $label = 'Memproses'): void
{
    // Guaranteed-visible ASCII loading bar for all Telegram clients/fonts.
    $frames = [
        '[##--------]',
        '[#####-----]',
        '[########--]',
        '[##########]',
    ];

    $res = tgApi($token, 'sendMessage', [
        'chat_id' => $chatId,
        'text' => $frames[0],
    ]);

    $messageId = $res['result']['message_id'] ?? null;
    if (!is_numeric($messageId)) {
        sendChatAction($token, $chatId, 'typing');
        usleep(300 * 1000);
        return;
    }

    foreach (array_slice($frames, 1) as $frame) {
        usleep(90 * 1000);
        tgApi($token, 'editMessageText', [
            'chat_id' => $chatId,
            'message_id' => (int) $messageId,
            'text' => $frame,
        ]);
    }

    usleep(90 * 1000);
    tgApi($token, 'deleteMessage', [
        'chat_id' => $chatId,
        'message_id' => (int) $messageId,
    ]);
}

function sendPhoto(string $token, string $chatId, string $fileId, string $caption = '', ?array $markup = null, bool $trackReply = true): array
{
    $payload = [
        'chat_id' => $chatId,
        'photo' => $fileId,
    ];
    if ($caption !== '') {
        $payload['caption'] = $caption;
    }
    if ($markup !== null) {
        $payload['reply_markup'] = json_encode($markup);
    }
    $res = tgApi($token, 'sendPhoto', $payload);
    if ($trackReply) {
        trackBotReplyMessage($chatId, (int) ($res['result']['message_id'] ?? 0));
    }
    return $res;
}

function draftSummaryText(array $parsed, bool $hasPhoto, bool $testingMode): string
{
    $photoState = $hasPhoto ? '✅ Ada' : '❌ Belum ada';
    $mode = $testingMode ? 'TESTING (belum push MikroTik/OLT)' : 'PRODUCTION';

    return "🧾 HASIL LENGKAP DRAFT AKHIR\n\n"
        . "👤 NAMA: {$parsed['NAMA']}\n"
        . "📱 NO_HP: {$parsed['NO_HP']}\n"
        . "🔖 SN_ONT: {$parsed['SN_ONT']}\n"
        . "🌐 PPPOE_USER: {$parsed['PPPOE_USER']}\n"
        . "🔐 PPPOE_PASS: " . DEFAULT_PPPOE_PASS . "\n"
        . "📦 PAKET_KODE: {$parsed['PAKET_KODE']}\n"
        . "💰 HARGA: Rp " . number_format((int) $parsed['HARGA'], 0, ',', '.') . "\n"
        . "📅 TANGGAL_PASANG: {$parsed['TANGGAL_PASANG']}\n\n"
        . "📷 FOTO_SN: {$photoState}\n"
        . "🧪 MODE: {$mode}";
}

function answerCallback(string $token, string $callbackId, string $text = ''): void
{
    $url = "https://api.telegram.org/bot{$token}/answerCallbackQuery";
    $payload = ['callback_query_id' => $callbackId];
    if ($text !== '') {
        $payload['text'] = $text;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_TIMEOUT => 15,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function tgApi(string $token, string $method, array $payload): array
{
    $url = "https://api.telegram.org/bot{$token}/{$method}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_TIMEOUT => 15,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);

    if (!is_string($raw) || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function normalizeDate(string $raw): ?string
{
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }

    foreach (['Y-m-d', 'd-m-Y', 'd/m/Y'] as $fmt) {
        $dt = DateTime::createFromFormat($fmt, $raw);
        if ($dt && $dt->format($fmt) === $raw) {
            return $dt->format('Y-m-d');
        }
    }
    $ts = strtotime($raw);
    return $ts === false ? null : date('Y-m-d', $ts);
}

function largestPhoto(array $photos): ?array
{
    if (empty($photos)) {
        return null;
    }
    usort($photos, fn($a, $b) => ($a['file_size'] ?? 0) <=> ($b['file_size'] ?? 0));
    return end($photos) ?: null;
}

function setDraftValue(string $chatId, string $key, string $value): void
{
    $draft = loadDraft($chatId);
    if (!isset($draft['parsed']) || !is_array($draft['parsed'])) {
        $draft['parsed'] = [];
    }
    $draft['parsed'][$key] = $value;
    $draft['parsed']['PPPOE_PASS'] = DEFAULT_PPPOE_PASS;
    $draft['updated_at'] = date('c');
    saveDraft($chatId, $draft);
}

function loadUiState(string $chatId): array
{
    $path = storageDir() . "/ui_{$chatId}.json";
    if (!file_exists($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function saveUiState(string $chatId, array $state): void
{
    file_put_contents(storageDir() . "/ui_{$chatId}.json", json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function trackBotReplyMessage(string $chatId, int $messageId): void
{
    if ($messageId <= 0) {
        return;
    }
    $ui = loadUiState($chatId);
    $ids = array_values(array_filter(array_map('intval', (array) ($ui['bot_reply_message_ids'] ?? []))));
    $ids[] = $messageId;
    $ids = array_values(array_unique($ids));
    if (count($ids) > 30) {
        $ids = array_slice($ids, -30);
    }
    $ui['bot_reply_message_ids'] = $ids;
    saveUiState($chatId, $ui);
}

function deleteMessageSafe(string $token, string $chatId, int $messageId): void
{
    if ($messageId <= 0) {
        return;
    }
    tgApi($token, 'deleteMessage', [
        'chat_id' => $chatId,
        'message_id' => $messageId,
    ]);
}

function clearTrackedBotReplies(string $token, string $chatId): void
{
    $ui = loadUiState($chatId);
    $ids = array_values(array_filter(array_map('intval', (array) ($ui['bot_reply_message_ids'] ?? []))));
    foreach ($ids as $id) {
        deleteMessageSafe($token, $chatId, $id);
    }
    $ui['bot_reply_message_ids'] = [];
    saveUiState($chatId, $ui);
}

function storageDir(): string
{
    $dir = __DIR__ . '/storage';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir;
}

function loadState(string $chatId): array
{
    $path = storageDir() . "/state_{$chatId}.json";
    if (!file_exists($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function setState(string $chatId, array $state): void
{
    file_put_contents(storageDir() . "/state_{$chatId}.json", json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function clearState(string $chatId): void
{
    $path = storageDir() . "/state_{$chatId}.json";
    if (file_exists($path)) {
        @unlink($path);
    }
}

function loadDraft(string $chatId): array
{
    $path = storageDir() . "/draft_{$chatId}.json";
    if (!file_exists($path)) {
        return ['parsed' => ['PPPOE_PASS' => DEFAULT_PPPOE_PASS], 'photo' => null];
    }
    $data = json_decode((string) file_get_contents($path), true);
    if (!is_array($data)) {
        return ['parsed' => ['PPPOE_PASS' => DEFAULT_PPPOE_PASS], 'photo' => null];
    }
    if (!isset($data['parsed']) || !is_array($data['parsed'])) {
        $data['parsed'] = [];
    }
    $data['parsed']['PPPOE_PASS'] = DEFAULT_PPPOE_PASS;
    return $data;
}

function saveDraft(string $chatId, array $draft): void
{
    file_put_contents(storageDir() . "/draft_{$chatId}.json", json_encode($draft, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function clearDraft(string $chatId): void
{
    $path = storageDir() . "/draft_{$chatId}.json";
    if (file_exists($path)) {
        @unlink($path);
    }
}

function saveFinalRequest(string $chatId, array $draft): string
{
    $ref = "req_{$chatId}_" . date('Ymd_His') . '.json';
    $payload = [
        'mode' => 'testing-submitted',
        'submitted_at' => date('c'),
        'chat_id' => $chatId,
        'parsed' => $draft['parsed'] ?? [],
        'photo' => $draft['photo'] ?? null,
    ];
    file_put_contents(storageDir() . "/{$ref}", json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return $ref;
}

function storeRawPayload(array $update): void
{
    file_put_contents(
        storageDir() . '/raw_' . date('Ymd') . '.log',
        json_encode($update, JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND
    );
}
