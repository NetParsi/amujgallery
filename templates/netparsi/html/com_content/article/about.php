<?php
/**
 * About page. Article: full-text image = hero, intro image = store photo;
 * text = first paragraph (lead), next paragraphs (two columns), then <h3> + text per value card.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$item   = $this->item;
$images = json_decode($item->images ?: '{}');
$hero   = !empty($images->image_fulltext) ? strtok($images->image_fulltext, '#') : 'images/demo-product/harir-green.jpg';
$store  = !empty($images->image_intro) ? strtok($images->image_intro, '#') : 'images/demo-product/harir-white.jpg';

// Intro paragraphs come before the first <h3>; values after it
$intro  = preg_split('#<h3[^>]*>#i', (string) $item->text, 2)[0];
preg_match_all('#<p[^>]*>(.*?)</p>#is', $intro, $m);
$paras  = $m[1];
$lead   = array_shift($paras);
$values = amuj_split_headings($item->text, 'h3');
?>
<section class="az-about-hero">
    <img src="<?php echo amuj_img($hero, 1920); ?>" alt="">
    <div class="az-about-hero-text">
        <span><?php echo $this->escape($item->title); ?></span>
        <h1>گالری آموژ</h1>
    </div>
</section>

<section class="az-about-intro">
    <?php if ($lead) : ?><p class="az-about-lead"><?php echo $lead; ?></p><?php endif; ?>
    <?php if ($paras) : ?>
        <div class="az-about-cols">
            <?php foreach ($paras as $para) : ?><p><?php echo $para; ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if ($values) : ?>
    <section class="az-about-values">
        <?php foreach ($values as $i => [$title, $text]) : ?>
            <div class="az-about-value">
                <span class="az-num">|<?php echo amuj_fa(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?>|</span>
                <h3><?php echo $title; ?></h3>
                <?php echo $text; ?>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<section class="az-about-split">
    <div class="az-about-split-img"><img src="<?php echo amuj_img($store, 1200); ?>" alt="" loading="lazy"></div>
    <div class="az-about-split-text">
        <span class="az-num">فروشگاه حضوری</span>
        <h2>از نزدیک ببینید و امتحان کنید</h2>
        <p>تهران، ولنجک، خیابان چهاردهم، پلاک ۱۹، برج هوما، طبقه ۹، واحد ۹۰۴</p>
        <p class="az-muted">برای هماهنگی بازدید یا مشاوره سایز، از طریق تلفن یا واتس‌اپ با ما در تماس باشید.</p>
        <div class="az-about-actions">
            <a class="az-btn" href="<?php echo amuj_url('contact'); ?>">تماس و مسیریابی</a>
            <a class="az-btn az-btn--ghost" href="<?php echo amuj_url('category'); ?>">مشاهده کالکشن</a>
        </div>
    </div>
</section>
