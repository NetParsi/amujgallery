<?php
/**
 * Amuj Gallery: email to the customer when the order status changes (override of order_notification.html.php, sent when the shop owner notifies the customer).
 */
defined('_JEXEC') or die('Restricted access');

$amujTexts = [
    'confirmed' => ['پرداخت سفارش شما تأیید شد', 'سفارش شما در حال آماده‌سازی است و پس از ارسال، کد رهگیری مرسوله برای شما پیامک می‌شود.'],
    'shipped'   => ['سفارش شما ارسال شد', 'سفارش شما تحویل شرکت حمل شد. امیدواریم از خریدتان لذت ببرید.'],
    'cancelled' => ['سفارش شما لغو شد', 'سفارش شما لغو شد. اگر مبلغی پرداخت کرده‌اید، طبق شرایط بازگشت وجه به حساب شما برگردانده می‌شود.'],
    'refunded'  => ['مبلغ سفارش شما بازگردانده شد', 'مبلغ سفارش شما بازگردانده شد. اگر سؤالی دارید با ما در تماس باشید.'],
];
[$amujTitle, $amujIntro] = $amujTexts[$data->cart->order_status]
    ?? ['وضعیت سفارش شما تغییر کرد', 'وضعیت سفارش شما به «' . htmlspecialchars(hikashop_orderStatus($data->cart->order_status)) . '» تغییر کرد.'];
$amujMail = ['title' => $amujTitle, 'intro' => $amujIntro, 'pay_info' => true];
require __DIR__ . '/amuj_order.php';
