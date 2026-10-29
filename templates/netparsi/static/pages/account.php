<?php
defined('_JEXEC') or die;

$user     = amuj_current_user();
$products = amuj_products();
$orders   = amuj_orders();
$lastNo   = array_key_first($orders);
$last     = $orders[$lastNo];
[$statusLabel, $statusMod] = amuj_order_status($last);
$notes    = array_slice(amuj_notifications(), 0, 2);

amuj_account_open('account');
?>
<h1 class="az-account-title">سلام <?php echo $user['first']; ?>، خوش آمدید</h1>

<div class="az-dash-grid az-dash-grid--club">
    <div class="az-club az-dash-club">
        <div class="az-club-head">
            <span class="az-club-label">باشگاه مشتریان آموژ</span>
            <span class="az-club-level"><?php echo $user['level']; ?></span>
        </div>
        <div class="az-club-bar"><span style="--p: <?php echo $user['progress']; ?>%"></span></div>
        <div class="az-club-meta">
            <span><?php echo amuj_price($user['remaining']); ?> خرید تا سطح <strong><?php echo $user['next_level']; ?></strong></span>
        </div>
    </div>
    <a class="az-dash-stat" href="<?php echo amuj_url('credits'); ?>">
        <span>موجودی کیف پول</span>
        <strong><?php echo amuj_price($user['wallet']); ?></strong>
        <small class="az-muted">قابل استفاده در پرداخت سفارش‌ها</small>
    </a>
</div>

<section class="az-dash-section">
    <div class="az-dash-head"><h2>اطلاعات حساب</h2></div>
    <div class="az-profile">
        <dl class="az-dl">
            <dt>نام و نام خانوادگی</dt><dd><?php echo $user['name']; ?></dd>
            <dt>شماره موبایل</dt><dd dir="ltr" class="uk-text-right"><?php echo $user['mobile']; ?></dd>
            <dt>ایمیل</dt><dd dir="ltr" class="uk-text-right"><?php echo $user['email']; ?></dd>
            <dt>تاریخ تولد</dt>
            <dd data-az-birthday>
                <?php if ($user['birthday']) : ?>
                    <?php echo $user['birthday']; ?>
                <?php else : ?>
                    <span class="az-muted" data-az-birthday-text>ثبت نشده</span>
                    <button class="az-btn az-btn--ghost az-btn--sm" type="button" uk-toggle="target: #az-birthday" data-az-birthday-btn>ثبت تاریخ تولد</button>
                <?php endif; ?>
            </dd>
        </dl>
        <?php if (!$user['birthday']) : ?>
            <p class="az-profile-hint" data-az-birthday-hint>تاریخ تولد خود را ثبت کنید تا در روز تولدتان هدیه کیف پول دریافت کنید.</p>
        <?php endif; ?>
    </div>
</section>

<?php
// Birthday popup (Persian calendar). Dynamic: saved to the customer profile by the club extension.
$months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
?>
<div id="az-birthday" uk-modal>
    <div class="uk-modal-dialog uk-modal-body az-modal az-modal--sm">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <h2 class="az-title">ثبت تاریخ تولد</h2>
        <p class="az-muted az-small">تاریخ تولد فقط یک بار ثبت می‌شود و برای ارسال هدیه تولد استفاده خواهد شد.</p>
        <form class="az-birthday-form" data-az-birthday-form>
            <div class="az-field"><label>روز</label>
                <select class="az-select" name="day" data-part="day" required>
                    <option value="">روز</option>
                    <?php for ($d = 1; $d <= 31; $d++) : ?><option value="<?php echo $d; ?>"><?php echo amuj_fa($d); ?></option><?php endfor; ?>
                </select>
            </div>
            <div class="az-field"><label>ماه</label>
                <select class="az-select" name="month" data-part="month" required>
                    <option value="">ماه</option>
                    <?php foreach ($months as $m) : ?><option><?php echo $m; ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="az-field"><label>سال</label>
                <select class="az-select" name="year" data-part="year" required>
                    <option value="">سال</option>
                    <?php for ($y = 1390; $y >= 1320; $y--) : ?><option value="<?php echo $y; ?>"><?php echo amuj_fa($y); ?></option><?php endfor; ?>
                </select>
            </div>
            <p class="az-auth-error" data-error></p>
            <button class="az-btn az-btn--block" type="submit">ذخیره تاریخ تولد</button>
        </form>
    </div>
</div>

<section class="az-dash-section">
    <div class="az-dash-head">
        <h2>آخرین سفارش</h2>
        <a href="<?php echo amuj_url('orders'); ?>">همه سفارش‌ها</a>
    </div>
    <a class="az-order-card" href="<?php echo amuj_url('order', ['no' => $lastNo]); ?>">
        <div class="az-order-card-top">
            <div>
                <strong dir="ltr"><?php echo amuj_fa($lastNo); ?></strong>
                <span class="az-muted"><?php echo $last['date']; ?> · <?php echo amuj_price(amuj_order_total($last)); ?></span>
            </div>
            <span class="az-badge az-badge--<?php echo $statusMod; ?>"><?php echo $statusLabel; ?></span>
        </div>
        <?php $steps = amuj_order_steps(); ?>
        <ol class="az-track az-track--mini">
            <?php foreach ($steps as $i => $step) : ?>
                <li class="<?php echo $i < $last['step'] ? 'is-done' : ($i === $last['step'] ? 'is-current' : ''); ?>"><span><?php echo $step; ?></span></li>
            <?php endforeach; ?>
        </ol>
        <div class="az-order-thumbs">
            <?php foreach (array_keys($last['items']) as $id) : ?><img src="<?php echo amuj_img($products[$id]['img'], 200); ?>" alt=""><?php endforeach; ?>
        </div>
    </a>
</section>

<section class="az-dash-section">
    <div class="az-dash-head">
        <h2>پیام‌های جدید</h2>
        <a href="<?php echo amuj_url('messages'); ?>">همه پیام‌ها</a>
    </div>
    <ul class="az-notify-list az-dash-notes">
        <?php foreach ($notes as $note) : ?>
            <li class="az-note az-note--<?php echo $note['type']; ?><?php echo $note['unread'] ? ' is-unread' : ''; ?>">
                <div class="az-note-meta"><span class="az-note-type"><?php echo $note['label']; ?></span><time><?php echo $note['time']; ?></time></div>
                <strong class="az-note-title"><?php echo $note['title']; ?></strong>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php amuj_account_close(); ?>
