<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

class D4jBase {

	function encodeUrl($url) {
		$config = JFactory::getConfig();
		$sef = $config->get('sef');
		if ($sef) {
			$array = explode('/', $url);
			foreach ($array as &$value) {
				$value = urlencode($value);
			}
			$url = implode('/', $array);
		}
		return $url;
	}

	function encodeImageUrl($url) {
		$array = explode('/', $url);
		foreach ($array as &$value) {
			$value = urlencode($value);
		}
		$url = implode('/', $array);
		$url = str_replace('+', '%20', $url);
		return $url;
	}

	function getSiteUrl() {
		$base = JUri::root();
		$pattern = '~(http:\/\/|https:\/\/)(.*)~';
		$params = JComponentHelper::getParams('com_d4jinstant');
		switch ($params->get('ssl')) {
			case 'http':
			case 'https':
				$base = preg_replace($pattern, $params->get('ssl') . '://$2', $base);
				break;

			default:
				$config = JFactory::getConfig();
				$ssl = $config->get('force_ssl');
				switch ($ssl) {
					case 1:
						$base = preg_replace($pattern, 'http://$2', $base);
						break;
					case 2:
					default:
						break;
				}
				break;
		}
		return $base;
	}

	function getDomain($url) {
		preg_match_all('/(http:\/\/|https:\/\/).*?\//', $url, $matches);
		$domain = rtrim($matches[0][0], '/');
		return (string) $domain;
	}

}
