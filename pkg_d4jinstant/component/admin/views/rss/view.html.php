<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

class D4jInstantViewRss extends JViewLegacy {

	function display($tpl = null) {
		JToolBarHelper::title('RSS');
		D4jInstantHelper::addSubmenu('rss');
		$this->sidebar = JHtmlSidebar::render();
		
		$this->types = array('content');
		if ($this->isK2Installed()) {
			$this->types[] = 'k2';
		}
		
		if ($this->isZooInstalled()) {
			$this->types[] = 'zoo';
		}

		parent::display($tpl);
	}
	
	function isK2Installed() {
		$query = "SELECT extension_id FROM #__extensions WHERE element = 'com_k2'";
		$db = JFactory::getDbo();
		$result = $db->setQuery($query)->loadResult();
		return $result;
	}
	
	function isZooInstalled() {
		$query = "SELECT extension_id FROM #__extensions WHERE element = 'com_zoo'";
		$db = JFactory::getDbo();
		$result = $db->setQuery($query)->loadResult();
		return $result;
	}

}
