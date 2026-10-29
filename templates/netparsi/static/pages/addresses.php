<?php
defined('_JEXEC') or die;

$addresses = amuj_addresses();
$provinces = array_keys(amuj_shipping_rates());

amuj_account_open('addresses');
?>
<h1 class="az-account-title">آدرس‌های من</h1>

<div class="az-address-grid">
    <?php foreach ($addresses as $a) : ?>
        <div class="az-address<?php echo $a['default'] ? ' is-default' : ''; ?>">
            <div class="az-address-top">
                <strong><?php echo $a['label']; ?></strong>
                <?php if ($a['default']) : ?><span class="az-badge az-badge--done">پیش‌فرض</span><?php endif; ?>
            </div>
            <p><?php echo $a['province'] . '، ' . $a['city'] . '، ' . $a['address']; ?></p>
            <dl class="az-dl az-dl--compact">
                <dt>تحویل‌گیرنده</dt><dd><?php echo $a['name']; ?></dd>
                <dt>موبایل</dt><dd dir="ltr"><?php echo $a['phone']; ?></dd>
                <dt>کد پستی</dt><dd dir="ltr"><?php echo $a['postal']; ?></dd>
            </dl>
            <div class="az-address-actions">
                <button type="button" uk-toggle="target: #az-address-form">ویرایش</button>
                <?php if (!$a['default']) : ?><button type="button">انتخاب به‌عنوان پیش‌فرض</button><?php endif; ?>
                <button type="button" class="az-address-delete">حذف</button>
            </div>
        </div>
    <?php endforeach; ?>
    <button class="az-address az-address-add" type="button" uk-toggle="target: #az-address-form">
        <span class="az-address-plus" aria-hidden="true"></span>
        افزودن آدرس جدید
    </button>
</div>

<div id="az-address-form" uk-modal>
    <div class="uk-modal-dialog uk-modal-body az-modal">
        <button class="az-user-close az-modal-close uk-modal-close" type="button" aria-label="بستن"><svg viewBox="0 0 38 38" aria-hidden="true"><path d="M2 2l34 34M36 2L2 36"/></svg></button>
        <h2 class="az-title">آدرس جدید</h2>
        <form class="az-form-grid" onsubmit="UIkit.modal('#az-address-form').hide(); return false;">
            <div class="az-field"><label>عنوان آدرس</label><input class="az-input" placeholder="مثلاً خانه"></div>
            <div class="az-field"><label>نام تحویل‌گیرنده</label><input class="az-input"></div>
            <div class="az-field"><label>استان</label>
                <select class="az-select"><?php foreach ($provinces as $p) : ?><option><?php echo $p; ?></option><?php endforeach; ?></select>
            </div>
            <div class="az-field"><label>شهر</label><input class="az-input"></div>
            <div class="az-field" style="grid-column:1/-1"><label>آدرس کامل</label><input class="az-input" placeholder="خیابان، کوچه، پلاک، واحد"></div>
            <div class="az-field"><label>کد پستی</label><input class="az-input" inputmode="numeric" dir="ltr"></div>
            <div class="az-field"><label>تلفن همراه</label><input class="az-input" inputmode="numeric" dir="ltr"></div>
            <label class="az-check" style="grid-column:1/-1"><input type="checkbox"> این آدرس پیش‌فرض باشد</label>
            <div style="grid-column:1/-1"><button class="az-btn" type="submit">ذخیره آدرس</button></div>
        </form>
    </div>
</div>
<?php amuj_account_close(); ?>
