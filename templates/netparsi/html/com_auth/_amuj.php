<?php
/**
 * Shared frame for the SMS auth component's three pages (cellphone, authcode, registration)
 * in the Amuj sign-in design (static/pages/login.php). Each page posts to the component as before;
 * netparsi.js [data-az-auth-live] formats the mobile, joins the code boxes and runs the resend timer.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

if (!function_exists('amuj_auth_open')) {
    /**
     * Takes the component's messages off the queue: errors are shown inside the card,
     * notices ("complete the form", ...) are dropped because each step already explains itself.
     */
    function amuj_auth_error()
    {
        $app = Factory::getApplication();
        $errors = [];
        foreach ($app->getMessageQueue(true) as $msg) {
            if (in_array($msg['type'], ['error', 'danger', 'warning'], true)) {
                $errors[] = $msg['message'];
            }
        }

        return implode(' ', $errors);
    }

    function amuj_auth_open($step)
    {
        ?>
        <div class="az-auth-page">
            <a class="az-logo az-auth-logo" href="<?php echo amuj_url('home'); ?>" aria-label="گالری آموژ"><?php echo amuj_logo(); ?></a>
            <div class="az-auth-card">
                <ol class="az-auth-steps" aria-hidden="true">
                    <?php for ($i = 1; $i <= 3; $i++) : ?><li<?php echo $i <= $step ? ' class="is-active"' : ''; ?>></li><?php endfor; ?>
                </ol>
        <?php
    }

    function amuj_auth_close()
    {
        ?>
            </div>
            <a class="az-auth-home" href="<?php echo amuj_url('home'); ?>">بازگشت به صفحه اصلی</a>
        </div>
        <?php
    }

    /** 09123456789 -> ۰۹۱۲ ۳۴۵ ۶۷۸۹ */
    function amuj_auth_phone($mobile)
    {
        return amuj_fa(preg_replace('/^(\d{4})(\d{3})(\d+)$/', '$1 $2 $3', (string) $mobile));
    }
}
