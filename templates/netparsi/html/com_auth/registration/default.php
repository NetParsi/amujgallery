<?php
/**
 * SMS auth step 3, new customers only: name + email (posts to registration.step3, which
 * creates the Joomla user with the mobile as username and signs them in).
 * Email follows the component option: 1 required, 2 optional, 0 hidden.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Auth\Site\Helper\AuthHelper;

require_once dirname(__DIR__) . '/_amuj.php';

$app        = Factory::getApplication();
$referer    = $app->getSession()->get('smsregReferer', '');
$reg_Itemid = AuthHelper::getOneMenu('registration');
$emailMode  = (string) $this->params->get('is_email_required', '1');
$error      = amuj_auth_error();

amuj_auth_open(3);
?>
<form class="az-auth-step is-active" data-az-auth-live method="post" novalidate
      action="<?php echo Route::_('index.php?option=com_auth&task=registration.step3&Itemid=' . $reg_Itemid); ?>">
    <h1>به گالری آموژ خوش آمدید</h1>
    <p class="az-auth-lead">برای تکمیل ثبت‌نام<?php echo $this->mobile ? ' با شماره <strong dir="ltr">' . amuj_auth_phone($this->mobile) . '</strong>' : ''; ?>،
        <?php echo $emailMode === '1' ? 'نام و ایمیل' : 'نام'; ?> خود را وارد کنید.</p>
    <div class="az-field">
        <label for="az-name">نام و نام خانوادگی</label>
        <input class="az-input" id="az-name" name="name" autocomplete="name" required autofocus>
        <span class="az-auth-error" data-error-name></span>
    </div>
    <?php if ($emailMode === '1' || $emailMode === '2') : ?>
        <div class="az-field">
            <label for="az-email">ایمیل<?php echo $emailMode === '2' ? ' <span class="az-muted">(اختیاری)</span>' : ''; ?></label>
            <input class="az-input" id="az-email" name="email" type="email" dir="ltr" autocomplete="email"<?php echo $emailMode === '1' ? ' required' : ''; ?>>
            <span class="az-auth-error" data-error-email></span>
        </div>
    <?php endif; ?>
    <?php foreach ((array) $this->fields as $field) :
        // Extra registration fields of com_users; the mobile one is filled from the verified number
        if (in_array($field->fieldname, ['mobile', 'cellphone'], true)) {
            $field->setValue($this->mobile);
            $field->readonly = true;
            $field->hidden = true;
        } ?>
        <div class="az-field"<?php echo $field->hidden ? ' hidden' : ''; ?>><?php echo $field->label . $field->input; ?></div>
    <?php endforeach; ?>
    <span class="az-auth-error" data-error><?php echo $error; ?></span>
    <button class="az-btn az-btn--block" type="submit">ثبت‌نام و ورود</button>
    <p class="az-auth-resend"><a class="az-auth-link" href="<?php echo Route::_('index.php?option=com_auth&task=registration.clear&Itemid=' . $reg_Itemid); ?>">ورود با شماره دیگر</a></p>
    <input type="hidden" name="referer" value="<?php echo htmlspecialchars($referer, ENT_QUOTES); ?>">
</form>
<?php amuj_auth_close();
