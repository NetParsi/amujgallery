<?php
/**
 * Products in one swipeable row of Amuj cards; the module title is the section heading.
 */
defined('_JEXEC') or die;

if (empty($items)) {
    return;
}
require __DIR__ . '/_amuj.php';
?>
<section class="az-section">
    <div class="az-section-head">
        <h2><?php echo $heading; ?></h2>
        <?php if ($more) : ?><a href="<?php echo $more; ?>">مشاهده همه</a><?php endif; ?>
    </div>
    <div class="az-row-slider" uk-slider="finite: true">
        <ul class="uk-slider-items">
            <?php foreach ($cards as $id => $p) : ?>
                <li><?php amuj_card($id, $p); ?></li>
            <?php endforeach; ?>
        </ul><?php amuj_slider_nav(); ?>
    </div>
</section>
