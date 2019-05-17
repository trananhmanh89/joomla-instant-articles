<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

class D4jInstantModelConfig extends JModelForm {

	function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_d4jinstant.config', 'config', array('control' => 'jform', 'load_data' => $loadData));
		return $form;
	}

	function apply() {
		$input = JFactory::getApplication()->input;
		$jform = $input->get('jform', array(), 'array');
		$params = JComponentHelper::getParams('com_d4jinstant');
		$params->loadArray($jform);
		$data = $params->toString('JSON');
		$this->updateDb($data);
	}

	function disconnect() {
		$app = JFactory::getApplication();
		$app->setUserState('facebook.appid', '');
		$app->setUserState('facebook.secret', '');
		$app->setUserState('facebook.pages', '');
		$params = JComponentHelper::getParams('com_d4jinstant');
		$params->set('pageid', '');
		$params->set('appid', '');
		$params->set('secret', '');
		$params->set('access_token', '');
		$data = $params->toString('JSON');
		$this->updateDb($data);
	}

	function updateDb($data) {
		$db = JFactory::getDbo();
		$q = 'UPDATE #__extensions SET params = ' . $db->quote($data) . ' WHERE element = "com_d4jinstant"';
		$db->setQuery($q);
		$db->execute();
	}

}
