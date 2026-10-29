<?php
/**
 * Page right after the order is created. A gateway plugin returns a page that sends the customer to the
 * bank (form or script): shown as "redirecting to the gateway", or as an error when the plugin could not
 * start the payment. Otherwise (bank transfer, wallet) the Amuj order card.
 */
defined('_JEXEC') or die;

defined('AMUJ_BASE') || define('AMUJ_BASE', \Joomla\CMS\Uri\Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$this->nextButton = false;

$html    = (string) $this->html;
$payType = (string) ($this->order->order_payment_method ?? '');
$gateway = $payType !== '' && !in_array($payType, ['banktransfer', 'club'], true);

// Online gateway: the plugin's page sends the customer to the bank (a form or a script redirect)
if (empty($this->order) || ($gateway && (stripos($html, '<form') !== false || stripos($html, 'window.location') !== false))) : ?>
    <div class="az-thanks az-co-redirect">
        <h1>در حال انتقال به درگاه پرداخت</h1>
        <p class="az-muted">لطفاً چند لحظه صبر کنید و صفحه را نبندید.</p>
        <div class="az-co-redirect-form"><?php echo $html; ?></div>
    </div>
    <?php
    return;
endif;

// Online gateway that could not start the payment (e.g. a wrong merchant code): the plugin prints its error
if ($gateway && trim(strip_tags($html)) !== '') : ?>
    <div class="az-thanks is-failed">
        <h1>اتصال به درگاه پرداخت انجام نشد</h1>
        <p class="az-muted">سفارش شما (شماره <?php echo amuj_fa($this->order->order_number); ?>) ثبت شده اما پرداخت آغاز نشد. لطفاً دوباره تلاش کنید یا روش پرداخت دیگری انتخاب کنید.</p>
        <div class="az-thanks-note" dir="auto"><?php echo $this->escape(trim(strip_tags($html))); ?></div>
        <div class="az-thanks-actions">
            <a class="az-btn" href="<?php echo amuj_order_url($this->order->order_id); ?>">مشاهده سفارش</a>
            <a class="az-btn az-btn--ghost" href="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>">بازگشت به فروشگاه</a>
        </div>
    </div>
    <?php
    return;
endif;

$order = $this->order;
$url   = $this->url;

// Bank-transfer instructions come from the payment method (HikaShop > Payment methods > کارت به کارت)
$paymentInfo = '';
if (($order->order_payment_method ?? '') === 'banktransfer' && !empty($order->order_payment_id)) {
    $params = \Joomla\CMS\Factory::getDbo()->setQuery('SELECT payment_params FROM ' . hikashop_table('payment')
        . ' WHERE payment_id = ' . (int) $order->order_payment_id)->loadResult();
    $params = hikashop_unserialize($params);
    $paymentInfo = !empty($params->information) ? hikashop_translate($params->information) : '';
}

require __DIR__ . '/_amuj_thanks.php';
