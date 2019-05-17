<?php

/**
 * @package     Facebook Instant Articles
 * @subpackage  com_d4jinstant
 * @copyright   Copyright (C) MrMeo - D4J Team http://designforjoomla.com
 * @license     GPLv2 or later
 */
defined('_JEXEC') or die('Restricted access');

JLoader::register('D4jInstantHelper', JPATH_ADMINISTRATOR . '/components/com_d4jinstant/helpers/d4jinstant.php');

$controller = JControllerLegacy::getInstance('d4jinstant');

$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));
$controller->redirect();
