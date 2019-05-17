<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('groupedlist');

class JFormFieldZooBlogCategory extends JFormFieldGroupedList {
	protected $type = 'zooblogcategory';
	
	protected $options = array();
	
	function getGroups() {
		$blogs = $this->getBlogApps();
		if (!$blogs) {
			return array();
		}
		
		$groups = array();
		foreach ($blogs as $blog) {
			$this->buildListOptions($blog->id, 0, 0);
			$groups[$blog->name] = $this->options;
			$this->options = array();
		}

		return $groups;
	}
	
	function getBlogApps() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->qn(array('id', 'name')))
			->from($db->qn('#__zoo_application'))
			->where($db->qn('application_group') . '=' . $db->q('blog'));
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function buildListOptions($appid, $parentid, $level) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select($db->qn('parent'))
			->select($db->qn(array('name', 'id'), array('text', 'value')))
			->from($db->qn('#__zoo_category'))
			->where($db->qn('application_id') . '=' . $db->q($appid))
			->where($db->qn('parent') . '=' . $db->q($parentid))
			->order('ordering asc');
		$db->setQuery($query);
		$list = $db->loadObjectList();

		foreach ($list as $item) {
			$prefix = $level > 0 ? str_repeat('. ', $level - 1) . '| _ ' : '';
			$item->text = $prefix . $item->text;
			$this->options[] = $item;
			$this->buildListOptions($appid, $item->value, $level + 1);
		}
	}
}
