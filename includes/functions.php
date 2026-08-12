<?php
/**
 * Shared helper functions used across the public site and the admin panel.
 * Requires includes/config.php to already be loaded ($pdo available).
 */

// ---------------------------------------------------------------------------
// Language / translation
// ---------------------------------------------------------------------------

function current_lang(): string
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    return $_SESSION['lang'] ?? DEFAULT_LANG;
}

function is_rtl(): bool
{
    return current_lang() === 'ar';
}

/** Load the translation array for the active language (cached per request). */
function trans_all(): array
{
    static $cache = [];
    $lang = current_lang();
    if (!isset($cache[$lang])) {
        $file = __DIR__ . '/../lang/' . $lang . '.php';
        $cache[$lang] = file_exists($file) ? require $file : [];
    }
    return $cache[$lang];
}

/** t('nav.home') -> translated string, falls back to the key itself. */
function t(string $key): string
{
    $all = trans_all();
    return $all[$key] ?? $key;
}

/** Pick the right language column, e.g. field($row, 'title') -> title_ar or title_en. */
function field(array $row, string $base): string
{
    $col = $base . '_' . current_lang();
    return $row[$col] ?? ($row[$base . '_en'] ?? '');
}

function lang_url(string $lang): string
{
    $qs = $_GET;
    $qs['lang'] = $lang;
    return '?' . http_build_query($qs);
}

// ---------------------------------------------------------------------------
// Output helpers
// ---------------------------------------------------------------------------

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function json_list(?string $json): array
{
    if (!$json) return [];
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function slugify(string $text): string
{
    $text = trim($text);
    $translit = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = $translit !== false ? $translit : $text;
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : ('item-' . substr(md5((string)microtime(true)), 0, 6));
}

// ---------------------------------------------------------------------------
// Settings
// ---------------------------------------------------------------------------

function get_settings(PDO $pdo): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach ($pdo->query('SELECT setting_key, setting_value FROM settings') as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache;
}

function setting(PDO $pdo, string $key, string $default = ''): string
{
    $s = get_settings($pdo);
    return $s[$key] ?? $default;
}

// ---------------------------------------------------------------------------
// CSRF protection
// ---------------------------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $sent = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $sent);
}

// ---------------------------------------------------------------------------
// Admin auth
// ---------------------------------------------------------------------------

function admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_role(string ...$roles): void
{
    require_admin();
    if (!in_array($_SESSION['admin_role'] ?? '', $roles, true)) {
        http_response_code(403);
        die('غير مسموح لك بالوصول لهذه الصفحة / You are not allowed to access this page.');
    }
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_get(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

// ---------------------------------------------------------------------------
// Misc
// ---------------------------------------------------------------------------

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
