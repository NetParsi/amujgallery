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
$input = $app->getInput();
$wa    = $this->getWebAssetManager();
$user = JFactory::getUser();
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

$this->setMetaData('viewport', 'width=device-width, initial-scale=1, maximum-scale=1');
$this->setMetaData('theme-color', '#e70000');
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
    . ($this->direction == 'rtl' ? ' rtl' : '');
?>">
<jdoc:include type="message" />
<main>
    <?php if ($this->countModules('contenttop', true)) : ?>
        <jdoc:include type="modules" name="contenttop" style="html5" />
    <?php endif; ?>
    <jdoc:include type="component" />
    <?php if ($this->countModules('contentbottom', true)) : ?>
        <jdoc:include type="modules" name="contentbottom" style="html5" />
    <?php endif; ?>
</main>
<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>