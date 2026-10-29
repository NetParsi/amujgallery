<?php
/**
 * Amuj Gallery email frame for every HikaShop email (HikaShop > Configuration > Emails: default template "amuj").
 * Email-safe markup: tables, inline styles, system fonts (web fonts do not load in most mail apps).
 * The body of the email is {VAR:TPL_CONTENT}.
 */
defined('_JEXEC') or die('Restricted access');

$amujSite = rtrim(HIKASHOP_LIVE, '/') . '/';
$amujContact = ['phone' => '', 'tel' => '', 'instagram' => '', 'instagram_handle' => '', 'address' => ''];
if (!function_exists('amuj_contact') && is_file(JPATH_ROOT . '/templates/netparsi/static/_helpers.php')) {
    defined('AMUJ_BASE') || define('AMUJ_BASE', $amujSite);
    defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
    require_once JPATH_ROOT . '/templates/netparsi/static/_helpers.php';
}
if (function_exists('amuj_contact')) {
    $amujContact = array_merge($amujContact, amuj_contact());
}
$font = "Tahoma, 'Segoe UI', Arial, sans-serif";
?><style type="text/css">
body.hikashop_mail { margin: 0; padding: 0; background-color: #f3f3f0; }
.ReadMsgBody, .ExternalClass { width: 100%; }
div, p, a, li, td { -webkit-text-size-adjust: none; }
a { color: #000000; text-decoration: none; }
@media (max-width: 620px) {
	.amuj-wrap { width: 100% !important; }
	.amuj-pad { padding-left: 18px !important; padding-right: 18px !important; }
	.amuj-col { display: block !important; width: 100% !important; }
}
{VAR:TPL_CSS}
</style>
<div dir="rtl" style="margin:0;padding:24px 0;background-color:#f3f3f0;font-family:<?php echo $font; ?>;direction:rtl;">
	<table class="amuj-wrap" role="presentation" width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="width:600px;margin:0 auto;background-color:#ffffff;border-collapse:collapse;">
		<tr>
			<td style="padding:28px 0 18px;text-align:center;border-bottom:3px solid #b2cc9d;">
				<a href="<?php echo $amujSite; ?>"><img src="<?php echo $amujSite; ?>images/amuj-logo-mail.png" width="96" alt="گالری آموژ" style="display:inline-block;width:96px;height:auto;border:0;"></a>
			</td>
		</tr>
		<tr>
			<td class="amuj-pad" dir="rtl" style="padding:30px 36px 34px;text-align:right;font-family:<?php echo $font; ?>;font-size:14px;line-height:26px;color:#1a1a1a;">
{VAR:TPL_CONTENT}
			</td>
		</tr>
		<tr>
			<td class="amuj-pad" dir="rtl" style="padding:22px 36px;background-color:#eef3e9;text-align:right;font-family:<?php echo $font; ?>;font-size:12px;line-height:22px;color:#5f6b57;">
				<strong style="color:#1a1a1a;font-size:13px;">گالری آموژ</strong><br>
				<?php if ($amujContact['phone']) : ?>تلفن و واتساپ: <a href="tel:<?php echo $amujContact['tel']; ?>" style="color:#1a1a1a;" dir="ltr"><?php echo $amujContact['phone']; ?></a><?php endif; ?>
				<?php if ($amujContact['instagram']) : ?> &nbsp;|&nbsp; اینستاگرام: <a href="<?php echo $amujContact['instagram']; ?>" style="color:#1a1a1a;" dir="ltr"><?php echo $amujContact['instagram_handle']; ?></a><?php endif; ?>
				<?php if ($amujContact['address']) : ?><br><?php echo $amujContact['address']; ?><?php endif; ?>
			</td>
		</tr>
	</table>
	<p style="margin:14px 0 0;text-align:center;font-family:<?php echo $font; ?>;font-size:11px;color:#9a9a9a;">این ایمیل به‌صورت خودکار از <a href="<?php echo $amujSite; ?>" style="color:#9a9a9a;">فروشگاه گالری آموژ</a> ارسال شده است.</p>
</div>
