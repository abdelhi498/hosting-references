<?php
/**
 * Renders the promotional popup markup from the settings table.
 * Shared between the public footer (real popup, gated by JS timing)
 * and the admin popup page (always-visible inline preview).
 *
 * Returns '' when the popup has nothing meaningful to show (disabled,
 * or no title / no destination link), so callers can just echo it.
 */
function render_promo_popup(PDO $pdo, bool $preview = false): string
{
    $s = get_settings($pdo);
    if (!$preview && empty($s['popup_enabled'])) {
        return '';
    }

    $coupon = null;
    $couponId = (int)($s['popup_coupon_id'] ?? 0);
    if ($couponId) {
        $stmt = $pdo->prepare("SELECT c.*, co.name AS company_name, co.slug AS company_slug
                                FROM coupons c JOIN companies co ON co.id = c.company_id
                                WHERE c.id = ? AND c.is_active = 1 AND co.is_active = 1");
        $stmt->execute([$couponId]);
        $coupon = $stmt->fetch() ?: null;
    }

    $lang = current_lang();
    $title = trim($s['popup_title_' . $lang] ?? '') ?: ($coupon ? field($coupon, 'title') : '');
    $desc  = trim($s['popup_desc_' . $lang] ?? '') ?: ($coupon ? field($coupon, 'description') : '');
    $cta   = trim($s['popup_cta_' . $lang] ?? '') ?: t('coupons.use_now');
    $badge = trim($s['popup_badge_text'] ?? '') ?: ($coupon['discount_text'] ?? '');

    if ($coupon) {
        $link = 'go.php?company=' . urlencode($coupon['company_slug']) . '&coupon=' . (int)$coupon['id'];
    } else {
        $link = trim($s['popup_custom_link'] ?? '');
    }

    if ($title === '' || $link === '') {
        return '';
    }

    $delay = max(0, (int)($s['popup_delay_seconds'] ?? 4));
    $frequency = in_array($s['popup_frequency'] ?? '', ['every_visit', 'once_session', 'once_day'], true)
        ? $s['popup_frequency'] : 'once_session';

    ob_start();
    ?>
    <div id="promo-popup" class="promo-popup<?= $preview ? ' is-preview' : '' ?>" data-delay="<?= $delay ?>" data-frequency="<?= e($frequency) ?>"<?= $preview ? '' : ' hidden' ?>>
      <div class="promo-popup-backdrop" data-popup-close></div>
      <div class="promo-popup-card" role="dialog" aria-modal="true" aria-labelledby="promo-popup-title">
        <button type="button" class="promo-popup-close" data-popup-close aria-label="<?= is_rtl() ? 'إغلاق' : 'Close' ?>">&times;</button>
        <?php if ($badge): ?><span class="promo-popup-badge"><?= e($badge) ?></span><?php endif; ?>
        <h3 id="promo-popup-title"><?= e($title) ?></h3>
        <?php if ($desc): ?><p><?= e($desc) ?></p><?php endif; ?>
        <a href="<?= e($link) ?>" class="btn btn-primary btn-block" target="_blank" rel="nofollow noopener" data-popup-close><?= e($cta) ?></a>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
