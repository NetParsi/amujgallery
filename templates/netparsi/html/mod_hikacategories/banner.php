<?php
/**
 * Full-width banner per category: category image, name as title, description (plain text) as the link text.
 */
defined('_JEXEC') or die;

if (empty($categories)) {
    return;
}

require __DIR__ . '/_amuj.php';
?>
<?php foreach ($categories as $category) :
    $text = trim(strip_tags($category->description)) ?: 'مشاهده کالکشن'; ?>
    <section class="az-section">
        <a class="az-wide-banner" href="<?php echo $e(amuj_category_url($category->id, $category->link)); ?>">
            <?php if ($category->image) : ?><img src="<?php echo $e($category->image); ?>" alt="" loading="lazy"><?php endif; ?>
            <div>
                <h3><?php echo $e($category->name); ?></h3>
                <span class="az-link"><?php echo $e($text); ?></span>
            </div>
        </a>
    </section>
<?php endforeach; ?>
