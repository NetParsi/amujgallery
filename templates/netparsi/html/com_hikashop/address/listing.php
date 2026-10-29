<?php
/**
 * Customer panel: addresses (menu item "addresses" -> HikaShop address listing) in the Amuj design.
 * Cards of the saved addresses; edit / new open HikaShop's address form, delete and "set as default"
 * use HikaShop's own address tasks (with the form token).
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

amuj_panel_guard();
Factory::getDocument()->setTitle('آدرس‌های من');

$db     = Factory::getDbo();
$hkUser = (int) hikashop_loadUser(false);
$rows   = $hkUser ? $db->setQuery('SELECT * FROM ' . hikashop_table('address') . ' WHERE address_user_id = ' . $hkUser
    . ' AND address_published = 1 AND (address_type IN ("", "both", "billing") OR address_type IS NULL) ORDER BY address_default DESC, address_id DESC')->loadObjectList() : [];

// The checkout saves an address twice (billing + shipping copy): show each place once
$seen = [];
$addresses = [];
foreach ($rows as $a) {
    $key = md5(implode('|', [$a->address_firstname, $a->address_lastname, $a->address_street, $a->address_city, $a->address_post_code, $a->address_telephone]));
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $addresses[] = $a;
    }
}
$states = [];
$keys = array_filter(array_unique(array_column(array_map('get_object_vars', $addresses), 'address_state')));
if ($keys) {
    $states = $db->setQuery('SELECT zone_namekey, zone_name FROM ' . hikashop_table('zone') . ' WHERE zone_namekey IN ('
        . implode(',', array_map([$db, 'quote'], $keys)) . ')')->loadAssocList('zone_namekey', 'zone_name');
}

$token = Session::getFormToken();
global $Itemid;
$item = $Itemid ? '&Itemid=' . (int) $Itemid : '';

amuj_account_open('addresses');
?>
<h1 class="az-account-title">آدرس‌های من</h1>

<div class="az-address-grid">
    <?php foreach ($addresses as $a) :
        $place = implode('، ', array_unique(array_filter([$states[$a->address_state] ?? '', $a->address_city, $a->address_street])));
        ?>
        <div class="az-address<?php echo $a->address_default ? ' is-default' : ''; ?>">
            <div class="az-address-top">
                <strong><?php echo $this->escape(trim($a->address_firstname . ' ' . $a->address_lastname)); ?></strong>
                <?php if ($a->address_default) : ?><span class="az-badge az-badge--done">پیش‌فرض</span><?php endif; ?>
            </div>
            <p><?php echo $this->escape(amuj_fa($place)); ?></p>
            <dl class="az-dl az-dl--compact">
                <?php if ($a->address_telephone) : ?><dt>موبایل</dt><dd dir="ltr"><?php echo amuj_fa($this->escape($a->address_telephone)); ?></dd><?php endif; ?>
                <?php if ($a->address_post_code) : ?><dt>کد پستی</dt><dd dir="ltr"><?php echo amuj_fa($this->escape($a->address_post_code)); ?></dd><?php endif; ?>
            </dl>
            <div class="az-address-actions">
                <a href="<?php echo hikashop_completeLink('address&task=edit&cid=' . (int) $a->address_id . $item); ?>">ویرایش</a>
                <?php if (!$a->address_default) : ?>
                    <a href="<?php echo hikashop_completeLink('address&task=setdefault&address_default=' . (int) $a->address_id . '&' . $token . '=1' . $item); ?>">انتخاب به‌عنوان پیش‌فرض</a>
                    <a class="az-address-delete" href="<?php echo hikashop_completeLink('address&task=delete&address_id=' . (int) $a->address_id . '&' . $token . '=1' . $item); ?>"
                        onclick="return confirm('این آدرس حذف شود؟');">حذف</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <a class="az-address az-address-add" href="<?php echo hikashop_completeLink('address&task=edit&cid=0&makenew=1' . $item); ?>">
        <span class="az-address-plus" aria-hidden="true"></span>
        افزودن آدرس جدید
    </a>
</div>
<?php amuj_account_close(); ?>
