<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.netparsi
 *
 * Error page (404 and others) in the store's own look: same header, footer and styles.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\ErrorDocument $this */

$app = Factory::getApplication();
$wa  = $this->getWebAssetManager();

$tplMediaPath = JPATH_ROOT . '/media/templates/site/netparsi/';
$tplMediaUrl  = Uri::root() . 'media/templates/site/netparsi/';
$wa->registerStyle('netparsiStyle', $tplMediaUrl . 'css/netparsi.css', ['version' => (string) @filemtime($tplMediaPath . 'css/netparsi.css')]);
$wa->registerScript('netparsiScript', $tplMediaUrl . 'js/netparsi.js', ['version' => (string) @filemtime($tplMediaPath . 'js/netparsi.js')]);
$wa->usePreset('uikitPreset')->usePreset('netparsiPreset');

$this->addHeadLink($tplMediaUrl . 'img/favicon.svg', 'icon', 'rel', ['type' => 'image/svg+xml']);
$this->setMetaData('viewport', 'width=device-width, initial-scale=1, maximum-scale=1');
$this->setMetaData('theme-color', '#b2cc9d');

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once __DIR__ . '/static/_helpers.php';

$amujErrorCode = (int) $this->error->getCode() ?: 500;
$amujOverlay   = false;
$this->setTitle($amujErrorCode === 404 ? 'صفحه پیدا نشد' : 'خطا');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <jdoc:include type="metas" />
    <jdoc:include type="styles" />
    <jdoc:include type="scripts" />
</head>
<body class="site az-page-notfound<?php echo $this->direction === 'rtl' ? ' rtl' : ''; ?>">
<?php include __DIR__ . '/static/header.php'; ?>
<main id="az-main" class="az-main">
    <?php include __DIR__ . '/static/pages/notfound.php'; ?>
    <?php if ($this->debug) : ?>
        <pre class="az-error-debug" dir="ltr"><?php echo htmlspecialchars($this->error->getMessage() . "\n" . $this->error->getFile() . ':' . $this->error->getLine(), ENT_QUOTES, 'UTF-8'); ?></pre>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/static/footer.php'; ?>
</body>
</html>
