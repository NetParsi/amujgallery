<?php
/**
 * Joomla's login page is not used by the shop: customers sign in with their mobile number (com_auth).
 * Extensions that send guests here (e.g. HikaShop's order and address pages) are forwarded to the
 * mobile login, keeping the page to come back to.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$app    = Factory::getApplication();
$return = $app->getInput()->getBase64('return', '');
$target = base64_decode($return, true);
// Only pages of this site
if (!$target || !Uri::isInternal($target)) {
    $return = base64_encode(amuj_url('account'));
}

if (!$app->getIdentity()->guest) {
    $app->redirect(base64_decode($return));
    return;
}

$app->redirect(amuj_url('login', ['return' => $return]));
