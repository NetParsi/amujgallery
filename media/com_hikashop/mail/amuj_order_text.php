<?php
/**
 * Amuj Gallery: plain-text part of the order emails (shown by mail apps that do not display HTML).
 * Same input as amuj_order.php ($data->cart, $vars, $amujMail).
 */
defined('_JEXEC') or die('Restricted access');

$amujSite = rtrim(HIKASHOP_LIVE, '/') . '/';
defined('AMUJ_BASE') || define('AMUJ_BASE', $amujSite);
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_ROOT . '/templates/netparsi/static/_helpers.php';

$order   = $data->cart;
$isAdmin = !empty($amujMail['admin']);
$out     = [];

$out[] = strip_tags($amujMail['title']);
$out[] = '';
if (!$isAdmin) {
    $name = trim((string) ($order->customer->name ?? ''));
    $out[] = ($name ?: 'مشتری گرامی') . ' عزیز،';
}
$out[] = strip_tags($amujMail['intro']);
$out[] = '';
$out[] = 'شماره سفارش: ' . amuj_fa($order->order_number);
$out[] = 'وضعیت: ' . hikashop_orderStatus($order->order_status);
$out[] = '';
foreach ((array) $order->products as $op) {
    if (!empty($op->order_product_option_parent_id) || (int) $op->order_product_quantity <= 0) {
        continue;
    }
    $line = ((float) $op->order_product_price + (float) $op->order_product_tax) * (int) $op->order_product_quantity;
    $out[] = '- ' . strip_tags($op->order_product_name) . ' × ' . amuj_fa((int) $op->order_product_quantity) . ': ' . amuj_price($line);
}
$out[] = '';
$wallet = 0;
foreach ((array) ($order->additional ?? []) as $add) {
    $p = is_string($add->order_product_params ?? null) ? json_decode($add->order_product_params) : ($add->order_product_params ?? null);
    if (!empty($p->additional_key) && $p->additional_key === 'club') {
        $wallet += abs((float) $add->order_product_price);
    }
}
if ((float) $order->order_discount_price > 0) {
    $out[] = 'تخفیف: ' . amuj_price((float) $order->order_discount_price);
}
$out[] = 'هزینه ارسال: ' . ((float) $order->order_shipping_price > 0 ? amuj_price((float) $order->order_shipping_price) : 'رایگان');
if ($wallet > 0) {
    $out[] = 'پرداخت از کیف پول: ' . amuj_price($wallet);
}
$out[] = 'مبلغ قابل پرداخت: ' . amuj_price((float) $order->order_full_price);
if (!empty($vars['SHIPPING'])) {
    $out[] = 'روش ارسال: ' . trim(strip_tags($vars['SHIPPING']));
}
if (!empty($vars['PAYMENT'])) {
    $out[] = 'روش پرداخت: ' . trim(strip_tags($vars['PAYMENT']));
}

$unpaid = in_array($order->order_status, explode(',', hikashop_config()->get('order_unpaid_statuses', 'created')), true);
if (!empty($amujMail['pay_info']) && $unpaid && $order->order_payment_method === 'banktransfer' && (float) $order->order_full_price > 0) {
    $payment = hikashop_get('class.payment')->get($order->order_payment_id);
    $info = trim(html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n", (string) ($payment->payment_params->information ?? ''))), ENT_QUOTES, 'UTF-8'));
    if ($info !== '') {
        $out[] = '';
        $out[] = 'اطلاعات واریز:';
        $out[] = $info;
        $out[] = 'شماره سفارش را در توضیحات واریز بنویسید.';
    }
}

$out[] = '';
$out[] = ($isAdmin ? 'مشاهده سفارش در مدیریت: ' . $amujSite . 'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&cid[]=' . (int) $order->order_id
    : 'مشاهده سفارش: ' . ($vars['URL'] ?? $amujSite));
$out[] = '';
$out[] = 'گالری آموژ — ' . $amujSite;

echo implode("\n", $out);
