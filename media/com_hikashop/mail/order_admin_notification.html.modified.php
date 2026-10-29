<?php
/**
 * Amuj Gallery: email to the shop owner for a new order (override of order_admin_notification.html.php).
 */
defined('_JEXEC') or die('Restricted access');

$amujMail = [
    'title' => 'سفارش جدید در فروشگاه',
    'intro' => $data->cart->order_payment_method === 'banktransfer' && $data->cart->order_status === 'created'
        ? 'یک سفارش کارت به کارت ثبت شد. پس از دیدن واریز در حساب، وضعیت سفارش را در مدیریت به «تأییدشده» تغییر دهید.'
        : 'یک سفارش جدید در فروشگاه ثبت شد.',
    'admin' => true,
];
require __DIR__ . '/amuj_order.php';
