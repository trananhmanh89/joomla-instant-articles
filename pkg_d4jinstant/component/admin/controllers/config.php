<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

class D4jInstantControllerConfig extends JControllerLegacy {
	
	function getModel($name = 'Config', $prefix = 'D4jInstantModel', $config = array()) {
		return parent::getModel($name, $prefix, $config);
	}

	function apply() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model = $this->getModel();
		$model->apply();
		$this->setRedirect('index.php?option=com_d4jinstant');
		JFactory::getApplication()->enqueueMessage('Save config successfully!');
	}
	
	function disconnect() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model = $this->getModel();
		$model->disconnect();
		$this->setRedirect('index.php?option=com_d4jinstant&view=setup');
	}

}
