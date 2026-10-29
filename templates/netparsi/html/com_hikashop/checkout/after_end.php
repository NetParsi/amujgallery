<?php
/**
 * Return from a payment gateway (and from the full-wallet payment): the Amuj order card in the state
 * the payment plugin left the order in (paid / waiting / failed). HikaShop's own messages are not repeated.
 */
defined('_JEXEC') or die;

$app = \Joomla\CMS\Factory::getApplication();
$app->getMessageQueue(true);

if (empty($this->order)) {
    echo '<div class="az-thanks"><h1>سفارشی پیدا نشد</h1><p class="az-muted"><a class="az-link" href="' . \Joomla\CMS\Uri\Uri::root() . '">بازگشت به فروشگاه</a></p></div>';
    return;
}

$order = $this->order;
$url   = $this->url;
$paymentInfo = '';

$app->triggerEvent('onHikashopOrderTrackingDisplay', [$order]);

require __DIR__ . '/_amuj_thanks.php';
