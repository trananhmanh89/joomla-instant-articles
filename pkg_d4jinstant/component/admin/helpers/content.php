<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;
require_once JPATH_ROOT . '/components/com_content/helpers/route.php';
require_once JPATH_ADMINISTRATOR . '/components/com_d4jinstant/helpers/base.php';

class D4jContent extends D4jBase {

	function __construct($id = 0) {
		$item = $this->getItem($id);
		$item->siteUrl = $this->getSiteUrl();
		$item->domain = $this->getDomain($item->siteUrl);
		$this->id = $item->id;
		$this->url = $this->getUrl($item);
		$this->title = $item->title;
		$this->subtitle = isset($item->subtitle) ? $item->subtitle : '';
		$this->created = $item->created;
		$this->modified = $item->modified;
		$this->kicker = $item->category;
		$this->author = $item->created_by_alias ? $item->created_by_alias : $item->author;
		$this->content = $this->formatContent($item);
		$this->cover = $this->getCover($item, $this->content);
		$this->publish_up = $item->publish_up;
		$this->published = $this->getSate($item);
	}

	function getSate($item) {
		return $item->state;
	}

	function getUrl($item) {
		$slug = $item->id . ':' . $item->alias;
		$route = ContentHelperRoute::getArticleRoute($slug, $item->catid, $item->language);
		$url = JRoute::_($route);
		$url = $this->encodeUrl($url);
		$url = $item->domain . $url;
		return $url;
	}

	function getItem($id) {
		$db = JFactory::getDbo();
		$query = 'select u.name author, cat.title category, c.*  from #__content c
							left join #__categories cat on cat.id = c.catid
							left join #__users u on u.id = c.created_by
							where c.id=' . (int) $id;
		$db->setQuery($query);
		return $db->loadObject();
	}

	function formatContent($item) {
		if (isset($item->type) && $item->type === 'article') {
			// is zoo
			$item->text = $item->content;
		} else {
			if (isset($item->attribs)) {
				$context = 'com_content.article';
				$item->params = new JRegistry($item->attribs);
			} elseif(isset($item->params)) {
				$context = 'com_k2.item';
				$item->params = new JRegistry($item->params);
			}

			$item->text = $item->introtext . ' ' . $item->fulltext;
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array($context, &$item, &$item->params, 0));
			$dispatcher->trigger('onContentAfterTitle', array($context, &$item, &$item->params, 0));
			$dispatcher->trigger('onContentBeforeDisplay', array($context, &$item, &$item->params, 0));
			$dispatcher->trigger('onContentAfterDisplay', array($context, &$item, &$item->params, 0));
		}
		
		$content = $item->text;

		$twitter_pattern = '/(<blockquote class="twitter-tweet"(.*?)<\/blockquote>[\r\n]+<script(.*?)<\/script>)/';
		$content = preg_replace($twitter_pattern, '<iframe>$1</iframe>', $content);
		
		preg_match_all($twitter_pattern, $content, $matches);

		$url_pattern = '/href=["\']((.*?)index\.php\?option=(.*?))["\']/';
		preg_match_all($url_pattern, $content, $matches);
		foreach ($matches[1] as $i) {
			$url = str_replace(JURi::root(), '', $i);
			$url = JRoute::_($url);
			$url = $this->encodeUrl($url);
			$url = $item->domain . $url;
			$content = str_replace($i, $url, $content);
		}

		$content = preg_replace('~\{loadmodule(.*?)\}~', '', $content);
		$content = preg_replace('~\{loadposition(.*?)\}~', '', $content);
		$content = preg_replace('~<div(.*?)>(.*?)<\/div>~', '<p>$2</p>', $content);
		$content = preg_replace('~<br(.*?)>~', '</p><p>', $content);

		$img_pattern = '~<img(.*?)src=["\'](?!http:\/\/)(?!https:\/\/)(.*?)["\']~';
		$img_replace = '<img$1src="' . $item->siteUrl . '$2"';
		$content = preg_replace($img_pattern, $img_replace, $content);

		$img_pattern = '~<img(.*?)src=["\'](http:\/\/|https:\/\/)(.*?)["\'](.*?)\/>~';
		preg_match_all($img_pattern, $content, $matches);
		foreach ($matches[3] as $match) {
			$encodeurl = $this->encodeImageUrl($match);
			$content = str_replace($match, $encodeurl, $content);
		}

		$link_pattern = '~<a(.*?)href=["\'](?!http:\/\/)(?!https:\/\/)(.*?)["\']~';
		$link_replace = '<a$1href="' . $item->siteUrl . '$2"';
		$content = preg_replace($link_pattern, $link_replace, $content);
		return $content;
	}

	function getCover($item, $content) {
		$cover = new stdClass();
		$images = json_decode($item->images);
		if ($images->image_intro || $images->image_fulltext) {
			$cover->url = $images->image_intro ? $images->image_intro : $images->image_fulltext;
			if (!$this->isAbosluteUrl($cover->url)) {
				$cover->url = $item->siteUrl . $this->encodeImageUrl($cover->url);
			}
		} elseif (preg_match_all('/<img(.*?)src=["\'](.*?)["\']/', $content, $matches)) {
			$cover->url = $matches[2][0];
		} else {
			$cover->url = $item->siteUrl . 'administrator/components/com_d4jinstant/assets/images/cover.png';
		}

		if ($images->image_intro_caption) {
			$cover->caption = $images->image_intro_caption;
		} elseif ($images->image_fulltext_caption) {
			$cover->caption = $images->image_fulltext_caption;
		} else {
			$cover->caption = $item->title;
		}
		return $cover;
	}

	function isAbosluteUrl($url) {
		return preg_match('/(http:\/\/|https:\/\/).*/', $url);
	}

}
