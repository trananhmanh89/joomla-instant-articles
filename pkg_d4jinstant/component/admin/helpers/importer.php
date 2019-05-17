<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_d4jinstant/vendor/autoload.php';

use Facebook\InstantArticles\Client\Client;
use Facebook\InstantArticles\Elements\InstantArticle;
use Facebook\InstantArticles\Elements\Header;
use Facebook\InstantArticles\Elements\Time;
use Facebook\InstantArticles\Elements\Ad;
use Facebook\InstantArticles\Elements\Analytics;
use Facebook\InstantArticles\Elements\Author;
use Facebook\InstantArticles\Elements\Image;
use Facebook\InstantArticles\Elements\Caption;
use Facebook\InstantArticles\Elements\Footer;
use Facebook\InstantArticles\Transformer\Transformer;
use Facebook\InstantArticles\AMP\AMPArticle;

class D4jImporter {
	
	function toAmp($data) {
		ob_start();
		$instant_article = $this->getInstantArticle($data);
		$properties = array(
				'styles-folder' => JPATH_ADMINISTRATOR . '/components/com_d4jinstant/assets'
		);
		$amp_string = AMPArticle::create($instant_article, $properties)->render();
		ob_end_clean();
		return $amp_string;
	}

	function post($data, $msg) {
		$params = JComponentHelper::getParams('com_d4jinstant');
		$fb = new Facebook\Facebook([
				'app_id' => $params->get('appid'),
				'app_secret' => $params->get('secret'),
				'default_graph_version' => 'v2.8',
		]);

		$content = [
				'link' => $data->url,
				'message' => $msg,
		];

		try {
			$response = $fb->post('/'.$params->get('page').'/feed', $content, $params->get('access_token'));
			$msg = $this->getMessage('Post to Facebook successfully!', 'notice');
			$postid = $response->getDecodedBody();
			$msg->postid = $postid;
		} catch (Facebook\Exceptions\FacebookResponseException $e) {
			$msg = $this->getMessage('Graph returned an error: ' . $e->getMessage(), 'error');
		} catch (Facebook\Exceptions\FacebookSDKException $e) {
			$msg = $this->getMessage('Facebook SDK returned an error: ' . $e->getMessage(), 'error');
		}
		die(json_encode($msg));
	}

	function import($data) {
		ob_start();
		$instant_article = $this->getInstantArticle($data);
		$client = $this->getClient();
		try {
			$client->importArticle($instant_article, true);
			$msg = $this->getMessage('Import Facebook Instant Article Successfully!', 'notice');
		} catch (Exception $e) {
			try {
				$client->importArticle($instant_article, false);
				$msg = $this->getMessage('Import successfully, but your Instant Article is not live yet. You need submit your fanpage to review!', 'warning');
			} catch (Exception $e) {
				$msg = $this->getMessage('Could not import the article (' . $e->getMessage() . ')', 'error');
			}
		}
		ob_end_clean();

		die(json_encode($msg));
	}

	function getClient() {
		$params = JComponentHelper::getParams('com_d4jinstant');
		$appid = $params->get('appid');
		$secret = $params->get('secret');
		$token = $params->get('access_token');
		$pageid = $params->get('pageid');
		$client = Client::create($appid, $secret, $token, $pageid);
		return $client;
	}

	function getMessage($text, $type) {
		$msg = new stdClass();
		$msg->{$type} = array($text);
		return $msg;
	}
	
	function getTrackingCode($data) {
		$params = JComponentHelper::getParams('com_d4jinstant');
		$type = $params->get('tracking_type');
		$gaid = $params->get('gaid');
		$tracking_code = $params->get('tracking_code');
		if ($type === 'ga' && $gaid) {
			return '
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id='.$gaid.'"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag(\'js\', new Date());

  gtag(\'config\', \''.$gaid.'\', {
    \'page_title\': \''.$data->title.'\'
  });
</script>
';
		} else if ($type === 'custom' && $tracking_code ) {
			return $tracking_code;
		} else {
			return '';
		}
	}

	function getInstantArticle($data) {
		$params = JComponentHelper::getParams('com_d4jinstant');
		$instant_article = InstantArticle::create();
		$instant_article->withCanonicalUrl($data->url);
		$instant_article->withHeader(
				Header::create()
						->withTitle($data->title)
						->withSubTitle($data->subtitle)
						->withPublishTime(
								Time::create(Time::PUBLISHED)
								->withDatetime(
										\DateTime::createFromFormat(
												'Y-m-d G:i:s', $data->created
										)
								)
						)
						->withModifyTime(
								Time::create(Time::MODIFIED)
								->withDatetime(
										\DateTime::createFromFormat(
												'Y-m-d G:i:s', $data->modified
										)
								)
						)
						->addAuthor(
								Author::create()
								->withName($data->author)
						)
						->withKicker($data->kicker)
						->withCover(
								Image::create()
								->withURL($data->cover->url)
								->withCaption(
										Caption::create()
										->appendText($data->cover->caption)
								)
						)
		);

		$encoded_content = mb_convert_encoding($data->content, 'HTML-ENTITIES', 'UTF-8');
		$document = new DOMDocument();
		$document->loadHTML($encoded_content);

		$rules = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_d4jinstant/assets/conf.json');
		$transformer = new Transformer();
		$transformer->loadRules($rules);
		$transformer->transform($instant_article, $document);

		$tracking_code = $this->getTrackingCode($data);
		if ($tracking_code) {
			$instant_article
					->addChild(
							Analytics::create()
							->withHTML(
									$tracking_code
							)
			);
		}

		$header = $instant_article->getHeader();
		$ad_type = $params->get('ad_type');
		$ad_size = explode(',', $params->get('ad_size'));
		$width = (int) $ad_size[0];
		$height = (int) $ad_size[1];
		$ad = Ad::create()
				->enableDefaultForReuse()
				->withWidth($width)
				->withHeight($height);

		switch ($ad_type) {
			case 'fan':
				$placement_id = $params->get('placement_id');
				if ($placement_id != '') {
					$ad->withSource(
							'https://www.facebook.com/adnw_request?placement='
							. $placement_id . '&adtype=banner' . $width . 'x' . $height
					);
					$header->addAd($ad);
				}
				break;

			case 'iframeurl':
				$iframeurl = $params->get('iframe_url');
				if ($iframeurl != '') {
					$ad->withSource(
							$iframeurl
					);
					$header->addAd($ad);
				}
				break;

			case 'embed':
				$embed_code = $params->get('embed_code');
				if ($embed_code != '') {
					$ad->withHTML(
							$embed_code
					);
					$header->addAd($ad);
				}
				break;

			case 'none':
			default :
				break;
		}

		$footer = Footer::create();
		$copyright = $params->get('copyright');
		if ($copyright != '') {
			$footer->withCopyright($copyright);
		}
		$instant_article->withFooter($footer);
		$instant_article->withStyle($params->get('style'));
		return $instant_article;
	}

}
