<?php
/**
 * Amuj Gallery: email to the customer when the order is created (override of order_creation_notification.html.php).
 */
defined('_JEXEC') or die('Restricted access');

$amujPaid = in_array($data->cart->order_status, ['confirmed', 'shipped'], true);
$amujMail = [
    'title'    => $amujPaid ? 'سفارش شما ثبت و پرداخت شد' : 'سفارش شما ثبت شد',
    'intro'    => $amujPaid
        ? 'از خرید شما سپاسگزاریم. سفارش شما در حال آماده‌سازی است و پس از ارسال، کد رهگیری مرسوله برای شما پیامک می‌شود.'
        : ($data->cart->order_payment_method === 'banktransfer'
            ? 'از خرید شما سپاسگزاریم. برای نهایی شدن سفارش، مبلغ آن را به کارت زیر واریز کنید؛ پس از تأیید واریز، سفارش ارسال می‌شود.'
            : 'از خرید شما سپاسگزاریم. سفارش شما ثبت شد و پس از تأیید پرداخت پردازش می‌شود.'),
    'pay_info' => true,
];
require __DIR__ . '/amuj_order.php';
