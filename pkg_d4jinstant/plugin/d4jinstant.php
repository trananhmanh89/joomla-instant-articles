<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

class PlgSystemD4JInstant extends JPlugin {

	public $d4jparams = null;
	public $access_token = null;

	function __construct(&$subject, $config = array()) {
		parent::__construct($subject, $config);
		if ($this->isD4jInstantInstalled()) {
			define('D4JINSTANT_INSTALLED', 1);
			$this->d4jparams = JComponentHelper::getParams('com_d4jinstant');
			$this->access_token = $this->d4jparams->get('access_token');
		}
	}

	function onAfterDispatch() {
		$app = JFactory::getApplication();
		if (!defined('D4JINSTANT_INSTALLED')) {
			return;
		}

		if ($app->isAdmin()) {
			return;
		}

		if (!$this->access_token) {
			return;
		}

		if ($app->input->get('format')) {
			return;
		}

		$this->addMetaTag();
		$importid = $app->input->get('importid');
		$do = $app->input->get('do');
		$type = $app->input->get('type');
		$key = $app->input->get('key');
		$token = md5($this->d4jparams->get('access_token'));
		if (!$importid || $key !== $token) {
			return;
		}

		if (!$do || !$type) {
			return;
		}

		$file = JPATH_ADMINISTRATOR . '/components/com_d4jinstant/helpers/' . $type . '.php';
		if (file_exists($file)) {
			require_once $file;
			require_once JPATH_ADMINISTRATOR . '/components/com_d4jinstant/helpers/importer.php';
			$class = 'D4j' . ucfirst($type);
			$data = new $class($importid);
			$importer = new D4jImporter;
			if (gmdate('Y-m-d H:i:s') < $data->publish_up || $data->published != 1) {
				die(json_encode($importer->getMessage('Your article is not published. Please try again later.', 'error')));
			}

			if ($do === 'd4jimport') {
				$importer->import($data);
			} else if ($do === 'd4jpost') {
				$msg = $app->input->get('msg', '', 'raw');
				$importer->post($data, $msg);
			}
		} else {
			die('{"error":["Import failed"]}');
		}
	}

	function onBeforeRender() {
		$app = JFactory::getApplication();

		if (!defined('D4JINSTANT_INSTALLED')) {
			return;
		}

		if ($app->isSite()) {
			return;
		}

		if (!$this->access_token) {
			return;
		}

		if (!$this->isAccessable()) {
			return;
		}

		$isSupported = false;
		if ($this->isContent()) {
			$isSupported = true;
			$id = $app->input->get('id');
			$this->addAjaxData('content', $id);
		}

		if ($this->isK2()) {
			$isSupported = true;
			$cid = $app->input->get('cid');
			$this->addAjaxData('k2', $cid);
	
		}
		
		if ($this->isZoo()) {
			$isSupported = true;
			$cid = $app->input->get('cid');
			$id = $cid[0];
			$this->addAjaxData('zoo', $id);
		}
		
		if ($isSupported) {
			if ($this->d4jparams->get('use_button')) {
				$this->addImportButton();
			}

			if ($this->d4jparams->get('use_post_button')) {
				$this->addFacebookPostButton();
			}
		}
	}

	function addMetaTag() {
		$doc = JFactory::getDocument();
		$meta = '<meta property="fb:pages" content="' . $this->d4jparams->get('pageid') . '" />';
		$doc->addCustomTag($meta);
	}
	
	function isZoo() {
		$app = JFactory::getApplication();
		$option = $app->input->get('option');
		$controller = $app->input->get('controller');
		$task = $app->input->get('task');
		$cid = $app->input->get('cid');
		if ($option === 'com_zoo' && $controller === 'item' && $task === 'edit' && !empty($cid[0])) {
			$categories = $this->d4jparams->get('zooblogcategory', array());
	
			$id = $cid[0];
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('i.*')
				->select($db->qn('i.id'))
				->from($db->qn('#__zoo_item', 'i'))
				->join('left', $db->qn('#__zoo_category_item', 'ci') . ' ON (' . $db->qn('ci.item_id') . ' = ' . $db->qn('i.id') . ')')
				->where($db->qn('i.id') . '=' . $db->q($id))
				->where($db->qn('i.type') . ' = ' . $db->q('article'))
				->where($db->qn('i.state') . ' = ' . $db->q(1))
				->where($db->qn('ci.category_id') . ' IN (' . implode(',', $db->q($categories)) . ')')
				->group('i.id');
			$db->setQuery($query);
			$item = $db->loadResult();
			if ($item) {
				return true;
			} else {
				return false;
			}
		}
		
		return false;
	}
	
	function isContent() {
		$app = JFactory::getApplication();
		$option = $app->input->get('option');
		$view = $app->input->get('view');
		$layout = $app->input->get('layout');
		$id = $app->input->get('id');
		if ($option === 'com_content' && $view === 'article' && $layout === 'edit' && $id) {
			$listcat = $this->d4jparams->get('content_category', array());
			$db = JFactory::getDbo();
			$query = "SELECT catid FROM #__content WHERE id =" . $id;
			$catid = $db->setQuery($query)->loadResult();
			if (in_array($catid, $listcat)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}

	function isK2() {
		$app = JFactory::getApplication();
		$option = $app->input->get('option');
		$view = $app->input->get('view');
		$cid = $app->input->get('cid');
		if ($option === 'com_k2' && $view === 'item' && $cid) {
			$listcat = $this->d4jparams->get('k2_category', array());
			$db = JFactory::getDbo();
			$query = "SELECT catid FROM #__k2_items WHERE id =" . $cid;
			$catid = $db->setQuery($query)->loadResult();
			if (in_array($catid, $listcat)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}

	function isTzPortfolio() {
		
	}

	function addAjaxData($type, $id) {
		require_once JPATH_ADMINISTRATOR . '/components/com_d4jinstant/helpers/base.php';
		$helper = new D4jBase;
		$data = new stdClass;
		$data->type = $type;
		$data->key = md5($this->d4jparams->get('access_token'));
		$data->importid = $id;
		$script = 'var d4jajax = { url: "' . $helper->getSiteUrl() . '", data:' . json_encode($data) . ' };';
		JFactory::getDocument()->addScriptDeclaration($script);
		$doc = JFactory::getDocument();
		$doc->addStyleSheet(JUri::base() . 'components/com_d4jinstant/assets/css/style.css');
	}

	function addImportButton() {
		$path = JPATH_ADMINISTRATOR . '/components/com_d4jinstant/layouts/';
		$layout = new JLayoutFile('importbutton', $path);
		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton('Custom', $layout->render(array()), 'facebook2');
	}

	function addFacebookPostButton() {
		$path = JPATH_ADMINISTRATOR . '/components/com_d4jinstant/layouts/';
		$layout = new JLayoutFile('facebookbutton', $path);
		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton('Custom', $layout->render(array()), 'facebook2');
	}

	function isAccessable() {
		$user = JFactory::getUser();
		$usergroup = $this->d4jparams->get('usergroup', array());
		$intersect_groups = array_intersect($user->groups, $usergroup);
		if (empty($intersect_groups)) {
			return false;
		} else {
			return true;
		}
	}

	function isD4jInstantInstalled() {
		$query = "SELECT extension_id FROM #__extensions WHERE element = 'com_d4jinstant'";
		$db = JFactory::getDbo();
		$result = $db->setQuery($query)->loadResult();
		return $result;
	}

}
