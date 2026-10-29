<?php
/**
 * Customer club wallet box of the checkout (override of plugins/hikashop/club/club_checkout.php).
 * Ticking it subtracts the wallet balance (up to the order total) as the "club" additional line;
 * the club plugin withdraws it from the wallet once the order is confirmed.
 * Available: $use_credit ('0'|'1'), $userTotalCredit, $used_credit_amount, $this->step, $this->module_position.
 */
defined('_JEXEC') or die('Restricted access');

$id = (int) $this->step . '_' . (int) $this->module_position;
$on = $use_credit == '1';
$fa = function ($n) {
    return strtr(number_format((float) $n), ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', ',' => '٬']) . ' تومان';
};
?>
<?php if (!$this->ajax) : ?>
<div id="hikashop_checkout_plg-shop-club_<?php echo $this->step; ?>_<?php echo $this->module_position; ?>" class="hikashop_checkout_club">
<?php endif; ?>
    <div class="hikashop_checkout_loading_elem"></div>
    <div class="hikashop_checkout_loading_spinner small_spinner"></div>
    <?php if ($this->display) : ?>
        <label class="az-co-credit<?php echo $userTotalCredit > 0 ? '' : ' is-disabled'; ?>">
            <input type="checkbox" id="club_use_credit_<?php echo $id; ?>" <?php echo $on ? 'checked' : ''; ?> <?php echo $userTotalCredit > 0 || $on ? '' : 'disabled'; ?>
                onchange="return window.checkout.submitClubCredit(<?php echo (int) $this->step; ?>, <?php echo (int) $this->module_position; ?>, this.checked ? 1 : 0);">
            <span>
                <strong>پرداخت از کیف پول</strong>
                <small>
                    <?php if ($on) : ?>
                        <?php echo $fa($used_credit_amount); ?> از کیف پول کسر می‌شود
                    <?php elseif ($userTotalCredit > 0) : ?>
                        موجودی کیف پول: <?php echo $fa($userTotalCredit); ?>
                    <?php else : ?>
                        کیف پول شما فعلاً موجودی ندارد
                    <?php endif; ?>
                </small>
            </span>
        </label>
    <?php endif; ?>
<?php if (!$this->ajax) : ?>
</div>
<script type="text/javascript">
if (!window.checkout) window.checkout = {};
window.Oby.registerAjax(['checkout.cart.updated', 'cart.updated'], function (params) {
	if (window.checkout.isSource(params, <?php echo (int) $this->step; ?>, <?php echo (int) $this->module_position; ?>)) return;
	window.checkout.refreshClubCredit(<?php echo (int) $this->step; ?>, <?php echo (int) $this->module_position; ?>);
});
window.checkout.refreshClubCredit = function (step, id) { return window.checkout.refreshBlock('plg.shop.club', step, id); };
window.checkout.submitClubCredit = function (step, id, use) {
	return window.checkout.submitBlock('plg.shop.club', step, id, {'ajax': '1', 'club_use_credit': use});
};
</script>
<?php endif;
