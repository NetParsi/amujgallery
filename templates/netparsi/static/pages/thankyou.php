<?php
defined('_JEXEC') or die;
?>
<div class="az-thanks">
    <span uk-icon="icon: check; ratio: 2.4"></span>
    <h1>سفارش شما با موفقیت ثبت شد</h1>
    <p>شماره سفارش: <strong><?php echo amuj_fa('AM-140508-2731'); ?></strong></p>
    <p class="az-muted">جزئیات سفارش و کد رهگیری مرسوله به ایمیل و تلفن همراه شما ارسال می‌شود. وضعیت سفارش را از بخش حساب کاربری دنبال کنید.</p>
    <div class="uk-margin-medium-top uk-flex uk-flex-center" style="gap:8px">
        <a class="az-btn" href="<?php echo amuj_url('account'); ?>">پیگیری سفارش</a>
        <a class="az-btn az-btn--ghost" href="<?php echo amuj_url('home'); ?>">ادامه خرید</a>
    </div>
</div>
