<?php
/**
 * FAQ page: each <h2> of the article text is a question, the text under it the answer.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$item   = $this->item;
$topics = [
    ['سفارش‌های من', 'پیگیری وضعیت و جزئیات سفارش', amuj_url('orders')],
    ['ارسال و تحویل', 'روش‌ها، زمان و هزینه ارسال', amuj_url('policy', ['doc' => 'shipping'])],
    ['تعویض و مرجوعی', 'شرایط و مراحل بازگرداندن کالا', amuj_url('policy', ['doc' => 'returns'])],
    ['خرید و پرداخت', 'روش‌های پرداخت و کد تخفیف', amuj_url('policy', ['doc' => 'terms'])],
];
?>
<div class="az-policy">
    <aside class="az-policy-side">
        <?php amuj_policy_menu('faq'); ?>
    </aside>

    <article class="az-policy-body">
        <span class="az-num">قوانین و راهنما</span>
        <h1><?php echo $this->escape($item->title); ?></h1>
        <p class="az-muted az-small">پاسخ رایج‌ترین سؤال‌ها درباره خرید از گالری آموژ</p>

        <div class="az-help-grid">
            <?php foreach ($topics as [$title, $text, $url]) : ?>
                <a href="<?php echo $url; ?>">
                    <strong><?php echo $title; ?></strong>
                    <span><?php echo $text; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="az-faq">
            <?php foreach (amuj_split_headings($item->text) as [$q, $a]) : ?>
                <details class="az-faq-item">
                    <summary><?php echo $q; ?><span class="az-faq-icon" aria-hidden="true"></span></summary>
                    <?php echo $a; ?>
                </details>
            <?php endforeach; ?>
        </div>

        <div class="az-policy-help">
            پاسخ سؤالتان را پیدا نکردید؟ <a class="az-link" href="<?php echo amuj_url('contact'); ?>">با ما در تماس باشید</a>
        </div>
    </article>
</div>
