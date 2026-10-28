<?php
/**
 * Full-width banner per category: category image, name as title, description (plain text) as the link text.
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
