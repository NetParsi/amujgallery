<?php
/**
 * Customer panel: order detail (HikaShop order show, opened from "سفارش‌های من" and from the order emails)
 * in the Amuj design: status steps, payment instructions while unpaid, items, delivery, totals.
 * $this->element is the full order HikaShop loaded after checking it belongs to the customer.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

amuj_panel_guard();

$order = $this->element;
if (empty($order->order_id)) {
    echo '<p class="az-muted">سفارش پیدا نشد.</p>';
    return;
}
Factory::getDocument()->setTitle('سفارش ' . $order->order_number);

$db = Factory::getDbo();
[$label, $mod, $step] = amuj_order_state($order->order_status);
$steps = amuj_order_steps();

// Lines with photo / colour / size
$lines = [];
foreach ((array) $order->products as $op) {
    if (empty($op->order_product_option_parent_id) && (int) $op->order_product_quantity > 0) {
        $lines[] = $op;
    }
}
$parents = [];
if ($lines) {
    foreach ($db->setQuery('SELECT product_id, IF(product_parent_id > 0, product_parent_id, product_id) AS main FROM ' . hikashop_table('product')
        . ' WHERE product_id IN (' . implode(',', array_map(function ($l) { return (int) $l->product_id; }, $lines)) . ')')->loadObjectList() as $r) {
        $parents[(int) $r->product_id] = (int) $r->main;
    }
}
$mains = amuj_hika_products(array_values(array_unique($parents)));

// Totals
$subtotal = 0;
foreach ($lines as $op) {
    $subtotal += ((float) $op->order_product_price + (float) $op->order_product_tax) * (int) $op->order_product_quantity;
}
$wallet = 0;
foreach ((array) ($order->additional ?? []) as $add) {
    $p = is_string($add->order_product_params ?? null) ? json_decode($add->order_product_params) : ($add->order_product_params ?? null);
    if (!empty($p->additional_key) && $p->additional_key === 'club') {
        $wallet += abs((float) $add->order_product_price);
    }
}
$total = (float) $order->order_full_price;

// Delivery and payment
$address = $order->shipping_address ?? $order->billing_address ?? null;
$province = '';
if ($address && !empty($address->address_state)) {
    $state = is_object($address->address_state) ? $address->address_state->zone_namekey : $address->address_state;
    $province = (string) $db->setQuery('SELECT zone_name FROM ' . hikashop_table('zone') . ' WHERE zone_namekey = ' . $db->quote($state))->loadResult();
}
$shipName = '';
if (!empty($order->order_shipping_id)) {
    $ids = array_filter(array_map('intval', preg_split('/[,;]/', preg_replace('/@\d+/', '', (string) $order->order_shipping_id))));
    if ($ids) {
        $shipName = implode('، ', $db->setQuery('SELECT shipping_name FROM ' . hikashop_table('shipping') . ' WHERE shipping_id IN (' . implode(',', $ids) . ')')->loadColumn());
    }
}
$payment = !empty($order->order_payment_id) ? hikashop_get('class.payment')->get($order->order_payment_id) : null;
$payName = $payment->payment_name ?? ($wallet > 0 && $total <= 0 ? 'کیف پول' : '');

// Unpaid: bank-transfer instructions, or a "pay now" link for an online gateway
$unpaid  = in_array($order->order_status, explode(',', hikashop_config()->get('order_unpaid_statuses', 'created')), true) && $total > 0;
$payInfo = $unpaid && $order->order_payment_method === 'banktransfer' ? (string) ($payment->payment_params->information ?? '') : '';
$payUrl  = $unpaid && !in_array($order->order_payment_method, ['banktransfer', 'club', ''], true)
    ? hikashop_completeLink('order&task=pay&order_id=' . (int) $order->order_id . $this->url_itemid) : '';

amuj_account_open('order');
?>
<a class="az-back" href="<?php echo amuj_url('orders'); ?>">→ بازگشت به سفارش‌ها</a>

<div class="az-order-head">
    <div>
        <h1 class="az-account-title" dir="ltr"><?php echo amuj_fa($order->order_number); ?></h1>
        <span class="az-muted">ثبت شده در <?php echo amuj_jdate($order->order_created, true); ?></span>
    </div>
    <span class="az-badge az-badge--<?php echo $mod; ?>"><?php echo $label; ?></span>
</div>

<?php if ($step >= 0) : ?>
    <ol class="az-track">
        <?php foreach ($steps as $i => $s) : ?>
            <li class="<?php echo $i < $step ? 'is-done' : ($i === $step ? 'is-current' : ''); ?>" style="--i: <?php echo $i; ?>"><span><?php echo $s; ?></span></li>
        <?php endforeach; ?>
    </ol>
<?php else : ?>
    <div class="az-thanks-note">این سفارش لغو شده است.<?php echo $wallet > 0 ? ' اگر بخشی از آن از کیف پول پرداخت شده بود، با تأیید فروشگاه به کیف پول شما بازمی‌گردد.' : ''; ?></div>
<?php endif; ?>

<?php if ($payInfo) : ?>
    <div class="az-thanks-pay az-order-pay">
        <div class="az-thanks-pay-amount"><span>مبلغ قابل واریز</span><strong><?php echo amuj_price($total); ?></strong></div>
        <div class="az-thanks-pay-info"><?php echo $payInfo; ?></div>
        <p class="az-small az-muted">شماره سفارش (<?php echo amuj_fa($order->order_number); ?>) را در توضیحات واریز بنویسید. سفارش پس از تأیید واریز ارسال می‌شود.</p>
    </div>
<?php elseif ($payUrl) : ?>
    <div class="az-thanks-pay az-order-pay">
        <div class="az-thanks-pay-amount"><span>این سفارش هنوز پرداخت نشده است</span><strong><?php echo amuj_price($total); ?></strong></div>
        <a class="az-btn" href="<?php echo $payUrl; ?>">پرداخت سفارش</a>
    </div>
<?php endif; ?>

<div class="az-order-detail">
    <section>
        <h2 class="az-dash-h">اقلام سفارش</h2>
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
                <?php if ($p) : ?><a href="<?php echo $p['url']; ?>"><img src="<?php echo amuj_img($img, 200); ?>" alt=""></a><?php endif; ?>
                <div>
                    <div><?php echo $this->escape($p['name'] ?? strip_tags($op->order_product_name)); ?></div>
                    <div class="az-muted"><?php echo implode(' | ', array_filter([$color, $size ? 'سایز ' . $size : '', 'تعداد ' . amuj_fa((int) $op->order_product_quantity)])); ?></div>
                    <div><?php echo amuj_price($lineTotal); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section>
        <h2 class="az-dash-h">ارسال و پرداخت</h2>
        <dl class="az-dl">
            <?php if ($address) : ?>
                <dt>تحویل‌گیرنده</dt><dd><?php echo $this->escape(trim($address->address_firstname . ' ' . $address->address_lastname)); ?><?php if ($address->address_telephone) : ?> — <span dir="ltr"><?php echo amuj_fa($this->escape($address->address_telephone)); ?></span><?php endif; ?></dd>
                <dt>آدرس</dt><dd><?php echo $this->escape(amuj_fa(implode('، ', array_unique(array_filter([$province, $address->address_city, $address->address_street]))))); ?><?php if ($address->address_post_code) : ?><br><span class="az-muted">کد پستی <?php echo amuj_fa($this->escape($address->address_post_code)); ?></span><?php endif; ?></dd>
            <?php endif; ?>
            <?php if ($shipName) : ?><dt>روش ارسال</dt><dd><?php echo $this->escape($shipName); ?></dd><?php endif; ?>
            <?php if ($payName) : ?><dt>روش پرداخت</dt><dd><?php echo $this->escape($payName); ?></dd><?php endif; ?>
        </dl>
        <div class="az-order-sum">
            <div class="az-summary-row"><span>جمع کالاها</span><span><?php echo amuj_price($subtotal); ?></span></div>
            <?php if ((float) $order->order_discount_price > 0) : ?>
                <div class="az-summary-row az-co-minus"><span>تخفیف<?php echo $order->order_discount_code ? ' <small class="az-muted">(' . $this->escape($order->order_discount_code) . ')</small>' : ''; ?></span><span>− <?php echo amuj_price((float) $order->order_discount_price); ?></span></div>
            <?php endif; ?>
            <div class="az-summary-row"><span>هزینه ارسال</span><span><?php echo (float) $order->order_shipping_price > 0 ? amuj_price((float) $order->order_shipping_price) : 'رایگان'; ?></span></div>
            <?php if ($wallet > 0) : ?>
                <div class="az-summary-row az-co-minus"><span>پرداخت از کیف پول</span><span>− <?php echo amuj_price($wallet); ?></span></div>
            <?php endif; ?>
            <div class="az-summary-row is-total"><span>مبلغ قابل پرداخت</span><span><?php echo $total > 0 ? amuj_price($total) : 'رایگان'; ?></span></div>
        </div>
    </section>
</div>

<div class="az-order-actions">
    <a class="az-btn az-btn--ghost" href="#" onclick="window.print(); return false;">چاپ سفارش</a>
    <?php if ($step === 4) : ?><a class="az-btn az-btn--ghost" href="<?php echo amuj_url('policy', ['doc' => 'returns']); ?>">درخواست مرجوعی</a><?php endif; ?>
    <a class="az-btn az-btn--ghost" href="<?php echo amuj_url('contact'); ?>">پشتیبانی سفارش</a>
</div>
<?php amuj_account_close(); ?>
