<?php

/**
 * @package     Facebook Instant Articles
 * @subpackage  plg_d4jinstant
 * @copyright   Copyright (C) MrMeo - D4J Team http://designforjoomla.com
 * @license     GPLv2 or later
 */
defined('_JEXEC') or die;

class plgSystemD4jinstantInstallerScript {

	function install($parent) {
		$query = "UPDATE `#__extensions` 
			SET `enabled` = 1
			WHERE `type` = 'plugin' 
			AND `element` = 'd4jinstant'";
		$db = JFactory::getDBO();
		$db->setQuery($query)->execute();
	}

}
