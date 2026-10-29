<?php
/**
 * Order result card shared by checkout/end.php (right after ordering) and checkout/after_end.php
 * (return from a payment gateway). Expects $order (HikaShop full order), $url (order page) and
 * optionally $paymentInfo (HTML of the bank-transfer instructions).
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

Factory::getDocument()->setTitle('نتیجه سفارش');

$db        = Factory::getDbo();
$config    = hikashop_config();
$confirmed = $config->get('order_confirmed_status', 'confirmed');
$status    = (string) ($order->order_status ?? '');
$payType   = (string) ($order->order_payment_method ?? '');
$isPaid    = in_array($status, [$confirmed, 'shipped'], true);
$isFailed  = in_array($status, ['cancelled', 'refunded'], true);
$isWaiting = !$isPaid && !$isFailed; // bank transfer awaiting the store's check, or a payment not confirmed yet
$total     = (float) ($order->order_full_price ?? 0);

// Part of the order paid from the club wallet (the "club" additional line)
$wallet = 0;
foreach ((array) ($order->additional ?? []) as $add) {
    $params = is_string($add->order_product_params ?? null) ? json_decode($add->order_product_params) : ($add->order_product_params ?? null);
    if (!empty($params->additional_key) && $params->additional_key === 'club') {
        $wallet += abs((float) $add->order_product_price);
    }
}

// Lines with photo / colour / size from the product data
$lines = [];
foreach ((array) ($order->products ?? []) as $op) {
    if (!empty($op->order_product_option_parent_id) || (int) $op->order_product_quantity <= 0) {
        continue;
    }
    $lines[] = $op;
}
$parents = [];
if ($lines) {
    $parents = $db->setQuery('SELECT product_id, IF(product_parent_id > 0, product_parent_id, product_id) FROM ' . hikashop_table('product')
        . ' WHERE product_id IN (' . implode(',', array_map(function ($l) { return (int) $l->product_id; }, $lines)) . ')')->loadAssocList('product_id');
    $parents = array_map(function ($r) { return (int) end($r); }, $parents);
}
$mains = amuj_hika_products(array_values(array_unique($parents)));

$address = $order->shipping_address ?? $order->billing_address ?? null;
$shipName = '';
if (!empty($order->order_shipping_id)) {
    $ids = array_map('intval', explode(',', preg_replace('/@\d+/', '', (string) $order->order_shipping_id)));
    $shipName = implode('، ', $db->setQuery('SELECT shipping_name FROM ' . hikashop_table('shipping') . ' WHERE shipping_id IN (' . implode(',', $ids) . ')')->loadColumn());
}
$province = '';
if ($address && !empty($address->address_state)) {
    $province = (string) $db->setQuery('SELECT zone_name FROM ' . hikashop_table('zone') . ' WHERE zone_namekey = ' . $db->quote(is_object($address->address_state) ? $address->address_state->zone_namekey : $address->address_state))->loadResult();
}

// Customer club: cashback of the customer's level on this order (credited when the order is confirmed)
$cashback = null;
$user = Factory::getApplication()->getIdentity();
if (!$user->guest && !$isFailed) {
    $level = $db->setQuery('SELECT l.level_name, l.cashback_rate FROM #__club_levels l JOIN #__users u ON u.club_level_id = l.id WHERE u.id = ' . (int) $user->id)->loadObject();
    if ($level && (float) $level->cashback_rate > 0 && $total > 0) {
        $amount = round($total * $level->cashback_rate / 100);
        // Same rounding as the club plugin when it deposits the cashback (option "round_zero")
        $helper = '\Joomla\Component\Club\Administrator\Helper\ClubHelper';
        if (class_exists($helper)) {
            $amount = $helper::roundAmount($amount, (int) $helper::getParams()->get('round_zero', 3));
        }
        $cashback = [$level->level_name, $amount];
    }
}
?>
<div class="az-thanks<?php echo $isFailed ? ' is-failed' : ''; ?>">
    <div class="az-status-icon" aria-hidden="true">
        <svg viewBox="0 0 96 96">
            <circle class="az-status-ring" cx="48" cy="48" r="44"/>
            <?php if ($isFailed) : ?>
                <path class="az-status-mark" d="M34 34l28 28M62 34L34 62"/>
            <?php else : ?>
                <path class="az-status-mark" d="M28 50l13 13 27-29"/>
            <?php endif; ?>
        </svg>
    </div>

    <?php if ($isFailed) : ?>
        <h1>پرداخت ناموفق بود</h1>
        <p class="az-muted">پرداخت سفارش شما تکمیل نشد. اگر مبلغی از حساب شما کسر شده، حداکثر تا ۷۲ ساعت آینده توسط بانک بازگردانده می‌شود.</p>
    <?php elseif ($isPaid) : ?>
        <h1>سفارش شما با موفقیت ثبت شد</h1>
        <p class="az-muted">پرداخت انجام شد. جزئیات سفارش و کد رهگیری مرسوله به تلفن همراه شما پیامک می‌شود.</p>
    <?php else : ?>
        <h1>سفارش شما ثبت شد</h1>
        <p class="az-muted"><?php echo $payType === 'banktransfer'
            ? 'برای نهایی شدن سفارش، مبلغ را به کارت زیر واریز کنید. پس از تأیید واریز، سفارش ارسال می‌شود.'
            : 'سفارش شما ثبت شد و پس از تأیید پرداخت پردازش می‌شود.'; ?></p>
    <?php endif; ?>

    <?php if (!empty($paymentInfo) && $isWaiting) : ?>
        <div class="az-thanks-pay">
            <div class="az-thanks-pay-amount"><span>مبلغ قابل واریز</span><strong><?php echo amuj_price($total); ?></strong></div>
            <div class="az-thanks-pay-info"><?php echo $paymentInfo; ?></div>
            <p class="az-small az-muted">شماره سفارش (<?php echo amuj_fa($order->order_number); ?>) را در توضیحات واریز بنویسید.</p>
        </div>
    <?php endif; ?>

    <?php if (!$isFailed) : ?>
        <div class="az-thanks-card">
            <div class="az-thanks-meta">
                <div><span>شماره سفارش</span><strong dir="ltr"><?php echo amuj_fa($order->order_number); ?></strong></div>
                <div><span>وضعیت</span><strong><?php echo $isPaid ? 'پرداخت‌شده' : 'در انتظار پرداخت'; ?></strong></div>
                <?php if ($wallet > 0 && $total <= 0) : ?>
                    <div><span>پرداخت از کیف پول</span><strong><?php echo amuj_price($wallet); ?></strong></div>
                <?php else : ?>
                    <div>
                        <span><?php echo $isPaid ? 'مبلغ پرداخت‌شده' : 'مبلغ قابل پرداخت'; ?></span><strong><?php echo amuj_price($total); ?></strong>
                        <?php if ($wallet > 0) : ?><small class="az-muted">+ <?php echo amuj_price($wallet); ?> از کیف پول</small><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="az-thanks-items">
                <?php foreach ($lines as $op) :
                    $main = $parents[(int) $op->product_id] ?? (int) $op->product_id;
                    $p = $mains[$main] ?? null;
                    $img = $p['img'] ?? '';
                    $color = $size = '';
                    if ($p) {
                        foreach ($p['variants'] as $key => $v) {
                            if ((int) $v[0] === (int) $op->product_id) {
                                [$cid, $sid] = explode('|', $key);
                                $ci = array_search((int) $cid, $p['color_ids']);
                                $si = array_search((int) $sid, $p['size_ids']);
                                $color = $ci !== false ? $p['color_names'][$ci] : '';
                                $size  = $si !== false ? $p['sizes'][$si] : '';
                                $img   = $p['color_images'][(int) $cid] ?? $img;
                                break;
                            }
                        }
                    }
                    $lineTotal = ((float) $op->order_product_price + (float) $op->order_product_tax) * (int) $op->order_product_quantity;
                    ?>
                    <div class="az-summary-item">
                        <?php if ($img) : ?><img src="<?php echo amuj_img($img, 200); ?>" alt=""><?php endif; ?>
                        <div>
                            <div><?php echo $this->escape($p['name'] ?? strip_tags($op->order_product_name)); ?></div>
                            <div class="az-muted"><?php echo implode(' | ', array_filter([$color, $size ? 'سایز ' . $size : '', 'تعداد ' . amuj_fa((int) $op->order_product_quantity)])); ?></div>
                            <div><?php echo amuj_price($lineTotal); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($address || $shipName) : ?>
                <div class="az-thanks-ship">
                    <?php if ($address) : ?>
                        <div><span>ارسال به</span><?php echo $this->escape(trim($address->address_firstname . ' ' . $address->address_lastname)
                            . ' — ' . amuj_fa(implode('، ', array_unique(array_filter([$province, $address->address_city, $address->address_street]))))); ?></div>
                    <?php endif; ?>
                    <?php if ($shipName) : ?>
                        <div><span>روش ارسال</span><?php echo $this->escape($shipName); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($cashback) : ?>
            <div class="az-thanks-club">
                <span><?php echo $isPaid ? 'به‌زودی' : 'پس از تأیید پرداخت،'; ?> <strong><?php echo amuj_price($cashback[1]); ?></strong> بازگشت خرید (سطح <?php echo $this->escape($cashback[0]); ?>) به کیف پول شما اضافه می‌شود.</span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="az-thanks-actions">
        <?php if ($isFailed) : ?>
            <a class="az-btn" href="<?php echo hikashop_completeLink('checkout'); ?>">تلاش مجدد</a>
            <a class="az-btn az-btn--ghost" href="<?php echo hikashop_completeLink('cart&task=show'); ?>">بازگشت به سبد خرید</a>
        <?php else : ?>
            <a class="az-btn" href="<?php echo amuj_order_url($order->order_id); ?>">مشاهده سفارش</a>
            <a class="az-btn az-btn--ghost" href="<?php echo Uri::root(); ?>">ادامه خرید</a>
        <?php endif; ?>
    </div>
    <p class="az-small az-muted">نیاز به کمک دارید؟ <a class="az-link" href="tel:<?php echo amuj_contact()['tel']; ?>" dir="ltr"><?php echo amuj_contact()['phone']; ?></a></p>
</div>
