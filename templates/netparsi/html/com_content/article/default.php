<?php
/**
 * Text page (terms, shipping, returns, privacy, ...): side menu + article.
 * Each <h2> of the article text starts a section. Other layouts: about.php, faq.php (article option "Layout").
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$item     = $this->item;
$sections = amuj_split_headings($item->text);
?>
<div class="az-policy">
    <aside class="az-policy-side">
        <?php amuj_policy_menu($item->alias); ?>
    </aside>

    <article class="az-policy-body">
        <span class="az-num">قوانین و راهنما</span>
        <h1><?php echo $this->escape($item->title); ?></h1>
        <p class="az-muted az-small">آخرین به‌روزرسانی: <?php echo amuj_jalali_month($item->modified ?: $item->created); ?></p>
        <?php if ($sections) : ?>
            <?php foreach ($sections as $i => [$heading, $body]) : ?>
                <section style="--i: <?php echo $i; ?>">
                    <h2><?php echo $heading; ?></h2>
                    <?php echo $body; ?>
                </section>
            <?php endforeach; ?>
        <?php else : ?>
            <section style="--i: 0"><?php echo $item->text; ?></section>
        <?php endif; ?>
        <div class="az-policy-help">
            سؤال دیگری دارید؟ <a class="az-link" href="<?php echo amuj_url('contact'); ?>">با ما در تماس باشید</a>
        </div>
    </article>
</div>
