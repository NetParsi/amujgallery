<?php
/**
 * Products as a 4-column grid of Amuj cards; the module title is the section heading.
 * Other layout: slider.php (one swipeable row).
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
    <div class="az-grid">
        <?php foreach ($cards as $id => $p) : amuj_card($id, $p); endforeach; ?>
    </div>
</section>
