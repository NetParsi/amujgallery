<?php
/**
 * Customer panel: add / edit an address (HikaShop address form) in the Amuj design.
 * The fields are HikaShop's own (same layout and styles as the checkout address form).
 */
defined('_JEXEC') or die;

if ($this->tmpl === 'component') {
    require JPATH_ROOT . '/components/com_hikashop/views/address/tmpl/form.php';
    return;
}

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

amuj_panel_guard();

$isNew = empty($this->address->address_id) || hikaInput::get()->getInt('makenew');
Factory::getDocument()->setTitle($isNew ? 'آدرس جدید' : 'ویرایش آدرس');
global $Itemid;

amuj_account_open('addresses');
?>
<a class="az-back" href="<?php echo amuj_url('addresses'); ?>">→ بازگشت به آدرس‌ها</a>
<h1 class="az-account-title"><?php echo $isNew ? 'آدرس جدید' : 'ویرایش آدرس'; ?></h1>

<form action="<?php echo hikashop_completeLink('address&task=save'); ?>" method="post" name="hikashop_address_form" id="hikashop_address_form"
    enctype="multipart/form-data" class="az-co-live az-address-edit"
    onsubmit="return hikashopCheckChangeForm('address', 'hikashop_address_form');">
    <?php
    $this->fields = $this->extraFields['address'];
    $this->fieldClass = $this->fieldsClass;
    $this->inputPrefix = 'data[address]';
    $this->idPrefix = 'hikashop_address';
    $this->onChangeJs = "window.hikashop.toggleField(this,'{FIELD}','address',0,'hikashop_');";
    echo '<fieldset class="hkform-horizontal">' . $this->loadHkLayout('address_form_layout') . '</fieldset>';
    ?>
    <input type="hidden" name="Itemid" value="<?php echo (int) $Itemid; ?>"/>
    <input type="hidden" name="ctrl" value="address"/>
    <input type="hidden" name="task" value="save"/>
    <input type="hidden" name="type" value="<?php echo hikaInput::get()->getCmd('type', ''); ?>"/>
    <input type="hidden" name="action" value="<?php echo hikaInput::get()->getCmd('task', ''); ?>"/>
    <input type="hidden" name="makenew" value="<?php echo hikaInput::get()->getInt('makenew', 0); ?>"/>
    <input type="hidden" name="redirect" value="<?php echo hikaInput::get()->getWord('redirect', ''); ?>"/>
    <input type="hidden" name="step" value="<?php echo hikaInput::get()->getInt('step', -1); ?>"/>
    <input type="hidden" name="data[address][address_user_id]" value="<?php echo !empty($this->address->address_user_id) ? (int) $this->address->address_user_id : (int) $this->user_id; ?>"/>
    <?php if (!hikaInput::get()->getInt('makenew')) : ?>
        <input type="hidden" name="data[address][address_id]" value="<?php echo (int) @$this->address->address_id; ?>"/>
        <input type="hidden" name="address_id" value="<?php echo (int) @$this->address->address_id; ?>"/>
    <?php endif; ?>
    <?php echo HTMLHelper::_('form.token'); ?>
    <div class="az-address-edit-actions">
        <button class="az-btn" type="submit">ذخیره آدرس</button>
        <a class="az-btn az-btn--ghost" href="<?php echo amuj_url('addresses'); ?>">انصراف</a>
    </div>
</form>
<?php amuj_account_close(); ?>
