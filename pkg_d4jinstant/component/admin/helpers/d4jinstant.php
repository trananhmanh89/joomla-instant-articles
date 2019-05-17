<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

class D4jInstantHelper extends JHelperContent {

	static $extension = 'com_d4jinstant';

	static function addSubmenu($vName) {
		JHtmlSidebar::addEntry(
				JText::_('Config'), 'index.php?option=com_d4jinstant', $vName == 'config'
		);
		JHtmlSidebar::addEntry(
				JText::_('RSS'), 'index.php?option=com_d4jinstant&view=rss', $vName == 'rss'
		);
	}

}
