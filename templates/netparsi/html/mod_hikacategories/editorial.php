<?php
/**
 * Two (or more) large category tiles side by side: category image + name, linking to its shop page.
 */
defined('_JEXEC') or die;

if (empty($categories)) {
    return;
}

require __DIR__ . '/_amuj.php';
?>
<section class="az-section">
    <div class="az-editorial">
        <?php foreach ($categories as $category) : ?>
            <a href="<?php echo $e(amuj_category_url($category->id, $category->link)); ?>">
                <?php if ($category->image) : ?><img src="<?php echo $e($category->image); ?>" alt="" loading="lazy"><?php endif; ?>
                <span><?php echo $e($category->name); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
