<?php
/**
 * Customer club birthday popup (mod_clubpopup) in the Amuj design: the same modal as the dashboard.
 * Shown to a signed-in customer without a birthday, once per "hide for" days of the module (cookie);
 * saved by the club extension (task saveBirthday).
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$app  = Factory::getApplication();
$user = $app->getIdentity();
if ($user->guest || !empty($user->get('birthday'))) {
    return;
}
// The dashboard has the same form in its own modal
if ($app->getInput()->getCmd('option') === 'com_club' && $app->getInput()->getCmd('view', 'profile') === 'profile') {
    return;
}

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$cookie   = 'amuj_bday_' . (int) $module->id;
$hideDays = max(1, (int) $params->get('hide_for', 1));
if (!empty($_COOKIE[$cookie])) {
    return;
}

$months   = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
$thisYear = amuj_jyear();
$id       = 'az-birthday-popup';
?>
<div id="<?php echo $id; ?>" uk-modal="bg-close: false">
    <div class="uk-modal-dialog uk-modal-body az-modal az-modal--sm">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <h2 class="az-title">ثبت تاریخ تولد</h2>
        <p class="az-muted az-small">تاریخ تولد خود را ثبت کنید تا در روز تولدتان هدیه کیف پول دریافت کنید. تاریخ تولد فقط یک بار ثبت می‌شود.</p>
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
                    <?php for ($y = $thisYear - 10; $y >= $thisYear - 85; $y--) : ?><option value="<?php echo $y; ?>"><?php echo amuj_fa($y); ?></option><?php endfor; ?>
                </select>
            </div>
            <p class="az-auth-error" data-error></p>
            <button class="az-btn az-btn--block" type="submit">ذخیره تاریخ تولد</button>
            <button class="az-birthday-later uk-modal-close" type="button">بعداً</button>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('<?php echo $id; ?>');
    if (!el || !window.UIkit) return;
    setTimeout(function () { UIkit.modal(el).show(); }, 1200);
    // Closed (or saved): not again for the module's "hide for" days
    UIkit.util.on(el, 'hidden', function () {
        document.cookie = '<?php echo $cookie; ?>=1; max-age=<?php echo $hideDays * 86400; ?>; path=/; SameSite=Lax';
    });
});
</script>
