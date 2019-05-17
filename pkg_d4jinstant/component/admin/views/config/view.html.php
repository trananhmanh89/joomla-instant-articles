<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class D4jInstantViewConfig extends JViewLegacy {

	function display($tpl = null) {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_d4jinstant');
		$token = $params->get('access_token');
		if ($token) {
			$app->enqueueMessage('Login Facebook Successfully! Your page is <b><a target="blank" href="https://facebook.com/' . $params->get('pageid') . '">' . $params->get('name') . '</a></b>', 'notice');
		} else {
			$app->redirect('index.php?option=com_d4jinstant&view=setup');
		}

		JToolBarHelper::title('Config', 'equalizer.png');
		JToolbarHelper::apply('config.apply');
		JToolbarHelper::unpublish('config.disconnect', 'Logout Facebook');
		D4jInstantHelper::addSubmenu('config');
		$this->sidebar = JHtmlSidebar::render();
		$this->form = $this->get('Form');
		$data = $params->toArray();
		$this->form->bind($data);
		$this->k2 = $this->isK2Installed();
		$this->zoo = $this->isZooInstalled();
		parent::display($tpl);
	}
	
	function isK2Installed() {
		$query = "SELECT extension_id FROM #__extensions WHERE element = 'com_k2' AND enabled = 1";
		$db = JFactory::getDbo();
		$result = $db->setQuery($query)->loadResult();
		return $result;
	}
	
	function isZooInstalled() {
		$query = "SELECT extension_id FROM #__extensions WHERE element = 'com_zoo' AND enabled = 1";
		$db = JFactory::getDbo();
		$result = $db->setQuery($query)->loadResult();
		return $result;
	}

}
