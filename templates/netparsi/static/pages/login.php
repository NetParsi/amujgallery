<?php
defined('_JEXEC') or die;

/*
 * Sign in / sign up in three steps (same flow as the custom auth component):
 *   1. mobile number  ->  2. SMS code  ->  existing customer: signed in
 *                                       ->  new customer: 3. name + email, then signed in
 * Demo: any 11-digit mobile; code 11111 = existing customer, any other 5 digits = new customer.
 * Dynamic: the auth component's views get template overrides with this markup.
 */
?>
<div class="az-auth-page">
    <a class="az-logo az-auth-logo" href="<?php echo amuj_url('home'); ?>" aria-label="گالری آموژ"><?php echo amuj_logo(); ?></a>

    <div class="az-auth-card" data-az-auth data-success="<?php echo amuj_url('account', ['demo_user' => 1]); ?>">
        <ol class="az-auth-steps" aria-hidden="true">
            <li class="is-active"></li><li></li><li></li>
        </ol>

        <!-- Step 1: mobile -->
        <form class="az-auth-step is-active" data-step="1" novalidate>
            <h1>ورود / ثبت‌نام</h1>
            <p class="az-auth-lead">برای ورود یا ساخت حساب، شماره موبایل خود را وارد کنید.</p>
            <div class="az-field">
                <label for="az-mobile">شماره موبایل</label>
                <input class="az-input az-auth-mobile" id="az-mobile" name="mobile" type="tel" inputmode="numeric" dir="ltr"
                       placeholder="۰۹۱۲ ۳۴۵ ۶۷۸۹" maxlength="13" autocomplete="tel" autofocus>
                <span class="az-auth-error" data-error></span>
            </div>
            <button class="az-btn az-btn--block" type="submit" disabled>ارسال کد تأیید</button>
            <p class="az-auth-terms">ورود شما به معنای پذیرش <a class="az-link" href="<?php echo amuj_url('help'); ?>">قوانین و حریم خصوصی</a> گالری آموژ است.</p>
        </form>

        <!-- Step 2: code -->
        <form class="az-auth-step" data-step="2" novalidate>
            <h1>کد تأیید را وارد کنید</h1>
            <p class="az-auth-lead">کد ۵ رقمی به شماره <strong dir="ltr" data-az-phone></strong> ارسال شد.
                <button type="button" class="az-auth-link" data-az-back>ویرایش شماره</button></p>
            <div class="az-otp" dir="ltr">
                <?php for ($i = 0; $i < 5; $i++) : ?>
                    <input type="text" inputmode="numeric" maxlength="1" aria-label="رقم <?php echo amuj_fa($i + 1); ?>" autocomplete="<?php echo $i === 0 ? 'one-time-code' : 'off'; ?>">
                <?php endfor; ?>
            </div>
            <span class="az-auth-error" data-error></span>
            <button class="az-btn az-btn--block" type="submit" disabled>تأیید و ادامه</button>
            <p class="az-auth-resend">
                <span data-az-timer>ارسال مجدد کد تا <b>۲:۰۰</b></span>
                <button type="button" class="az-auth-link" data-az-resend hidden>ارسال مجدد کد</button>
            </p>
        </form>

        <!-- Step 3: new customer -->
        <form class="az-auth-step" data-step="3" novalidate>
            <h1>به گالری آموژ خوش آمدید</h1>
            <p class="az-auth-lead">برای تکمیل ثبت‌نام، نام و ایمیل خود را وارد کنید.</p>
            <div class="az-field">
                <label for="az-name">نام و نام خانوادگی</label>
                <input class="az-input" id="az-name" name="name" autocomplete="name">
                <span class="az-auth-error" data-error-name></span>
            </div>
            <div class="az-field">
                <label for="az-email">ایمیل</label>
                <input class="az-input" id="az-email" name="email" type="email" dir="ltr" autocomplete="email">
                <span class="az-auth-error" data-error-email></span>
            </div>
            <button class="az-btn az-btn--block" type="submit">ثبت‌نام و ورود</button>
        </form>

        <!-- Done -->
        <div class="az-auth-step az-auth-done" data-step="4">
            <div class="az-status-icon" aria-hidden="true">
                <svg viewBox="0 0 96 96"><circle class="az-status-ring" cx="48" cy="48" r="44"/><path class="az-status-mark" d="M28 50l13 13 27-29"/></svg>
            </div>
            <h1 data-az-done-title>خوش آمدید</h1>
            <p class="az-auth-lead">در حال انتقال به حساب کاربری…</p>
        </div>
    </div>

    <a class="az-auth-home" href="<?php echo amuj_url('home'); ?>">بازگشت به صفحه اصلی</a>
</div>
