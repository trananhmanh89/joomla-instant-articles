<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';
require_once JPATH_ADMINISTRATOR . '/components/com_d4jinstant/helpers/content.php';

class D4jZoo extends D4jContent {

	function getItem($id) {
		$zoo = App::getInstance('zoo');
		$items = $zoo->table->item->getByIds(array($id));
		$item = $items[$id];
		$category = $item->getPrimaryCategory();
		$item->category = $category->name;
		$item->title = $item->name;
		$item->author = $item->getAuthor();
		$item->content = '';
		$elements = $item->elements;
		$item->subtitle = $elements->get('08795744-c2dc-4a68-8252-4e21c4c4c774')[0]['value'];
		$contents = $elements->get('2e3c9e69-1f9e-4647-8d13-4e88094d2790');
		foreach ($contents as $content) {
			$item->content .= $content['value'];
		}
		return $item;
	}

	function getUrl($item) {
		$zoo = App::getInstance('zoo');
		$zoo_route = $zoo->route->item($item, false);
		$router = JFactory::getApplication()->getRouter();
		$uri = $router->build($zoo_route)->toString();
		$url = $item->domain . $uri;
		return $url;
	}

	function getCover($item, $content) {
		$cover = new stdClass();
		$elements = $item->elements;
		$image = $elements->get('cdce6654-4e01-4a7f-9ed6-0407709d904c');
		$file = $image['file'];
		$image_path = JPATH_SITE . '/' . $file;
		if ($file && file_exists($image_path)) {
			$cover->url = $item->siteUrl . $file;
		} elseif (preg_match_all('/<img(.*?)src=["\'](.*?)["\']/', $content, $matches)) {
			$cover->url = $matches[2][0];
		} else {
			$cover->url = $item->siteUrl . 'administrator/components/com_d4jinstant/assets/images/cover.png';
		}

		if (!empty($image['title'])) {
			$cover->caption = $image['title'];
		} else {
			$cover->caption = $item->title;
		}
		return $cover;
	}

}
