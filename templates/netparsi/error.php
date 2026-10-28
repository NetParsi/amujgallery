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
    <div class="uk-container uk-container-large" id="outline">
        <div id="errorboxoutline">
            <?php if ($this->countModules('error-' . $errorCode)) : ?>
                <jdoc:include type="modules" name="error-<?php echo $errorCode; ?>" style="none" />
            <?php else : ?>
            <div class="uk-text-center">
                <div><?php echo HTMLHelper::image(Uri::base().'images/error-'.$errorCode.'.svg','','width="200"','',''); ?></div>
                <p class="uk-text-primary font f700 uk-text-large"><?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="uk-text-muted font f400"><?php echo Text::sprintf('ERROR_404_TEXT', '<a href="'.Uri::base().'products" class="uk-text-secondary f500">'.Text::_('PRODUCTS').'</a>'); ?></p>
                <div class="uk-margin-medium-top uk-margin-auto uk-width-1-1 uk-width-1-5@m">
                    <a class="uk-button uk-button-primary uk-border-pill" href="<?php echo Uri::base(); ?>">
                        <span><?php echo Text::_('HOME'); ?></span>
                    </a>
                </div>
            </div>
            <div id="errorboxheader" hidden><?php echo $this->error->getCode(); ?> - <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></div>
            <div id="errorboxbody" hidden>
                <?php endif; ?>
                <div id="techinfo">
                    <p>
                        <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
                        <?php if ($this->debug) : ?>
                            <br><?php echo htmlspecialchars($this->error->getFile(), ENT_QUOTES, 'UTF-8');?>:<?php echo $this->error->getLine(); ?>
                        <?php endif; ?>
                    </p>
                    <?php if ($this->debug) : ?>
                        <div>
                            <?php echo $this->renderBacktrace(); ?>
                            <?php // Check if there are more Exceptions and render their data as well?>
                            <?php if ($this->error->getPrevious()) : ?>
                                <?php $loop = true; ?>
                                <?php // Reference $this->_error here and in the loop as setError() assigns errors to this property and we need this for the backtrace to work correctly?>
                                <?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions?>
                                <?php $this->setError($this->_error->getPrevious()); ?>
                                <?php while ($loop === true) : ?>
                                    <p><strong><?php echo Text::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
                                    <p>
                                        <?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
                                        <br><?php echo htmlspecialchars($this->_error->getFile(), ENT_QUOTES, 'UTF-8');?>:<?php echo $this->_error->getLine(); ?>
                                    </p>
                                    <?php echo $this->renderBacktrace(); ?>
                                    <?php $loop = $this->setError($this->_error->getPrevious()); ?>
                                <?php endwhile; ?>
                                <?php // Reset the main error object to the base error?>
                                <?php $this->setError($this->error); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>