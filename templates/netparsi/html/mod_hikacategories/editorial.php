<?php
/**
 * Two (or more) large category tiles side by side: category image + name, linking to its shop page.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

if (empty($categories)) {
    return;
}

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
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
