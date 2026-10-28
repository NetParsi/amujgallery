<?php
defined('_JEXEC') or die;
?>
<div class="az-auth">
    <div class="az-auth-col">
        <h1 class="az-title">ورود به حساب کاربری</h1>
        <form onsubmit="return false">
            <div class="az-field"><label>ایمیل یا تلفن همراه</label><input class="az-input" type="text"></div>
            <div class="az-field"><label>رمز عبور</label><input class="az-input" type="password"></div>
            <a class="az-btn az-btn--block" href="<?php echo amuj_url('account'); ?>">ورود</a>
            <p class="uk-margin-top"><a class="az-link az-small" href="#">رمز عبور را فراموش کرده‌اید؟</a></p>
        </form>
    </div>
    <div class="az-auth-col">
        <h2 class="az-title">ثبت‌نام</h2>
        <p class="az-muted">با ساخت حساب کاربری، سفارش‌هایتان را پیگیری کنید، آدرس‌ها را ذخیره کنید و از تخفیف‌های ویژه اعضا باخبر شوید.</p>
        <a class="az-btn az-btn--ghost az-btn--block uk-margin-top" href="<?php echo amuj_url('register'); ?>">ایجاد حساب کاربری</a>
    </div>
</div>
