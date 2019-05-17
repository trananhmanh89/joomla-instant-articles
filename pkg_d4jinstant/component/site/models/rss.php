<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

class D4jInstantModelRss extends JModelLegacy {

	function __construct($config = array()) {
		parent::__construct($config);
		$this->params = JComponentHelper::getParams('com_d4jinstant');
	}

	function getList() {
		if (!$this->params->get('rss')) {
			return;
		}
		$app = JFactory::getApplication();
		$type = $app->input->get('type');
		switch ($type) {
			case 'content':
				return $this->getArticleList();
			case 'k2':
				return $this->getK2List();
			case 'zoo':
				return $this->getZooList();
		}
	}
	
	function getZooList() {
		JLoader::register('D4jZoo', JPATH_ADMINISTRATOR . '/components/com_d4jinstant/helpers/zoo.php');
		$catids = $this->params->get('zooblogcategory');
		if (!count($catids)) {
			return array();
		}
		$query = 'SELECT i.id
				FROM #__zoo_item i
				LEFT JOIN #__zoo_category_item ci ON i.id = ci.item_id
				WHERE ci.category_id IN ('. implode(',', $catids).') AND `type` = "article" GROUP BY i.id
				ORDER BY i.publish_up DESC
				LIMIT '. (int) $this->params->get('numitem', 20);
		$ids = JFactory::getDbo()->setQuery($query)->loadColumn();
		$list = array();
		foreach ($ids as $id) {
			$list[] = new D4jZoo($id);
		}

		return $list;
	}

	function getArticleList() {
		JLoader::register('D4jContent', JPATH_ADMINISTRATOR . '/components/com_d4jinstant/helpers/content.php');
		$catids = $this->params->get('content_category');
		if (!$catids) {
			return;
		}
		$query = "SELECT id FROM #__content"
				. " WHERE catid in (" . implode(",", $catids) . ")"
				. " AND state = 1"
				. " AND publish_up < '" . date('Y-m-d H:i:s') . "'"
				. " ORDER BY publish_up DESC LIMIT " . (int) $this->params->get('numitem', 20);
		$db = JFactory::getDbo();
		$ids = $db->setQuery($query)->loadColumn();

		$list = array();
		foreach ($ids as $id) {
			$list[] = new D4jContent($id);
		}

		return $list;
	}

	function getK2List() {
		JLoader::register('D4jK2', JPATH_ADMINISTRATOR . '/components/com_d4jinstant/helpers/k2.php');
		$catids = $this->params->get('k2_category');
		if (!$catids) {
			return;
		}

		$query = "SELECT id FROM #__k2_items"
				. " WHERE catid in (" . implode(",", $catids) . ")"
				. " AND published = 1"
				. " AND publish_up < '" . date('Y-m-d H:i:s') . "'"
				. " ORDER BY publish_up DESC LIMIT " . (int) $this->params->get('numitem', 20);

		$db = JFactory::getDbo();
		$ids = $db->setQuery($query)->loadColumn();

		$list = array();
		foreach ($ids as $id) {
			$list[] = new D4jK2($id);
		}

		return $list;
	}

}
