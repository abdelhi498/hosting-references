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
                                WHERE c.id = ? AND c.is_active = 1 AND co.is_active = 1
                                AND (c.expires_at IS NULL OR c.expires_at >= CURDATE())");
        $stmt->execute([$couponId]);
        $coupon = $stmt->fetch() ?: null;
    }

    $lang = current_lang();
    $title = trim($s['popup_title_' . $lang] ?? '') ?: ($coupon ? field($coupon, 'title') : '');
    $desc  = trim($s['popup_desc_' . $lang] ?? '') ?: ($coupon ? field($coupon, 'description') : '');
    $cta   = trim($s['popup_cta_' . $lang] ?? '') ?: t('coupons.use_now');
    $badge = trim($s['popup_badge_text'] ?? '') ?: ($coupon['discount_text'] ?? '');
    $urgency = trim($s['popup_urgency_' . $lang] ?? '');

    if ($coupon) {
        $link = 'go.php?company=' . urlencode($coupon['company_slug']) . '&coupon=' . (int)$coupon['id'];
    } else {
        $link = trim($s['popup_custom_link'] ?? '');
    }

    if ($title === '' || $link === '') {
        return '';
    }

    $template = in_array($s['popup_template'] ?? '', ['center', 'corner', 'banner'], true)
        ? $s['popup_template'] : 'center';
    $delay = max(0, (int)($s['popup_delay_seconds'] ?? 4));
    $frequency = in_array($s['popup_frequency'] ?? '', ['every_visit', 'once_session', 'once_day'], true)
        ? $s['popup_frequency'] : 'once_session';

    // Real social proof only — actual click count on this coupon, never fabricated.
    $usesCount = $coupon ? (int)($coupon['clicks'] ?? 0) : 0;

    $daysLeft = null;
    if ($coupon && !empty($coupon['expires_at'])) {
        $daysLeft = (int)ceil((strtotime($coupon['expires_at']) - strtotime(date('Y-m-d'))) / 86400);
    }

    ob_start();
    ?>
    <div id="promo-popup" class="promo-popup promo-popup--<?= e($template) ?><?= $preview ? ' is-preview' : '' ?>" data-delay="<?= $delay ?>" data-frequency="<?= e($frequency) ?>" data-template="<?= e($template) ?>"<?= $preview ? '' : ' hidden' ?>>
      <div class="promo-popup-backdrop" data-popup-close></div>
      <div class="promo-popup-card" role="dialog" aria-modal="true" aria-labelledby="promo-popup-title">
        <button type="button" class="promo-popup-close" data-popup-close aria-label="<?= is_rtl() ? 'إغلاق' : 'Close' ?>">&times;</button>

        <div class="promo-popup-top">
          <?php if ($badge): ?><span class="promo-popup-badge"><?= e($badge) ?></span><?php endif; ?>
          <?php if ($urgency): ?><span class="promo-popup-urgency"><?= e($urgency) ?></span><?php endif; ?>
        </div>

        <h3 id="promo-popup-title"><?= e($title) ?></h3>
        <?php if ($desc): ?><p class="promo-popup-desc"><?= e($desc) ?></p><?php endif; ?>

        <?php if ($daysLeft !== null): ?>
          <div class="promo-popup-countdown">
            <?php if ($daysLeft <= 0): ?>
              <span>&#9203; <?= t('popup.ends_today') ?></span>
            <?php else: ?>
              <span>&#9203; <?= t('popup.ends_prefix') ?> <?= (int)$daysLeft ?> <?= t('popup.days_suffix') ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <div class="promo-popup-actions">
          <?php if ($coupon): ?>
            <button type="button" class="coupon-cta promo-popup-coupon"
                    data-copy="<?= e($coupon['code']) ?>"
                    data-goto="<?= e($link) ?>"
                    data-copied-label="<?= e(t('coupons.copied')) ?>"
                    data-popup-close>
              <span class="code"><?= e($coupon['code']) ?></span>
              <span class="cta-label"><?= e($cta) ?></span>
            </button>
          <?php else: ?>
            <a href="<?= e($link) ?>" class="btn btn-primary btn-block" target="_blank" rel="nofollow noopener" data-popup-close><?= e($cta) ?></a>
          <?php endif; ?>
        </div>

        <?php if ($usesCount > 0): ?>
          <p class="promo-popup-social-proof">&#128293; <?= t('popup.uses_prefix') ?> <?= number_format($usesCount) ?> <?= t('popup.uses_suffix') ?></p>
        <?php endif; ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
