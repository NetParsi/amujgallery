<?php
defined('_JEXEC') or die;

$products = amuj_products();
$items    = [1 => ['size' => 'S', 'qty' => 1], 4 => ['size' => 'M', 'qty' => 1]];
$total    = 0;
?>
<div class="az-cart">
    <ul class="az-tabs">
        <li><a class="is-active" href="<?php echo amuj_url('cart'); ?>">سبد خرید (<?php echo amuj_fa(count($items)); ?>)</a></li>
        <li><a href="#">علاقه‌مندی‌ها</a></li>
    </ul>

    <div class="az-cart-items">
        <?php foreach ($items as $id => $item) : $p = $products[$id]; $total += $p['price'] * $item['qty']; ?>
            <div class="az-cart-item">
                <a href="<?php echo amuj_url('product', ['id' => $id]); ?>"><img src="<?php echo amuj_img($p['img'], 600); ?>" alt=""></a>
                <div class="az-meta">
                    <div><?php echo $p['name']; ?></div>
                    <div><?php echo amuj_price($p['price']); ?></div>
                    <div class="az-muted">مشکی | <?php echo $item['size']; ?></div>
                    <div class="az-qty"><button type="button" data-step="-">−</button><span data-n="<?php echo $item['qty']; ?>"><?php echo amuj_fa($item['qty']); ?></span><button type="button" data-step="+">+</button></div>
                    <a class="az-remove">حذف</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <section class="az-section" style="padding-left:0;padding-right:0">
        <div class="az-section-head"><h2>شاید بپسندید</h2></div>
        <div class="az-row-slider" uk-slider="finite: true">
            <ul class="uk-slider-items">
                <?php foreach (array_slice($products, 4, null, true) as $id => $p) : ?>
                    <li><?php amuj_card($id, $p); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</div>

<div class="az-cart-bar">
    <div>
        <div class="az-total">جمع کل: <?php echo amuj_price($total); ?></div>
        <div class="az-small az-muted">* ارسال رایگان برای خریدهای بالای ۳ میلیون تومان</div>
    </div>
    <a class="az-btn" href="<?php echo amuj_url('checkout'); ?>">ادامه</a>
</div>
