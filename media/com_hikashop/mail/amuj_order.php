<?php
/**
 * Amuj Gallery: shared body of the order emails (customer and shop owner). Included by the
 * *.html.modified.php files of this folder after HikaShop's own preload, so $data->cart (full order),
 * $vars (URL, PAYMENT, SHIPPING) and $mail are available. The caller sets $amujMail:
 *   title    heading of the email
 *   intro    first paragraph (HTML)
 *   admin    true for the shop owner's copy (customer details + admin link instead of the greeting)
 *   pay_info true to show the bank-transfer instructions while the order is unpaid
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

// The same files are included for the plain-text part of the email ($amujText set by *.text.modified.php)
if (!empty($amujText)) {
    require __DIR__ . '/amuj_order_text.php';
    return;
}

$amujSite = rtrim(HIKASHOP_LIVE, '/') . '/';
defined('AMUJ_BASE') || define('AMUJ_BASE', $amujSite);
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_ROOT . '/templates/netparsi/static/_helpers.php';

$order    = $data->cart;
$db       = Factory::getDbo();
$font     = "Tahoma, 'Segoe UI', Arial, sans-serif";
$isAdmin  = !empty($amujMail['admin']);
$currency = (int) $order->order_currency_id;

// Order date in the Persian calendar
$created = (int) ($order->order_created ?? time());
$date = '';
if (class_exists('IntlDateFormatter')) {
    $fmt = new IntlDateFormatter('fa_IR@calendar=persian', IntlDateFormatter::LONG, IntlDateFormatter::SHORT, 'Asia/Tehran', IntlDateFormatter::TRADITIONAL, 'd MMMM y، ساعت HH:mm');
    $date = (string) $fmt->format($created);
}
$date = $date ?: amuj_jalali_month($created);

// Lines with photo, colour and size
$lines = [];
foreach ((array) $order->products as $op) {
    if (!empty($op->order_product_option_parent_id) || (int) $op->order_product_quantity <= 0) {
        continue;
    }
    $lines[] = $op;
}
$parents = [];
if ($lines) {
    $rows = $db->setQuery('SELECT product_id, IF(product_parent_id > 0, product_parent_id, product_id) AS main FROM ' . hikashop_table('product')
        . ' WHERE product_id IN (' . implode(',', array_map(function ($l) { return (int) $l->product_id; }, $lines)) . ')')->loadObjectList();
    foreach ($rows as $r) {
        $parents[(int) $r->product_id] = (int) $r->main;
    }
}
$mains = amuj_hika_products(array_values(array_unique($parents)));

// Totals
$subtotal = 0;
foreach ($lines as $op) {
    $subtotal += ((float) $op->order_product_price + (float) $op->order_product_tax) * (int) $op->order_product_quantity;
}
$discount = (float) $order->order_discount_price;
$shipping = (float) $order->order_shipping_price;
$wallet = 0;
foreach ((array) ($order->additional ?? []) as $add) {
    $p = is_string($add->order_product_params ?? null) ? json_decode($add->order_product_params) : ($add->order_product_params ?? null);
    if (!empty($p->additional_key) && $p->additional_key === 'club') {
        $wallet += abs((float) $add->order_product_price);
    }
}
$total = (float) $order->order_full_price;

// Address
$address = $order->shipping_address ?? $order->billing_address ?? null;
$addressText = '';
if ($address) {
    $state = is_object($address->address_state ?? null) ? $address->address_state->zone_namekey : ($address->address_state ?? '');
    $province = $state ? (string) $db->setQuery('SELECT zone_name FROM ' . hikashop_table('zone') . ' WHERE zone_namekey = ' . $db->quote($state))->loadResult() : '';
    $addressText = htmlspecialchars(trim($address->address_firstname . ' ' . $address->address_lastname)) . '<br>'
        . htmlspecialchars(implode('، ', array_unique(array_filter([$province, $address->address_city, $address->address_street]))))
        . ($address->address_post_code ? '<br>کد پستی ' . htmlspecialchars($address->address_post_code) : '')
        . ($address->address_telephone ? ' | تلفن ' . htmlspecialchars($address->address_telephone) : '');
    $addressText = amuj_fa($addressText);
}

// Payment instructions while a bank-transfer order is unpaid
$payInfo = '';
$unpaid = in_array($order->order_status, explode(',', hikashop_config()->get('order_unpaid_statuses', 'created')), true);
if (!empty($amujMail['pay_info']) && $unpaid && $order->order_payment_method === 'banktransfer' && $total > 0) {
    $payment = hikashop_get('class.payment')->get($order->order_payment_id);
    $payInfo = (string) ($payment->payment_params->information ?? '');
}

$statusName = hikashop_orderStatus($order->order_status);
$orderUrl = $isAdmin
    ? $amujSite . 'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&cid[]=' . (int) $order->order_id
    : (string) ($vars['URL'] ?? $amujSite);

$cell = 'font-family:' . $font . ';font-size:13px;line-height:22px;color:#1a1a1a;';
$muted = 'color:#7a7a7a;font-size:12px;';
$row = function ($label, $value, $strong = false, $green = false) use ($cell) {
    $style = $cell . ($strong ? 'font-size:15px;font-weight:bold;padding-top:10px;border-top:1px solid #dddddd;' : 'padding-top:4px;')
        . ($green ? 'color:#4f6b3a;' : '');
    return '<tr><td style="' . $style . 'text-align:right;">' . $label . '</td><td style="' . $style . 'text-align:left;white-space:nowrap;">' . $value . '</td></tr>';
};
?>
<h1 style="margin:0 0 6px;font-family:<?php echo $font; ?>;font-size:20px;line-height:30px;font-weight:normal;color:#000000;"><?php echo $amujMail['title']; ?></h1>
<?php if (!$isAdmin) :
    $name = trim((string) ($order->customer->name ?? '')) ?: trim(($address->address_firstname ?? '') . ' ' . ($address->address_lastname ?? ''));
    ?>
    <p style="margin:0 0 4px;<?php echo $cell; ?>"><?php echo htmlspecialchars($name ?: 'مشتری گرامی'); ?> عزیز،</p>
<?php endif; ?>
<p style="margin:0 0 22px;<?php echo $cell; ?>font-size:14px;line-height:26px;"><?php echo $amujMail['intro']; ?></p>

<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f5f5f2;margin-bottom:22px;">
	<tr>
		<td class="amuj-col" style="padding:14px 16px;<?php echo $cell; ?>"><span style="<?php echo $muted; ?>">شماره سفارش</span><br><strong dir="ltr"><?php echo amuj_fa($order->order_number); ?></strong></td>
		<td class="amuj-col" style="padding:14px 16px;<?php echo $cell; ?>"><span style="<?php echo $muted; ?>">تاریخ</span><br><?php echo $date; ?></td>
		<td class="amuj-col" style="padding:14px 16px;<?php echo $cell; ?>"><span style="<?php echo $muted; ?>">وضعیت</span><br><?php echo htmlspecialchars($statusName); ?></td>
	</tr>
</table>

<?php if ($isAdmin) :
    $c = $order->customer ?? null;
    ?>
    <p style="margin:0 0 18px;<?php echo $cell; ?>">
        <span style="<?php echo $muted; ?>">مشتری</span><br>
        <?php echo htmlspecialchars((string) ($c->name ?? '')); ?>
        <?php if (!empty($c->username)) : ?> | <span dir="ltr"><?php echo amuj_fa(htmlspecialchars($c->username)); ?></span><?php endif; ?>
        <?php if (!empty($c->user_email)) : ?> | <span dir="ltr"><?php echo htmlspecialchars($c->user_email); ?></span><?php endif; ?>
    </p>
<?php endif; ?>

<?php if ($payInfo) : ?>
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#eef3e9;margin-bottom:22px;">
        <tr><td style="padding:16px 18px;<?php echo $cell; ?>">
            <span style="<?php echo $muted; ?>">مبلغ قابل واریز</span><br>
            <strong style="font-size:17px;"><?php echo amuj_price($total); ?></strong>
            <div style="margin-top:10px;<?php echo $cell; ?>"><?php echo $payInfo; ?></div>
            <div style="margin-top:6px;<?php echo $muted; ?>">شماره سفارش (<?php echo amuj_fa($order->order_number); ?>) را در توضیحات واریز بنویسید.</div>
        </td></tr>
    </table>
<?php endif; ?>

<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:8px;">
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
        <tr>
            <td width="64" style="padding:0 0 14px 14px;vertical-align:top;">
                <?php if ($img) : ?><img src="<?php echo amuj_img($img, 200); ?>" width="56" alt="" style="display:block;width:56px;height:84px;object-fit:cover;border:0;"><?php endif; ?>
            </td>
            <td style="padding:0 0 14px;vertical-align:top;<?php echo $cell; ?>">
                <?php echo htmlspecialchars($p['name'] ?? strip_tags($op->order_product_name)); ?><br>
                <span style="<?php echo $muted; ?>"><?php echo implode(' | ', array_filter([$color, $size ? 'سایز ' . $size : '', 'تعداد ' . amuj_fa((int) $op->order_product_quantity)])); ?></span>
            </td>
            <td style="padding:0 0 14px;vertical-align:top;text-align:left;white-space:nowrap;<?php echo $cell; ?>"><?php echo amuj_price($lineTotal); ?></td>
        </tr>
	<?php endforeach; ?>
</table>

<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:24px;border-top:1px solid #eeeeee;padding-top:8px;">
	<?php
    echo $row('جمع محصولات', amuj_price($subtotal));
    if ($discount > 0) {
        echo $row('تخفیف' . (!empty($order->order_discount_code) ? ' <span style="' . $muted . '">(' . htmlspecialchars($order->order_discount_code) . ')</span>' : ''), '− ' . amuj_price($discount), false, true);
    }
    echo $row('هزینه ارسال', $shipping > 0 ? amuj_price($shipping) : 'رایگان');
    if ($wallet > 0) {
        echo $row('پرداخت از کیف پول', '− ' . amuj_price($wallet), false, true);
    }
    echo $row('مبلغ قابل پرداخت', $total > 0 ? amuj_price($total) : 'رایگان', true);
    ?>
</table>

<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:26px;">
	<tr>
		<?php if ($addressText) : ?>
			<td class="amuj-col" width="50%" style="vertical-align:top;padding:0 0 12px 12px;<?php echo $cell; ?>"><span style="<?php echo $muted; ?>">ارسال به</span><br><?php echo $addressText; ?></td>
		<?php endif; ?>
		<td class="amuj-col" style="vertical-align:top;padding:0 0 12px;<?php echo $cell; ?>">
			<?php if (!empty($vars['SHIPPING'])) : ?><span style="<?php echo $muted; ?>">روش ارسال</span><br><?php echo strip_tags($vars['SHIPPING']); ?><br><?php endif; ?>
			<?php if (!empty($vars['PAYMENT'])) : ?><span style="<?php echo $muted; ?>">روش پرداخت</span><br><?php echo htmlspecialchars(strip_tags($vars['PAYMENT'])); ?><?php elseif ($wallet > 0 && $total <= 0) : ?><span style="<?php echo $muted; ?>">روش پرداخت</span><br>کیف پول<?php endif; ?>
		</td>
	</tr>
</table>

<table role="presentation" border="0" cellspacing="0" cellpadding="0">
	<tr><td style="background-color:#000000;">
		<a href="<?php echo htmlspecialchars($orderUrl); ?>" style="display:inline-block;padding:12px 30px;font-family:<?php echo $font; ?>;font-size:14px;color:#ffffff;text-decoration:none;"><?php echo $isAdmin ? 'مشاهده سفارش در مدیریت' : 'مشاهده سفارش'; ?></a>
	</td></tr>
</table>
<?php if (!$isAdmin) : ?>
	<p style="margin:22px 0 0;<?php echo $muted; ?>line-height:22px;">سؤالی درباره سفارش دارید؟ کافی است به همین ایمیل پاسخ دهید یا با ما تماس بگیرید.</p>
<?php endif; ?>
