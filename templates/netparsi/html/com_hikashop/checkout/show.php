<?php
/**
 * HikaShop checkout in the Amuj design. The workflow blocks keep HikaShop's own markup and AJAX;
 * this layout only places them: address / shipping / payment / extra fields on one side, the order
 * summary (cart, club wallet, coupon, finish button) in the side column.
 * Guests are sent to the mobile login (com_auth) and come back here.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$app = Factory::getApplication();
if ($app->getIdentity()->guest) {
    $app->redirect(amuj_login_url());
    return;
}

Factory::getDocument()->setTitle('تکمیل سفارش');

$contents = $this->workflow['steps'][$this->workflow_step]['content'];
if (in_array('end', array_column($contents, 'task'), true)) {
    // Payment hand-off page: HikaShop's own layout inside the Amuj frame
    echo '<div class="az-co-end">';
    require JPATH_ROOT . '/components/com_hikashop/views/checkout/tmpl/show.php';
    echo '</div>';
    return;
}
$lastStep = ($this->step + 1) == count($this->workflow['steps']);
$side     = ['cart', 'plg.shop.club', 'coupon', 'terms'];
$titles   = ['address' => 'آدرس تحویل', 'shipping' => 'روش ارسال', 'payment' => 'روش پرداخت', 'fields' => 'توضیحات سفارش'];

$main = $aside = '';
$handleEnter = [];
$last = 0;
foreach ($contents as $k => $content) {
    $task = $content['task'];
    $handleEnter[] = 'window.checkout.handleEnter(\'' . $task . '\',' . $this->step . ',' . $k . ');';
    // Plugin blocks (club wallet) echo their markup instead of returning it
    ob_start();
    $html = $this->displayBlock($task, $k, @$content['params']);
    $html = ob_get_clean() . $html;
    $last = $k;
    $visible = preg_replace('/<input[^>]+type="hidden"[^>]*>|<script.*?<\/script>/is', '', $html);
    if (trim(strip_tags($visible, '<input><select><textarea><img><button>')) === '') {
        // Empty block (e.g. login for a signed-in customer): keep its markup for HikaShop's AJAX, no heading
        $main .= $html;
        continue;
    }
    if (in_array($task, $side, true)) {
        $aside .= '<div class="az-co-block az-co-' . preg_replace('/[^a-z]/', '', $task) . (isset($content['params']['az_part']) ? '-' . $content['params']['az_part'] : '') . '">' . $html . '</div>';
    } else {
        $main .= '<section class="az-co-section az-co-' . preg_replace('/[^a-z]/', '', $task) . '">'
            . (isset($titles[$task]) ? '<h2 class="az-title">' . $titles[$task] . '</h2>' : '') . $html . '</section>';
    }
}
?>
<form action="<?php echo $this->checkoutHelper->completeLink('cid=' . $this->step, false, false, false, $this->itemid); ?>" method="post" id="hikashop_checkout_form" name="hikashop_checkout_form" enctype="multipart/form-data" onsubmit="if(window.checkout.onFormSubmit){ return window.checkout.onFormSubmit(this); }">
<input type="hidden" name="task" value="submitstep"/>
<input type="hidden" name="<?php echo hikashop_getFormToken(); ?>" id="hikashop_checkout_token" value="1"/>
<input type="hidden" name="cart_id" value="<?php echo $this->cart_id; ?>"/>
<input type="submit" style="display:none;"/>
<div id="hikashop_checkout" data-checkout-step="<?php echo $this->step; ?>" class="hikashop_checkout_page hikashop_checkout_page_step<?php echo $this->step; ?> az-checkout az-co-live">
    <div class="hikashop_checkout_loading_elem"></div>
    <div class="hikashop_checkout_loading_spinner"></div>

    <div class="az-co-main">
        <div class="az-steps">
            <a href="<?php echo hikashop_completeLink('cart&task=show'); ?>">۱. سبد خرید</a>
            <span class="is-active">۲. ارسال و پرداخت</span>
            <span>۳. ثبت سفارش</span>
        </div>
        <?php
        if (!empty($this->extraData['checkout']) && !empty($this->extraData['checkout']->checkout_top)) {
            echo implode("\r\n", $this->extraData['checkout']->checkout_top);
        }
        echo $main;
        if (!empty($this->extraData['checkout']) && !empty($this->extraData['checkout']->checkout_bottom)) {
            echo implode("\r\n", $this->extraData['checkout']->checkout_bottom);
        }
        ?>
    </div>

    <aside class="az-summary az-co-summary">
        <h2 class="az-title">خلاصه سفارش</h2>
        <?php echo $aside; ?>
        <button id="hikabtn_checkout_next" type="submit" class="az-btn az-btn--block uk-margin-top hikabtn_checkout_next" onclick="return window.checkout.submitStep(this);"><?php
            echo $lastStep ? 'ثبت سفارش و پرداخت' : 'ادامه';
        ?></button>
        <p class="az-small az-muted uk-margin-small-top">با ثبت سفارش، <a class="az-link" href="<?php echo amuj_url('policy', ['doc' => 'terms']); ?>">قوانین و مقررات</a> را می‌پذیرید.</p>
    </aside>
    <?php
    if (!empty($this->extra_data) && !isset($this->extraData['checkout'])) {
        echo implode("\r\n", $this->extra_data);
    }
    ?>
</div>
</form>
<?php
if ($this->emptyBlocksCount == count($contents)) {
    $handleEnter = ['window.checkout.submitStep(document.getElementById(\'hikabtn_checkout_next\'));'];
}
$doc = Factory::getDocument();
$doc->addScript(HIKASHOP_JS . 'checkout.js');
$js = '';
if ($lastStep) {
    $js .= 'window.checkout.loadingMessage = ' . json_encode('در حال ثبت سفارش...') . ";\n";
}
$js .= '
window.checkout.token = "' . hikashop_getFormToken() . '";
window.checkout.urls.show = "' . hikashop_completeLink('checkout&task=showblock' . $this->cartIdParam . '&Itemid=' . $this->itemid, 'ajax', false, true) . '";
window.checkout.urls.submit = "' . hikashop_completeLink('checkout&task=submitblock' . $this->cartIdParam . '&Itemid=' . $this->itemid, 'ajax', false, true) . '";
window.checkout.urls.submitstep = "' . hikashop_completeLink('checkout&task=submitstep' . $this->cartIdParam . '&Itemid=' . $this->itemid, 'ajax', false, true) . '";
window.Oby.registerAjax("checkout.step.completed",function(params){ var f = document.getElementById("hikashop_checkout_form"); if(!f || f.submit_in_progress) return; f.submit_in_progress = true; f.submit(); });
window.Oby.registerAjax("cart.empty",function(params){ setTimeout(function(){ window.location.reload(); },150); });
window.Oby.registerAjax("cart.updated",function(params){ if(!params || !params.resp || !params.resp.empty) return; window.Oby.fireAjax("cart.empty",null); });
window.hikashop.ready(function(){
	' . implode("\r\n\t", $handleEnter) . '
});
';
$doc->addScriptDeclaration($js);
