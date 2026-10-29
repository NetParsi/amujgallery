<?php
/**
 * Checkout address in the Amuj design: the customer's saved addresses as one list; the chosen address is
 * used for billing and delivery both. Adding / editing an address keeps HikaShop's own form (core layout).
 */
defined('_JEXEC') or die;

if (!empty($this->options['edit_address']) || empty($this->options['display'])) {
    require JPATH_ROOT . '/components/com_hikashop/views/checkout/tmpl/show_block_address.php';
    return;
}

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$cart      = $this->checkoutHelper->getCart();
$addresses = $this->checkoutHelper->getAddresses();
$step      = (int) $this->step;
$pos       = (int) $this->module_position;
$list      = [];
foreach ($addresses['data'] as $address) {
    if (empty($address->address_type) || in_array($address->address_type, ['both', 'billing'], true)) {
        $list[(int) $address->address_id] = $address;
    }
}
$selected = (int) $cart->cart_billing_address_id;
if (!isset($list[$selected])) {
    $selected = 0;
}

// Province names (HikaShop zone namekeys) for the address lines
$zones = [];
$keys  = array_filter(array_unique(array_column(array_map('get_object_vars', $list), 'address_state')));
if ($keys) {
    $db = Factory::getDbo();
    $zones = $db->setQuery('SELECT zone_namekey, zone_name FROM ' . hikashop_table('zone') . ' WHERE zone_namekey IN ('
        . implode(',', array_map([$db, 'quote'], $keys)) . ')')->loadAssocList('zone_namekey', 'zone_name');
}
?>
<?php if (empty($this->ajax)) : ?>
<div id="hikashop_checkout_address_<?php echo $step; ?>_<?php echo $pos; ?>" data-checkout-step="<?php echo $step; ?>" data-checkout-pos="<?php echo $pos; ?>" class="hikashop_checkout_address">
<?php endif; ?>
    <div class="hikashop_checkout_loading_elem"></div>
    <div class="hikashop_checkout_loading_spinner"></div>
    <?php $this->checkoutHelper->displayMessages('address'); ?>

    <div class="az-co-addresses">
        <?php foreach ($list as $id => $a) :
            $place = implode('، ', array_filter([$zones[$a->address_state] ?? '', $a->address_city, $a->address_street]));
            ?>
            <label class="az-option az-co-address-item">
                <span class="az-co-address-text">
                    <input type="radio" name="checkout[address][billing]" value="<?php echo $id; ?>" <?php echo $id === $selected ? 'checked' : ''; ?>
                        onchange="this.form.querySelector('[data-az-ship-address]').value = this.value; window.checkout.submitAddress(<?php echo $step; ?>, <?php echo $pos; ?>);">
                    <span>
                        <strong><?php echo $this->escape(trim($a->address_firstname . ' ' . $a->address_lastname)); ?></strong>
                        <span><?php echo $this->escape(amuj_fa($place)); ?></span>
                        <small class="az-muted"><?php echo amuj_fa(implode(' | ', array_filter([
                            $a->address_post_code ? 'کد پستی ' . $this->escape($a->address_post_code) : '',
                            $this->escape($a->address_telephone),
                        ]))); ?></small>
                    </span>
                </span>
                <span class="az-co-address-actions">
                    <a href="#" onclick="return window.checkout.editAddress(<?php echo $step; ?>, <?php echo $pos; ?>, <?php echo $id; ?>);">ویرایش</a>
                    <?php if ($id !== $selected) : ?>
                        <a href="#" onclick="return confirm('این آدرس حذف شود؟') && window.checkout.deleteAddress(<?php echo $step; ?>, <?php echo $pos; ?>, <?php echo $id; ?>);">حذف</a>
                    <?php endif; ?>
                </span>
            </label>
        <?php endforeach; ?>
        <input type="hidden" name="checkout[address][shipping]" value="<?php echo $selected ?: ''; ?>" data-az-ship-address>
        <button type="button" class="az-btn az-btn--ghost az-co-address-new" onclick="return window.checkout.newAddress(<?php echo $step; ?>, <?php echo $pos; ?>, 'billing');">+ افزودن آدرس جدید</button>
    </div>
<?php if (empty($this->ajax)) : ?>
</div>
<script type="text/javascript">
if(!window.checkout) window.checkout = {};
window.Oby.registerAjax(['checkout.user.updated','checkout.address.updated'], function(params){
	window.checkout.refreshAddress(<?php echo $step; ?>, <?php echo $pos; ?>);
});
window.checkout.refreshAddress = function(step, id) { return window.checkout.refreshBlock('address', step, id); };
window.checkout.submitAddress = function(step, id) { return window.checkout.submitBlock('address', step, id); };
window.checkout.editAddress = function(step, id, addr) {
	window.checkout.submitBlock('address', step, id, {'checkout[address][edit]':addr});
	return false;
};
window.checkout.deleteAddress = function(step, id, addr) {
	window.checkout.submitBlock('address', step, id, {'checkout[address][delete]':addr});
	return false;
};
window.checkout.newAddress = function(step, id, type) {
	window.checkout.submitBlock('address', step, id, {'checkout[address][new]':type});
	return false;
};
</script>
<?php endif;
