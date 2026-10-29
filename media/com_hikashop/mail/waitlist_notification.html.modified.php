<?php
/**
 * Amuj Gallery: "back in stock" email (sent by the plugin hikashop/amujrestock with amuj_label / amuj_url / amuj_main).
 */
defined('_JEXEC') or die('Restricted access');

$amujSite = rtrim(HIKASHOP_LIVE, '/') . '/';
defined('AMUJ_BASE') || define('AMUJ_BASE', $amujSite);
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_ROOT . '/templates/netparsi/static/_helpers.php';

$font  = "Tahoma, 'Segoe UI', Arial, sans-serif";
$cell  = 'font-family:' . $font . ';font-size:14px;line-height:26px;color:#1a1a1a;';
$label = (string) ($data->amuj_label ?? ($data->product_name ?? ''));
$url   = (string) ($data->amuj_url ?? $amujSite);
$p     = !empty($data->amuj_main) ? (amuj_hika_products([(int) $data->amuj_main])[(int) $data->amuj_main] ?? null) : null;
// Photo of the colour that is back in stock
$img = $p['img'] ?? '';
foreach ($p['variants'] ?? [] as $key => $v) {
    if ((int) $v[0] === (int) ($data->product_id ?? 0)) {
        $img = $p['color_images'][(int) explode('|', $key)[0]] ?? $img;
        break;
    }
}
?>
<h1 style="margin:0 0 6px;font-family:<?php echo $font; ?>;font-size:20px;line-height:30px;font-weight:normal;color:#000000;">موجود شد</h1>
<p style="margin:0 0 4px;<?php echo $cell; ?>"><?php echo htmlspecialchars(trim((string) ($data->amuj_name ?? $data->name ?? '')) ?: 'مشتری گرامی'); ?> عزیز،</p>
<p style="margin:0 0 22px;<?php echo $cell; ?>">کالایی که منتظرش بودید دوباره موجود شد. موجودی محدود است؛ اگر هنوز آن را می‌خواهید، زودتر سفارش دهید.</p>

<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f5f5f2;margin-bottom:24px;">
	<tr>
		<?php if ($p) : ?>
			<td width="96" style="padding:14px 16px 14px 0;vertical-align:top;">
				<img src="<?php echo amuj_img($img, 300); ?>" width="80" alt="" style="display:block;width:80px;height:107px;object-fit:cover;border:0;">
			</td>
		<?php endif; ?>
		<td style="padding:14px 16px;vertical-align:middle;<?php echo $cell; ?>">
			<strong><?php echo htmlspecialchars(amuj_fa($label)); ?></strong>
			<?php if ($p) : ?><br><span style="color:#7a7a7a;font-size:13px;"><?php echo amuj_price($p['price']); ?></span><?php endif; ?>
		</td>
	</tr>
</table>

<table role="presentation" border="0" cellspacing="0" cellpadding="0">
	<tr><td style="background-color:#000000;">
		<a href="<?php echo htmlspecialchars($url); ?>" style="display:inline-block;padding:12px 30px;font-family:<?php echo $font; ?>;font-size:14px;color:#ffffff;text-decoration:none;">مشاهده و خرید</a>
	</td></tr>
</table>
