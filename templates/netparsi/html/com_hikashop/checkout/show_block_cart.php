<?php
/**
 * Checkout order summary in the Amuj design: cart lines (photo, colour / size, quantity, price) and the
 * totals HikaShop calculated (coupon, shipping, club wallet and other additional lines, payable amount).
 * Quantities are changed on the cart page; the block refreshes itself through HikaShop's AJAX events.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$cart = $this->checkoutHelper->getCart();

$lines = [];
foreach ((array) ($cart->products ?? []) as $cp) {
    if (empty($cp->cart_product_quantity) || !empty($cp->cart_product_option_parent_id)) {
        continue;
    }
    $first = !empty($cp->prices) ? reset($cp->prices) : null;
    $lines[] = [
        'id'    => (int) $cp->product_id,
        'main'  => (int) (!empty($cp->product_parent_id) ? $cp->product_parent_id : $cp->product_id),
        'qty'   => (int) $cp->cart_product_quantity,
        'total' => (float) ($first->price_value_with_tax ?? 0),
    ];
}
$mains = amuj_hika_products(array_unique(array_column($lines, 'main')));

$sum = function ($prices) {
    $p = is_array($prices) ? reset($prices) : null;
    return $p ? (float) $p->price_value_with_tax : 0;
};
$subtotal = $sum($cart->total->prices ?? null);
$payable  = $sum($cart->full_total->prices ?? null);

$shippingCost = null;
$shippingName = [];
foreach ((array) ($cart->shipping ?? []) as $s) {
    $shippingCost = (float) $shippingCost + (float) ($s->shipping_price_with_tax ?? $s->shipping_price ?? 0);
    $shippingName[] = $s->shipping_name;
}
$coupon = !empty($cart->coupon) ? (float) ($cart->coupon->discount_value ?? 0) : 0;
?>
<?php if (empty($this->ajax)) : ?>
<div id="hikashop_checkout_cart_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" data-checkout-step="<?php echo $this->step; ?>" data-checkout-pos="<?php echo $this->module_position; ?>" class="hikashop_checkout_cart">
<?php endif; ?>
    <div class="hikashop_checkout_loading_elem"></div>
    <div class="hikashop_checkout_loading_spinner"></div>
    <?php $this->checkoutHelper->displayMessages('cart'); ?>

    <div class="az-co-items">
        <?php foreach ($lines as $l) :
            if (!isset($mains[$l['main']])) {
                continue;
            }
            $p = $mains[$l['main']];
            $img = $p['img'];
            $color = $size = '';
            foreach ($p['variants'] as $key => $v) {
                if ((int) $v[0] === $l['id']) {
                    [$cid, $sid] = explode('|', $key);
                    $ci = array_search((int) $cid, $p['color_ids']);
                    $si = array_search((int) $sid, $p['size_ids']);
                    $color = $ci !== false ? $p['color_names'][$ci] : '';
                    $size  = $si !== false ? $p['sizes'][$si] : '';
                    $img   = $p['color_images'][(int) $cid] ?? $img;
                    break;
                }
            }
            ?>
            <div class="az-summary-item">
                <img src="<?php echo amuj_img($img, 200); ?>" alt="">
                <div>
                    <div><?php echo $this->escape($p['name']); ?></div>
                    <div class="az-muted"><?php echo implode(' | ', array_filter([$color, $size ? 'سایز ' . $size : '', 'تعداد ' . amuj_fa($l['qty'])])); ?></div>
                    <div><?php echo amuj_price($l['total']); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
        <a class="az-link az-small" href="<?php echo hikashop_completeLink('cart&task=show'); ?>">ویرایش سبد خرید</a>
    </div>

    <div class="az-co-totals">
        <div class="az-summary-row"><span>جمع محصولات</span><span><?php echo amuj_price($subtotal); ?></span></div>
        <?php if ($coupon > 0) : ?>
            <div class="az-summary-row az-co-minus"><span>تخفیف <small class="az-muted">(<?php echo $this->escape($cart->coupon->discount_code); ?>)</small></span><span>− <?php echo amuj_price($coupon); ?></span></div>
        <?php endif; ?>
        <div class="az-summary-row">
            <span>هزینه ارسال<?php if ($shippingName) : ?> <small class="az-muted">(<?php echo $this->escape(implode('، ', $shippingName)); ?>)</small><?php endif; ?></span>
            <span><?php echo $shippingCost === null ? '<span class="az-muted">انتخاب نشده</span>' : ($shippingCost > 0 ? amuj_price($shippingCost) : 'رایگان'); ?></span>
        </div>
        <?php foreach ((array) ($cart->additional ?? []) as $key => $add) :
            $value = (float) ($add->price_value_with_tax ?? $add->price_value ?? 0);
            if (!$value) {
                continue;
            }
            $label = $key === 'club' ? 'کیف پول' : strip_tags(hikashop_translate($add->name));
            ?>
            <div class="az-summary-row<?php echo $value < 0 ? ' az-co-minus' : ''; ?>"><span><?php echo $this->escape($label); ?></span><span><?php echo ($value < 0 ? '− ' : '') . amuj_price(abs($value)); ?></span></div>
        <?php endforeach; ?>
        <div class="az-summary-row is-total"><span>مبلغ قابل پرداخت</span><span><?php echo $payable > 0 ? amuj_price($payable) : 'رایگان'; ?></span></div>
    </div>
<?php if (empty($this->ajax)) : ?>
</div>
<script type="text/javascript">
if(!window.checkout) window.checkout = {};
window.Oby.registerAjax(['checkout.cart.updated','cart.updated'], function(params){
	if(window.checkout.isSource(params, <?php echo (int) $this->step; ?>, <?php echo (int) $this->module_position; ?>))
		return;
	window.checkout.refreshCart(<?php echo (int) $this->step; ?>, <?php echo (int) $this->module_position; ?>);
});
window.checkout.refreshCart = function(step, id) { return window.checkout.refreshBlock('cart', step, id); };
window.checkout.submitCart = function(step, id) { return window.checkout.submitBlock('cart', step, id); };
</script>
<?php endif;
