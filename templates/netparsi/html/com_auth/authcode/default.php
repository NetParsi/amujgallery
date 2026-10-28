<?php
/**
 * SMS auth step 2: the 5-digit code (posts to authcode.step2: existing customer signs in,
 * a new mobile goes on to the registration page). Resend waits for the component's "resend" minutes.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Auth\Site\Helper\AuthHelper;

require_once dirname(__DIR__) . '/_amuj.php';

$app       = Factory::getApplication();
$session   = $app->getSession();
$code      = $session->get('smsregCode', '');
$mobile    = $session->get('smsregMobile', '');
$referer   = $session->get('smsregReferer', '');
$cp_Itemid = AuthHelper::getOneMenu('cellphone');
$ac_Itemid = AuthHelper::getOneMenu('authcode');

if (empty($code)) {
    $app->enqueueMessage('زمان کد تأیید تمام شده است؛ دوباره شماره موبایل را وارد کنید.', 'error');
    $app->redirect(Route::_('index.php?option=com_auth&view=cellphone&Itemid=' . $cp_Itemid, false));
}

// Seconds until "resend" is allowed (same rule as the component's own layout)
$confirm = AuthHelper::getConfirm('', $code, -1);
$wait    = max(0, (int) ($confirm->time ?? 0) + (int) AuthHelper::getParams()->get('resend', '') * 60 - time());
$error   = amuj_auth_error();

amuj_auth_open(2);
?>
<form class="az-auth-step is-active" data-az-auth-live method="post" novalidate
      action="<?php echo Route::_('index.php?option=com_auth&view=authcode&task=authcode.step2&Itemid=' . $ac_Itemid); ?>">
    <h1>کد تأیید را وارد کنید</h1>
    <p class="az-auth-lead">کد ۵ رقمی به شماره <strong dir="ltr"><?php echo amuj_auth_phone($mobile); ?></strong> ارسال شد.
        <a class="az-auth-link" href="<?php echo Route::_('index.php?option=com_auth&view=cellphone&Itemid=' . $cp_Itemid); ?>">ویرایش شماره</a></p>
    <div class="az-otp" dir="ltr">
        <?php for ($i = 0; $i < 5; $i++) : ?>
            <input type="text" inputmode="numeric" aria-label="رقم <?php echo amuj_fa($i + 1); ?>" autocomplete="<?php echo $i === 0 ? 'one-time-code' : 'off'; ?>"<?php echo $i === 0 ? ' autofocus' : ''; ?>>
        <?php endfor; ?>
    </div>
    <span class="az-auth-error" data-error><?php echo $error; ?></span>
    <button class="az-btn az-btn--block" type="submit" disabled>تأیید و ادامه</button>
    <p class="az-auth-resend">
        <span data-az-timer data-left="<?php echo $wait; ?>"<?php echo $wait ? '' : ' hidden'; ?>>ارسال مجدد کد تا <b></b></span>
        <a class="az-auth-link" data-az-resend<?php echo $wait ? ' hidden' : ''; ?>
           href="<?php echo Route::_('index.php?option=com_auth&view=authcode&task=authcode.sendCode&Itemid=' . $ac_Itemid . '&' . Session::getFormToken() . '=1'); ?>">ارسال مجدد کد</a>
    </p>
    <input type="hidden" name="codenum" value="">
    <input type="hidden" name="referer" value="<?php echo htmlspecialchars($referer, ENT_QUOTES); ?>">
</form>
<?php amuj_auth_close();
