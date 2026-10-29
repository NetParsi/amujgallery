<?php
/**
 * Page right after the order is created. A gateway plugin returns an auto-submitting form to the bank:
 * that is shown as "redirecting to the gateway". Otherwise (bank transfer, wallet) the Amuj order card.
 */
defined('_JEXEC') or die;

$this->nextButton = false;

if (empty($this->order) || stripos((string) $this->html, '<form') !== false) : ?>
    <div class="az-thanks az-co-redirect">
        <h1>در حال انتقال به درگاه پرداخت</h1>
        <p class="az-muted">لطفاً چند لحظه صبر کنید و صفحه را نبندید.</p>
        <div class="az-co-redirect-form"><?php echo $this->html; ?></div>
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
