<?php
/**
 * Amuj Gallery: email to the shop owner when a payment is received or refused (override of payment_notification.html.php).
 */
defined('_JEXEC') or die('Restricted access');

$amujOk = in_array($data->cart->order_status, ['confirmed', 'shipped'], true);
$amujMail = [
    'title' => $amujOk ? 'پرداخت سفارش انجام شد' : 'اطلاع پرداخت سفارش',
    'intro' => $amujOk
        ? 'پرداخت این سفارش انجام و سفارش تأیید شد. آن را برای ارسال آماده کنید.'
        : 'وضعیت پرداخت این سفارش: «' . htmlspecialchars(hikashop_orderStatus($data->cart->order_status)) . '».',
    'admin' => true,
];
require __DIR__ . '/amuj_order.php';
