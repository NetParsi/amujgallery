<?php
/**
 * Discount code box of the checkout summary (Amuj design, HikaShop coupon AJAX).
 */
defined('_JEXEC') or die;

$cart = $this->checkoutHelper->getCart();
$id   = $this->step . '_' . $this->module_position;
?>
<?php if (empty($this->ajax)) : ?>
<div id="hikashop_checkout_coupon_<?php echo $id; ?>" data-checkout-step="<?php echo $this->step; ?>" data-checkout-pos="<?php echo $this->module_position; ?>" class="hikashop_checkout_coupon">
<?php endif; ?>
    <div class="hikashop_checkout_loading_elem"></div>
    <div class="hikashop_checkout_loading_spinner"></div>
    <strong class="az-co-block-title">کد تخفیف</strong>
    <?php if (empty($cart->coupon)) : ?>
        <div class="az-co-coupon">
            <input class="az-input hikashop_checkout_coupon_field" id="hikashop_checkout_coupon_input_<?php echo $id; ?>" type="text" name="checkout[coupon]" value="" placeholder="کد تخفیف را وارد کنید" autocomplete="off">
            <button class="az-btn az-btn--ghost" type="submit" onclick="return window.checkout.submitCoupon(<?php echo $this->step . ',' . $this->module_position; ?>);">اعمال</button>
        </div>
    <?php else : ?>
        <div class="az-co-coupon-set">
            <span>کد <strong><?php echo $this->escape($cart->coupon->discount_code); ?></strong> اعمال شد</span>
            <?php if (empty($cart->cart_params->coupon_autoloaded)) : ?>
                <button type="button" class="az-remove" onclick="return window.checkout.removeCoupon(<?php echo $this->step . ',' . $this->module_position; ?>);">حذف</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="az-co-msg"><?php $this->checkoutHelper->displayMessages('coupon'); ?></div>
<?php if (empty($this->ajax)) : ?>
</div>
<script type="text/javascript">
if(!window.checkout) window.checkout = {};
window.Oby.registerAjax(['checkout.coupon.updated','cart.updated', 'checkout.cart.updated'], function(params){
	if(params && (params.cart_empty || (params.resp && params.resp.empty))) return;
	if(window.checkout.isSource(params, <?php echo (int) $this->step; ?>, <?php echo (int) $this->module_position; ?>))
		return;
	window.checkout.refreshCoupon(<?php echo (int) $this->step; ?>, <?php echo (int) $this->module_position; ?>);
});
window.checkout.refreshCoupon = function(step, id) { return window.checkout.refreshBlock('coupon', step, id); };
window.checkout.submitCoupon = function(step, id) {
	var el = document.getElementById('hikashop_checkout_coupon_input_' + step + '_' + id);
	if(!el)
		return false;
	if(el.value == '') {
		window.Oby.addClass(el, 'hikashop_red_border');
		return false;
	}
	return window.checkout.submitBlock('coupon', step, id);
};
window.checkout.removeCoupon = function(step, id) {
	window.checkout.submitBlock('coupon', step, id, {'checkout[removecoupon]':1});
	return false;
};
</script>
<?php endif;
