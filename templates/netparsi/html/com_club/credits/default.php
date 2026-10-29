<?php
/**
 * Customer panel: wallet (menu item "credits" -> com_club credits) in the Amuj design.
 * Balance and the club credit history: deposits (cashback, gifts) with their expiry, and withdrawals for orders.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

amuj_panel_guard();
Factory::getDocument()->setTitle('کیف پول');

$user = amuj_current_user();
$db   = Factory::getDbo();
$rows = $db->setQuery('SELECT id, trans_type, currency_amount, currency_balance, state, created_on, expire_on, description, shop_order_id'
    . ' FROM #__club_credits WHERE user_id = ' . (int) $user['id'] . ' ORDER BY created_on DESC, id DESC', 0, 100)->loadObjectList();

// Credit that expires within 30 days
$soon = $db->setQuery('SELECT SUM(currency_balance) AS amount, MIN(expire_on) AS first FROM #__club_credits WHERE user_id = ' . (int) $user['id']
    . ' AND trans_type = 1 AND state = 1 AND currency_balance > 0 AND expire_on <> "" AND expire_on <= ' . $db->quote(date('Y-m-d', strtotime('+30 days'))))->loadObject();

amuj_account_open('credits');
?>
<h1 class="az-account-title">کیف پول</h1>

<div class="az-dash-stat az-dash-stat--big az-dash-stat--accent az-wallet-hero">
    <span>موجودی کیف پول</span>
    <strong><?php echo amuj_price($user['wallet']); ?></strong>
    <small class="az-muted">
        <?php if (!empty($soon->amount)) : ?>
            <?php echo amuj_price($soon->amount); ?> از این موجودی تا <?php echo amuj_jdate($soon->first); ?> اعتبار دارد.
        <?php else : ?>
            موجودی در صفحه پرداخت با گزینه «پرداخت از کیف پول» قابل استفاده است.
        <?php endif; ?>
    </small>
</div>

<section class="az-dash-section">
    <div class="az-dash-head"><h2>تاریخچه تراکنش‌ها</h2></div>
    <?php if ($rows) : ?>
        <div class="az-tx-list">
            <?php foreach ($rows as $r) :
                $in = (int) $r->trans_type === 1;
                $title = trim((string) $r->description) ?: ($in ? 'افزایش اعتبار' : 'کسر اعتبار');
                $meta = [amuj_jdate($r->created_on)];
                if ($in && (int) $r->state === 1 && $r->expire_on) {
                    $meta[] = 'اعتبار تا ' . amuj_jdate($r->expire_on);
                } elseif ($in && (int) $r->state === 0) {
                    $meta[] = 'منقضی شده';
                } elseif ($in && (int) $r->state === 2) {
                    $meta[] = 'استفاده شده';
                }
                ?>
                <div class="az-tx">
                    <span class="az-tx-icon az-tx-icon--<?php echo $in ? 'in' : 'out'; ?>" aria-hidden="true"><?php echo $in ? '+' : '−'; ?></span>
                    <div class="az-tx-info">
                        <strong><?php echo $this->escape(amuj_fa($title)); ?></strong>
                        <span class="az-muted"><?php echo implode(' · ', $meta); ?></span>
                    </div>
                    <strong class="az-tx-amount <?php echo $in ? 'is-in' : 'is-out'; ?>"><?php echo ($in ? '+ ' : '− ') . amuj_price(abs((float) $r->currency_amount)); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p class="az-muted">هنوز تراکنشی ندارید. با هر خرید، بخشی از مبلغ به‌عنوان بازگشت خرید به کیف پول شما اضافه می‌شود.</p>
    <?php endif; ?>
</section>
<?php amuj_account_close(); ?>
