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

$this->addHeadLink(HTMLHelper::_('image', 'images/favicon.png', '', [], false, 1), 'icon', 'rel', ['sizes' => '48x48']);

// Detecting Active Variables
$option   = $input->getCmd('option', '');
$view     = $input->getCmd('view', '');
$layout   = $input->getCmd('layout', '');
$task     = $input->getCmd('task', '');
$itemid   = $input->getCmd('Itemid', '');
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu     = $app->getMenu()->getActive();
$pageclass = $menu !== null ? $menu->getParams()->get('pageclass_sfx', '') : '';
$wa
        ->usePreset('uikitPreset')
        ->usePreset('netparsiPreset')
;

$this->setMetaData('viewport', 'width=device-width, initial-scale=1, maximum-scale=1');
$this->setMetaData('theme-color', '#b2cc9d');

// Static demo pages: index.php?page=name renders static/pages/name.php
define('AMUJ_BASE', Uri::root());
define('AMUJ_ROOT', JPATH_ROOT);
require_once __DIR__ . '/static/_helpers.php';

// Demo live-search endpoint (replaced by mod_netparsisearch's com_ajax endpoint later)
if ($input->getInt('amujsearch')) {
    $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
    $app->sendHeaders();
    echo json_encode(amuj_search($input->getString('q', '')), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $app->close();
}

$amujPages  = amuj_pages();
$staticPage = $input->getCmd('page', '');

if ($staticPage === '' && $active && $active->home && $option === 'com_content') {
    $staticPage = 'home';
}

if (!isset($amujPages[$staticPage])) {
    $staticPage = '';
}

$amujOverlay = $staticPage ? $amujPages[$staticPage][1] : false;

if ($staticPage) {
    $this->setTitle($amujPages[$staticPage][0]);
}
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
?>">
<?php include __DIR__ . '/static/header.php'; ?>
<main class="az-main<?php echo $amujOverlay ? ' az-main--overlay' : ''; ?>">
    <jdoc:include type="message" />
    <?php if ($staticPage) : ?>
        <?php include __DIR__ . '/static/pages/' . $staticPage . '.php'; ?>
    <?php else : ?>
        <div class="az-component"><jdoc:include type="component" /></div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/static/footer.php'; ?>
<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>