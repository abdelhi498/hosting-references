<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
// Admin UI is always Arabic-first bilingual labels but we default the
// admin interface language to Arabic regardless of the public site toggle.
if (empty($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ar';
}
