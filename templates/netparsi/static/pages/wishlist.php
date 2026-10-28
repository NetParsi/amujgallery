<?php
defined('_JEXEC') or die;

/* Wishlist. Dynamic: HikaShop wishlist (Essential/Business). */
$products = amuj_products();
$saved    = [3 => $products[3], 2 => $products[2], 13 => $products[13]];

amuj_account_open('wishlist');
?>
<h1 class="az-account-title">علاقه‌مندی‌ها <span class="az-muted az-small" data-az-wish-count>(<?php echo amuj_fa(count($saved)); ?> کالا)</span></h1>

<div class="az-grid az-wish-grid" data-az-wishlist>
    <?php foreach ($saved as $id => $p) : ?>
        <div class="az-wish-item">
            <?php amuj_card($id, $p); ?>
            <button class="az-wish-remove" type="button" aria-label="حذف از علاقه‌مندی‌ها">حذف</button>
        </div>
    <?php endforeach; ?>
</div>
<div class="az-wish-empty" hidden>
    <p>هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید.</p>
    <a class="az-btn" href="<?php echo amuj_url('category'); ?>">مشاهده محصولات</a>
</div>
<?php amuj_account_close(); ?>
