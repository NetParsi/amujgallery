<?php
/**
 * Customer panel dashboard (menu item "account" -> com_club profile) in the Amuj design:
 * club level + progress, wallet, account details with the birthday form (saved by the club
 * extension, task saveBirthday), the latest order and the latest club messages.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

amuj_panel_guard();
Factory::getDocument()->setTitle('داشبورد | حساب کاربری');

$user   = amuj_current_user();
$orders = amuj_customer_orders($user['id'], 1);
$last   = $orders[0] ?? null;
$thumbs = $last ? amuj_order_thumbs([$last->order_id]) : [];
$notes  = array_slice(amuj_notifications(), 0, 2);
$steps  = amuj_panel_steps();

amuj_account_open('account');
?>
<h1 class="az-account-title">سلام <?php echo $this->escape($user['first']); ?>، خوش آمدید</h1>

<div class="az-dash-grid az-dash-grid--club">
    <div class="az-club az-dash-club">
        <div class="az-club-head">
            <span class="az-club-label">باشگاه مشتریان آموژ</span>
            <?php if ($user['level']) : ?><span class="az-club-level"><?php echo $this->escape($user['level']); ?></span><?php endif; ?>
        </div>
        <div class="az-club-bar"><span style="--p: <?php echo (int) $user['progress']; ?>%"></span></div>
        <div class="az-club-meta">
            <?php if ($user['next_level']) : ?>
                <span><?php echo amuj_price($user['remaining']); ?> خرید تا سطح <strong><?php echo $this->escape($user['next_level']); ?></strong></span>
            <?php else : ?>
                <span>شما در بالاترین سطح باشگاه هستید.</span>
            <?php endif; ?>
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
            <dt>نام و نام خانوادگی</dt><dd><?php echo $this->escape($user['name']); ?></dd>
            <?php if ($user['mobile']) : ?><dt>شماره موبایل</dt><dd dir="ltr" class="uk-text-right"><?php echo $user['mobile']; ?></dd><?php endif; ?>
            <dt>ایمیل</dt><dd dir="ltr" class="uk-text-right"><?php echo $user['email'] ? $this->escape($user['email']) : '<span class="az-muted">ثبت نشده</span>'; ?></dd>
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

<?php if (!$user['birthday']) :
    $months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    $thisYear = amuj_jyear();
    ?>
    <div id="az-birthday" uk-modal>
        <div class="uk-modal-dialog uk-modal-body az-modal az-modal--sm">
            <button class="uk-modal-close-default" type="button" uk-close></button>
            <h2 class="az-title">ثبت تاریخ تولد</h2>
            <p class="az-muted az-small">تاریخ تولد فقط یک بار ثبت می‌شود و برای ارسال هدیه تولد استفاده خواهد شد.</p>
            <form class="az-birthday-form" data-az-birthday-form action="<?php echo AMUJ_BASE; ?>index.php">
                <input type="hidden" name="option" value="com_club">
                <input type="hidden" name="task" value="saveBirthday">
                <input type="hidden" name="<?php echo Session::getFormToken(); ?>" value="1">
                <div class="az-field"><label>روز</label>
                    <select class="az-select" name="b_day" data-part="day" required>
                        <option value="">روز</option>
                        <?php for ($d = 1; $d <= 31; $d++) : ?><option value="<?php echo sprintf('%02d', $d); ?>"><?php echo amuj_fa($d); ?></option><?php endfor; ?>
                    </select>
                </div>
                <div class="az-field"><label>ماه</label>
                    <select class="az-select" name="b_month" data-part="month" required>
                        <option value="">ماه</option>
                        <?php foreach ($months as $i => $m) : ?><option value="<?php echo sprintf('%02d', $i + 1); ?>"><?php echo $m; ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="az-field"><label>سال</label>
                    <select class="az-select" name="b_year" data-part="year" required>
                        <option value="">سال</option>
                        <?php for ($y = ($thisYear ?: 1405) - 10; $y >= ($thisYear ?: 1405) - 85; $y--) : ?><option value="<?php echo $y; ?>"><?php echo amuj_fa($y); ?></option><?php endfor; ?>
                    </select>
                </div>
                <p class="az-auth-error" data-error></p>
                <button class="az-btn az-btn--block" type="submit">ذخیره تاریخ تولد</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<section class="az-dash-section">
    <div class="az-dash-head">
        <h2>آخرین سفارش</h2>
        <?php if ($last) : ?><a href="<?php echo amuj_url('orders'); ?>">همه سفارش‌ها</a><?php endif; ?>
    </div>
    <?php if ($last) :
        [$label, $mod, $step] = amuj_order_state($last->order_status);
        ?>
        <a class="az-order-card" href="<?php echo amuj_order_url($last->order_id); ?>">
            <div class="az-order-card-top">
                <div>
                    <strong dir="ltr"><?php echo amuj_fa($last->order_number); ?></strong>
                    <span class="az-muted"><?php echo amuj_jdate($last->order_created); ?> · <?php echo amuj_price($last->order_full_price); ?></span>
                </div>
                <span class="az-badge az-badge--<?php echo $mod; ?>"><?php echo $label; ?></span>
            </div>
            <?php if ($step >= 0) : ?>
                <ol class="az-track az-track--mini">
                    <?php foreach ($steps as $i => $s) : ?>
                        <li class="<?php echo $i < $step ? 'is-done' : ($i === $step ? 'is-current' : ''); ?>"><span><?php echo $s; ?></span></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
            <div class="az-order-thumbs">
                <?php foreach (array_slice($thumbs[$last->order_id] ?? [], 0, 4) as $img) : ?><img src="<?php echo $img; ?>" alt=""><?php endforeach; ?>
            </div>
        </a>
    <?php else : ?>
        <?php amuj_empty_state('orders', 'هنوز سفارشی ثبت نکرده‌اید', 'اولین خریدتان را از کالکشن جدید شروع کنید.', 'مشاهده جدیدترین‌ها', amuj_url('category')); ?>
    <?php endif; ?>
</section>

<section class="az-dash-section">
    <div class="az-dash-head">
        <h2>پیام‌های اخیر</h2>
        <?php if ($notes) : ?><a href="<?php echo amuj_url('messages'); ?>">همه پیام‌ها</a><?php endif; ?>
    </div>
    <?php if ($notes) : ?>
        <ul class="az-notify-list az-dash-notes">
            <?php foreach ($notes as $note) : ?>
                <li class="az-note az-note--<?php echo $note['type']; ?><?php echo $note['unread'] ? ' is-unread' : ''; ?>">
                    <div class="az-note-meta"><span class="az-note-type"><?php echo $note['label']; ?></span><time><?php echo $note['time']; ?></time></div>
                    <strong class="az-note-title"><?php echo $this->escape($note['title']); ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <?php amuj_empty_state('message', 'پیامی ندارید', 'پیام‌های باشگاه مشتریان و فروشگاه اینجا نمایش داده می‌شود.'); ?>
    <?php endif; ?>
</section>
<?php amuj_account_close(); ?>
