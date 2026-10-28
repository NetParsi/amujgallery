<?php
defined('_JEXEC') or die;

$products = amuj_products();
?>
<div class="az-search-box">
    <ul class="az-tabs">
        <li><a class="is-active" href="#">زنانه</a></li>
        <li><a href="#">مردانه</a></li>
        <li><a href="#">کودکان</a></li>
    </ul>
    <input class="az-input" type="search" placeholder="جستجوی محصول، دسته یا رنگ..." autofocus>
    <div class="az-search-tags">
        <?php foreach (['کت', 'پالتو', 'بافت', 'جین', 'پیراهن مشکی', 'بلیزر', 'دامن پلیسه', 'کیف'] as $tag) : ?>
            <a href="<?php echo amuj_url('category'); ?>"><?php echo $tag; ?></a>
        <?php endforeach; ?>
    </div>
</div>
<div class="az-cat-body">
    <div class="az-cat-title">پیشنهاد برای شما</div>
    <div class="az-grid" id="az-grid">
        <?php foreach ($products as $id => $p) : ?>
            <?php amuj_card($id, $p); ?>
        <?php endforeach; ?>
    </div>
</div>
