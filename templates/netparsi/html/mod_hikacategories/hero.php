<?php
/**
 * Home slideshow: one slide per category. Category images (first three) side by side,
 * category name = tab and title, description = caption text, button = the category's shop page.
 */
defined('_JEXEC') or die;

if (empty($categories)) {
    return;
}

require __DIR__ . '/_amuj.php';
// Slide duration (ms): the line under the active tab fills over this time, then the next slide shows (netparsi.js)
$slideDuration = 5000;
?>
<section class="az-hero" uk-slideshow="animation: fade; autoplay: false; finite: false" data-az-duration="<?php echo $slideDuration; ?>" style="--az-dur: <?php echo $slideDuration; ?>ms">
    <ul class="uk-slideshow-items">
        <?php foreach ($categories as $category) : ?>
            <li class="az-hero-shade">
                <div class="az-hero-split">
                    <?php foreach (array_slice($category->images, 0, 3) as $img) : ?><img src="<?php echo $e($img->url); ?>" alt=""><?php endforeach; ?>
                </div>
                <div class="az-hero-caption">
                    <h2><?php echo $e($category->name); ?></h2>
                    <?php if (trim(strip_tags($category->description)) !== '') : ?><p><?php echo $e(trim(strip_tags($category->description))); ?></p><?php endif; ?>
                    <a class="az-btn" href="<?php echo $e(amuj_category_url($category->id, $category->link)); ?>">مشاهده کالکشن</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <button class="az-hero-nav az-hero-nav--prev" type="button" uk-slideshow-item="previous" aria-label="اسلاید قبلی">
        <svg viewBox="0 0 24 48" aria-hidden="true"><path d="M6 2l14 22L6 46"/></svg>
    </button>
    <button class="az-hero-nav az-hero-nav--next" type="button" uk-slideshow-item="next" aria-label="اسلاید بعدی">
        <svg viewBox="0 0 24 48" aria-hidden="true"><path d="M18 2L4 24l14 22"/></svg>
    </button>
    <ul class="az-hero-tabs">
        <?php foreach ($categories as $i => $category) : ?>
            <li uk-slideshow-item="<?php echo $i; ?>">
                <a href="#"><?php echo $e($category->name); ?></a>
                <span class="az-hero-progress" aria-hidden="true"><i></i></span>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
