<?php
defined('_JEXEC') or die;
?>
<div class="az-narrow" style="max-width:640px">
    <h1 class="az-title">ایجاد حساب کاربری</h1>
    <form class="az-form-grid" onsubmit="return false">
        <div class="az-field"><label>نام</label><input class="az-input"></div>
        <div class="az-field"><label>نام خانوادگی</label><input class="az-input"></div>
        <div class="az-field"><label>ایمیل</label><input class="az-input" type="email"></div>
        <div class="az-field"><label>تلفن همراه</label><input class="az-input" type="tel"></div>
        <div class="az-field"><label>رمز عبور</label><input class="az-input" type="password"></div>
        <div class="az-field"><label>تکرار رمز عبور</label><input class="az-input" type="password"></div>
    </form>
    <label class="az-check"><input type="checkbox" checked> مایلم از کالکشن‌های جدید و تخفیف‌ها از طریق ایمیل باخبر شوم.</label>
    <label class="az-check"><input type="checkbox"> <span><a class="az-link" href="#">قوانین و حریم خصوصی</a> را مطالعه کرده‌ام و می‌پذیرم.</span></label>
    <a class="az-btn uk-margin-top" style="min-width:260px" href="<?php echo amuj_url('account'); ?>">ایجاد حساب</a>
    <p class="az-small uk-margin-top">حساب کاربری دارید؟ <a class="az-link" href="<?php echo amuj_url('login'); ?>">وارد شوید</a></p>
</div>
