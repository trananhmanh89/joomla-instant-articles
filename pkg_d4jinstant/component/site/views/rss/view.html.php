<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

class D4jInstantViewRss extends JViewLegacy {

	function display($tpl = null) {
		$this->list = $this->get('List');
		echo parent::display($tpl);die;
	}

}
