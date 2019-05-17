<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

class D4jInstantViewSetup extends JViewLegacy {
	
	function display($tpl = null) {
		JToolBarHelper::title('Login to Facebook', 'equalizer.png');
		
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_d4jinstant');
		$token = $params->get('access_token');
		if ($token) {
			$app->redirect('index.php?option=com_d4jinstant&view=config');
		}
		
		$appid = $app->getUserState('facebook.appid');
		$secret = $app->getUserState('facebook.secret');
		
		if ($appid && $secret) {
			$this->setLayout('pages');
            $this->appid = $appid;
            JToolbarHelper::unpublish('setup.resetApp', 'Reset App ID');
		} else {
			$this->setLayout('login');
		}
		parent::display($tpl);
	}
}