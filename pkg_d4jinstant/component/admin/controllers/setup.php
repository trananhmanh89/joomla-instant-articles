<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
defined('_JEXEC') or die;

class D4jInstantControllerSetup extends JControllerLegacy {

	function getModel($name = 'Setup', $prefix = 'D4jInstantModel', $config = array()) {
		return parent::getModel($name, $prefix, $config);
	}

	function login() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model = $this->getModel();
		$url = $model->getLoginUrl();
		$this->setRedirect($url);
	}
	
	function getPages() {
		$model = $this->getModel();
		$result = $model->getPages();
		die(json_encode($result));
	}
	
	function savePage() {
		$this->checkToken();
		$model = $this->getModel();
        
		if ($model->savePage()) {
            $this->setRedirect('index.php?option=com_d4jinstant&view=config');
        } else {
        	$this->setMessage('You have no page that supports Instant Article. Please sign up new one.');
            $this->setRedirect('index.php?option=com_d4jinstant&view=setup');
        }
	}
    
    function saveApp() {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $model = $this->getModel();
        $model->saveApp();
        $this->setRedirect('index.php?option=com_d4jinstant&view=setup');
    }
    
    function resetApp() {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $model = $this->getModel();
        $model->resetApp();
        $this->setRedirect('index.php?option=com_d4jinstant&view=setup');
    }
}
