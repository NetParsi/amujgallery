<?php
/**
 * SMS auth step 1: mobile number (posts to cellphone.step1, which sends the code).
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Auth\Site\Helper\AuthHelper;

require_once dirname(__DIR__) . '/_amuj.php';

$app    = Factory::getApplication();
$Itemid = AuthHelper::getOneMenu('cellphone');
$return = $app->getSession()->get('smsregReturn', '') ?: $app->getInput()->get->getString('return', '');
$error  = amuj_auth_error();

amuj_auth_open(1);
?>
<form class="az-auth-step is-active" data-az-auth-live method="post" novalidate
      action="<?php echo Route::_('index.php?option=com_auth&view=cellphone&task=cellphone.step1&Itemid=' . $Itemid . '&' . Session::getFormToken() . '=1'); ?>">
    <h1>ورود / ثبت‌نام</h1>
    <p class="az-auth-lead">برای ورود یا ساخت حساب، شماره موبایل خود را وارد کنید.</p>
    <div class="az-field">
        <label for="az-mobile">شماره موبایل</label>
        <input class="az-input az-auth-mobile" id="az-mobile" type="tel" inputmode="numeric" dir="ltr"
               placeholder="۰۹۱۲ ۳۴۵ ۶۷۸۹" maxlength="13" autocomplete="tel" autofocus>
        <span class="az-auth-error" data-error><?php echo $error; ?></span>
    </div>
    <button class="az-btn az-btn--block" type="submit" disabled>ارسال کد تأیید</button>
    <p class="az-auth-terms">ورود شما به معنای پذیرش <a class="az-link" href="<?php echo amuj_url('policy', ['doc' => 'privacy']); ?>">قوانین و حریم خصوصی</a> گالری آموژ است.</p>
    <input type="hidden" name="mobilenum" value="">
    <input type="hidden" name="return" value="<?php echo htmlspecialchars($return ?: base64_encode(\Joomla\CMS\Uri\Uri::root()), ENT_QUOTES); ?>">
</form>
<?php amuj_auth_close();
