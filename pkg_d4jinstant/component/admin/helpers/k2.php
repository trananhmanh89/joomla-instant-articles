<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/components/com_k2/helpers/route.php';
require_once JPATH_ADMINISTRATOR . '/components/com_d4jinstant/helpers/content.php';

class D4jK2 extends D4jContent {

	function getSate($item) {
		return $item->published;
	}

	function getItem($id) {
		$query = "SELECT u.name author, c.name category, i.*
			FROM #__k2_items i
			LEFT JOIN #__k2_categories c ON c.id = i.catid
			LEFT JOIN #__users u ON i.created_by = u.id
			WHERE i.id = " . (int) $id;
		$db = JFactory::getDbo();
		$item = $db->setQuery($query)->loadObject();
		return $item;
	}

	function getUrl($item) {
		$slug = $item->id . ':' . $item->alias;
		$route = K2HelperRoute::getItemRoute($slug, $item->catid);
		$url = JRoute::_($route);
		$url = $this->encodeUrl($url);
		$url = $item->domain . $url;
		return $url;
	}

	function getCover($item, $content) {
		$cover = new stdClass();
		$image_path = JPATH_SITE . '/media/k2/items/cache/' . md5('Image' . $item->id) . '_L.jpg';
		if (file_exists($image_path)) {
			$cover->url = $item->siteUrl . 'media/k2/items/cache/' . md5('Image' . $item->id) . '_L.jpg';
		} elseif (preg_match_all('/<img(.*?)src=["\'](.*?)["\']/', $content, $matches)) {
			$cover->url = $matches[2][0];
		} else {
			$cover->url = $item->siteUrl . 'administrator/components/com_d4jinstant/assets/images/cover.png';
		}
		if ($item->image_caption != '') {
			$cover->caption = $item->image_caption;
		} else {
			$cover->caption = $item->title;
		}
		return $cover;
	}

}
