<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.netparsi
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Environment\Browser;
$browser = Browser::getInstance();

//JHtml::_('jquery.framework');

/** @var Joomla\CMS\Document\HtmlDocument $this */

$app   = Factory::getApplication();
$user = Factory::getUser();
$input = $app->getInput();
$wa    = $this->getWebAssetManager();
$params = $app->getTemplate(true)->params;
$menu = $app->getMenu();
$active = $menu->getActive();

$pageparams = $menu->getParams( $active->id );

// Favicon: brand logo (SVG) from the template media folder
$this->addHeadLink(Uri::root() . 'media/templates/site/netparsi/img/favicon.svg', 'icon', 'rel', ['type' => 'image/svg+xml']);

// Detecting Active Variables
$option   = $input->getCmd('option', '');
$view     = $input->getCmd('view', '');
$layout   = $input->getCmd('layout', '');
$task     = $input->getCmd('task', '');
$itemid   = $input->getCmd('Itemid', '');
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu     = $app->getMenu()->getActive();
$pageclass = $menu !== null ? $menu->getParams()->get('pageclass_sfx', '') : '';

// Theme CSS/JS carry their file time as version, so browsers never keep a stale copy after an update
$tplMediaPath = JPATH_ROOT . '/media/templates/site/netparsi/';
$tplMediaUrl  = Uri::root() . 'media/templates/site/netparsi/';
$wa->registerStyle('netparsiStyle', $tplMediaUrl . 'css/netparsi.css', ['version' => (string) @filemtime($tplMediaPath . 'css/netparsi.css')]);
$wa->registerScript('netparsiScript', $tplMediaUrl . 'js/netparsi.js', ['version' => (string) @filemtime($tplMediaPath . 'js/netparsi.js')]);

$wa
        ->usePreset('uikitPreset')
        ->usePreset('netparsiPreset')
;

$this->setMetaData('viewport', 'width=device-width, initial-scale=1, maximum-scale=1');
$this->setMetaData('theme-color', '#b2cc9d');

// Static demo pages: index.php?page=name renders static/pages/name.php
// (component overrides in html/ may have loaded the helpers already)
defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once __DIR__ . '/static/_helpers.php';

// Demo live-search endpoint (replaced by mod_netparsisearch's com_ajax endpoint later)
if ($input->getInt('amujsearch')) {
    $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
    $app->sendHeaders();
    echo json_encode(amuj_search($input->getString('q', '')), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $app->close();
}

amuj_current_user(); // resolves the (demo) customer early so its cookie can be set

$amujPages  = amuj_pages();
$staticPage = $input->getCmd('page', '');

if ($staticPage === '' && $active && $active->home && $option === 'com_content') {
    $staticPage = 'home';
}

if (!isset($amujPages[$staticPage])) {
    $staticPage = '';
}

$amujOverlay = $staticPage ? $amujPages[$staticPage][1] : false;
// Sign-in pages (static demo or the SMS auth component) stand alone without header and footer
$amujBare    = ($staticPage && !empty($amujPages[$staticPage][2])) || $option === 'com_auth';

if ($staticPage) {
    $this->setTitle($amujPages[$staticPage][0]);
}

// Meta description, canonical, link previews and structured data (pages add their own data with amuj_seo())
amuj_seo_render($this);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <jdoc:include type="metas" />
    <jdoc:include type="styles" />
    <jdoc:include type="scripts" />
</head>

<body class="site <?php echo $option
    . ' view-' . $view
    . ($layout ? ' layout-' . $layout : ' no-layout')
    . ($task ? ' task-' . $task : ' no-task')
    . ($itemid ? ' itemid-' . $itemid : '')
    . ($pageclass ? ' ' . $pageclass : '')
    . ($this->direction == 'rtl' ? ' rtl' : '')
    . ($staticPage ? ' az-page-' . $staticPage : '');
?>" data-az-cart="<?php echo Uri::root(); ?>index.php?option=com_hikashop&amp;ctrl=product&amp;task=updatecart&amp;tmpl=raw"
    data-az-wish="<?php echo Uri::root(); ?>index.php?option=com_ajax&amp;group=ajax&amp;plugin=amujcontact&amp;format=raw"
    data-az-token="<?php echo \Joomla\CMS\Session\Session::getFormToken(); ?>"<?php
    // Guests: the heart opens the mobile login (and comes back here)
    echo Factory::getApplication()->getIdentity()->guest ? ' data-az-login="' . htmlspecialchars(amuj_login_url()) . '"' : ''; ?>>
<?php if ($staticPage === 'home') : ?>
    <?php // Home intro: blurred cover, the logo draws itself, then flies into the header logo (netparsi.js). Once per browser session. ?>
    <div id="az-intro" class="az-intro" aria-hidden="true"><div class="az-intro-logo"><?php echo amuj_logo(); ?></div></div>
    <script>(function(){var el=document.getElementById('az-intro');try{if(sessionStorage.getItem('azIntro')||window.matchMedia('(prefers-reduced-motion: reduce)').matches){el.parentNode.removeChild(el);return;}sessionStorage.setItem('azIntro','1');}catch(e){}document.documentElement.classList.add('az-intro-on');})();</script>
    <noscript><style>#az-intro{display:none}</style></noscript>
<?php endif; ?>
<?php if (!$amujBare) include __DIR__ . '/static/header.php'; ?>
<main id="az-main" class="az-main<?php echo $amujOverlay ? ' az-main--overlay' : ''; ?><?php echo $amujBare ? ' az-main--bare' : ''; ?>">
    <jdoc:include type="message" />
    <?php if ($staticPage) : ?>
        <?php include __DIR__ . '/static/pages/' . $staticPage . '.php'; ?>
    <?php else : ?>
        <div class="az-component"><jdoc:include type="component" /></div>
    <?php endif; ?>
</main>
<?php if (!$amujBare) include __DIR__ . '/static/footer.php'; ?>
<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>