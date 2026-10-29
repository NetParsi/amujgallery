<?php
/**
 * HikaShop cart page (menu item "سبد خرید" in the hidden menu amuj-pages) in the Amuj design.
 * Quantity / remove post to HikaShop (updatecart) and reload. Wishlists keep HikaShop's own layout.
 * Totals are HikaShop's; shipping is calculated at checkout.
 */
defined('_JEXEC') or die;

// Menu item "wishlist" (amuj-pages, cart type wishlist): the customer panel's wishlist page
$amujMenu = \Joomla\CMS\Factory::getApplication()->getMenu()->getActive();
if ($amujMenu && $amujMenu->getParams()->get('cart_type') === 'wishlist'
    && !in_array(\Joomla\CMS\Factory::getApplication()->getInput()->getCmd('tmpl'), ['component', 'raw', 'ajax'], true)) {
    require __DIR__ . '/_wishlist.php';
    return;
}

if ((!empty($this->cart->cart_type) && $this->cart->cart_type !== 'cart')
    || in_array(\Joomla\CMS\Factory::getApplication()->getInput()->getCmd('tmpl'), ['component', 'raw', 'ajax'], true)) {
    require JPATH_ROOT . '/components/com_hikashop/views/cart/tmpl/show.php';
    return;
}

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$db = Factory::getDbo();

// Cart lines (top-level products only; options belong to their parent line)
$lines = [];
if (!empty($this->cart->cart_id)) {
    $lines = $db->setQuery('SELECT cp.cart_product_id, cp.product_id, cp.cart_product_quantity, p.product_parent_id'
        . ' FROM ' . hikashop_table('cart_product') . ' cp JOIN ' . hikashop_table('product') . ' p ON p.product_id = cp.product_id'
        . ' WHERE cp.cart_id = ' . (int) $this->cart->cart_id . ' AND cp.cart_product_option_parent_id = 0 AND cp.cart_product_quantity > 0'
        . ' ORDER BY cp.cart_product_id')->loadObjectList();
}

// Prices calculated by HikaShop (discounts, taxes) per cart line
$hikaPrices = [];
foreach ((array) ($this->cart->products ?? []) as $cp) {
    if (!empty($cp->cart_product_id) && !empty($cp->prices)) {
        $first = reset($cp->prices);
        $hikaPrices[$cp->cart_product_id] = (float) ($first->unit_price->price_value_with_tax ?? $first->price_value_with_tax ?? 0);
    }
}

$mains = amuj_hika_products(array_map(function ($l) { return (int) ($l->product_parent_id ?: $l->product_id); }, $lines));

$items = [];
$count = 0;
$total = 0;
foreach ($lines as $l) {
    $mainId = (int) ($l->product_parent_id ?: $l->product_id);
    if (!isset($mains[$mainId])) {
        continue;
    }
    $p = $mains[$mainId];
    $color = $size = '';
    $img = $p['img'];
    foreach ($p['variants'] as $key => $v) {
        if ((int) $v[0] === (int) $l->product_id) {
            [$cid, $sid] = explode('|', $key);
            $ci = array_search((int) $cid, $p['color_ids']);
            $si = array_search((int) $sid, $p['size_ids']);
            $color = $ci !== false ? $p['color_names'][$ci] : '';
            $size  = $si !== false ? $p['sizes'][$si] : '';
            $img   = $p['color_images'][(int) $cid] ?? $img;
            break;
        }
    }
    $qty   = (int) $l->cart_product_quantity;
    $unit  = $hikaPrices[$l->cart_product_id] ?? $p['price'];
    $items[] = ['line' => (int) $l->cart_product_id, 'name' => $p['name'], 'url' => $p['url'], 'img' => $img,
        'color' => $color, 'size' => $size, 'qty' => $qty, 'price' => $unit * $qty];
    $count += $qty;
    $total += $unit * $qty;
}
if (!empty($this->cart->full_total->prices)) {
    $ft = reset($this->cart->full_total->prices);
    $total = (float) ($ft->price_value_with_tax ?? $total) ?: $total;
}

$free      = amuj_free_shipping();
$remaining = max(0, $free - $total);
$progress  = $free ? min(100, round($total / $free * 100)) : 100;
$checkout  = amuj_url('checkout'); // hidden menu item "checkout" (clean URL)

// Suggestions: newest products that are not in the cart
$suggest = $db->setQuery('SELECT product_id FROM ' . hikashop_table('product') . ' WHERE product_type = "main" AND product_published = 1'
    . ($mains ? ' AND product_id NOT IN (' . implode(',', array_keys($mains)) . ')' : '') . ' ORDER BY product_created DESC LIMIT 8')->loadColumn();
$suggest = amuj_hika_products($suggest);

if (!$items) :
    // Our empty state says it already: drop HikaShop's "cart is empty" notice, keep any other message
    $app = Factory::getApplication();
    $emptyText = \Joomla\CMS\Language\Text::_('CART_EMPTY');
    foreach ($app->getMessageQueue(true) as $msg) {
        if ($msg['message'] !== $emptyText) {
            $app->enqueueMessage($msg['message'], $msg['type']);
        }
    }
    ?>
    <div class="az-thanks az-state">
        <div class="az-status-icon" aria-hidden="true">
            <svg viewBox="0 0 96 96">
                <circle class="az-status-ring" cx="48" cy="48" r="44"/>
                <path class="az-status-mark" pathLength="70" d="M32 40h32l-3 27H35z"/>
                <path class="az-status-mark az-status-mark--late" pathLength="70" d="M40 40v-5a8 8 0 0 1 16 0v5"/>
            </svg>
        </div>
        <h1>سبد خرید شما خالی است</h1>
        <p class="az-muted">کالاهایی که به سبد اضافه کنید اینجا نمایش داده می‌شوند. از جدیدترین‌های کالکشن دیدن کنید.</p>
        <div class="az-thanks-actions">
            <a class="az-btn" href="<?php echo Uri::root(); ?>">مشاهده محصولات</a>
            <a class="az-btn az-btn--ghost" href="<?php echo amuj_url('wishlist'); ?>">علاقه‌مندی‌های من</a>
        </div>
    </div>
    <?php if ($suggest) : ?>
        <div class="az-cart-more">
            <section class="az-section">
                <div class="az-section-head"><h2>جدیدترین‌ها</h2></div>
                <div class="az-grid">
                    <?php foreach (array_slice($suggest, 0, 4, true) as $id => $sp) : amuj_card($id, $sp); endforeach; ?>
                </div>
            </section>
        </div>
    <?php endif; ?>
    <?php return;
endif;
?>
<div class="az-checkout az-cart" data-az-cart-page>
    <div class="az-cart-main">
        <div class="az-steps">
            <span class="is-active">۱. سبد خرید</span>
            <span>۲. ارسال</span>
            <span>۳. پرداخت</span>
        </div>

        <h1 class="az-title az-cart-title">سبد خرید <span class="az-muted">(<?php echo amuj_fa($count); ?> کالا)</span></h1>

        <div class="az-cart-list">
            <?php foreach ($items as $item) : ?>
                <div class="az-cart-row" data-az-line="<?php echo $item['line']; ?>">
                    <a class="az-cart-row-img" href="<?php echo $item['url']; ?>"><img src="<?php echo amuj_img($item['img']); ?>" alt=""></a>
                    <div class="az-cart-row-info">
                        <a class="az-cart-row-name" href="<?php echo $item['url']; ?>"><?php echo $this->escape($item['name']); ?></a>
                        <span class="az-muted"><?php echo implode(' | ', array_filter([$item['color'], $item['size'] ? 'سایز ' . $item['size'] : ''])); ?></span>
                        <div class="az-cart-row-actions">
                            <div class="az-qty"><button type="button" data-step="-" aria-label="کم کردن">−</button><span data-n="<?php echo $item['qty']; ?>"><?php echo amuj_fa($item['qty']); ?></span><button type="button" data-step="+" aria-label="زیاد کردن">+</button></div>
                            <button class="az-remove" type="button" data-az-remove>حذف</button>
                        </div>
                    </div>
                    <strong class="az-cart-row-price"><?php echo amuj_price($item['price']); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <aside class="az-summary az-cart-summary">
        <h2 class="az-title">خلاصه سبد</h2>

        <div class="az-freeship<?php echo $remaining ? '' : ' is-done'; ?>">
            <p>
                <?php if ($remaining) : ?>
                    <strong><?php echo amuj_price($remaining); ?></strong> تا ارسال رایگان باقی مانده
                <?php else : ?>
                    ارسال این سفارش <strong>رایگان</strong> است
                <?php endif; ?>
            </p>
            <div class="az-club-bar"><span style="--p: <?php echo $progress; ?>%"></span></div>
        </div>

        <div class="az-summary-row"><span>جمع کالاها (<?php echo amuj_fa($count); ?>)</span><span><?php echo amuj_price($total); ?></span></div>
        <div class="az-summary-row">
            <span>هزینه ارسال</span>
            <span class="az-muted"><?php echo $remaining ? 'وابسته به آدرس' : 'رایگان'; ?></span>
        </div>
        <div class="az-summary-row is-total"><span>جمع کل</span><span><?php echo amuj_price($total); ?></span></div>

        <a class="az-btn az-btn--block az-cart-next" href="<?php echo $checkout; ?>">ادامه فرایند خرید</a>
        <p class="az-small az-muted uk-margin-small-top">هزینه ارسال نهایی پس از ثبت آدرس در مرحله بعد محاسبه می‌شود.</p>
    </aside>
</div>

<?php if ($suggest) : ?>
    <div class="az-cart-more">
        <section class="az-section">
            <div class="az-section-head"><h2>شاید بپسندید</h2></div>
            <div class="az-row-slider" uk-slider="finite: true">
                <ul class="uk-slider-items">
                    <?php foreach ($suggest as $id => $sp) : ?>
                        <li><?php amuj_card($id, $sp); ?></li>
                    <?php endforeach; ?>
                </ul><?php amuj_slider_nav(); ?>
            </div>
        </section>
    </div>
<?php endif; ?>

<!-- Mobile: total and continue button stay pinned to the bottom -->
<div class="az-cart-bar">
    <div>
        <div class="az-total">جمع کل: <span><?php echo amuj_price($total); ?></span></div>
        <div class="az-small az-muted"><?php echo $remaining ? amuj_price($remaining) . ' تا ارسال رایگان' : 'ارسال رایگان'; ?></div>
    </div>
    <a class="az-btn" href="<?php echo $checkout; ?>">ادامه</a>
</div>
