<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT_ADMINISTRATOR . '/vendor/autoload.php';

class D4jInstantModelSetup extends JModelLegacy {

    function getFacebook() {
        $app = JFactory::getApplication();
        $fb = new Facebook\Facebook([
            'app_id' => $app->getUserState('facebook.appid'),
            'app_secret' => $app->getUserState('facebook.secret'),
            'default_graph_version' => 'v4.0',
        ]);
        return $fb;
    }

    function getLoginUrl() {
        $app = JFactory::getApplication();
        $app->getUserStateFromRequest('facebook.appid', 'appid');
        $app->getUserStateFromRequest('facebook.secret', 'secret');
        $fb = $this->getFacebook();
        $helper = $fb->getRedirectLoginHelper();
        $permissions = ['manage_pages', 'pages_manage_instant_articles,publish_pages'];
        $url = JURI::current() . '?option=com_d4jinstant&task=setup.getPages';
        return $helper->getLoginUrl($url, $permissions);
    }

    function getPages() {
        $app = JFactory::getApplication();
        $input = $app->input;
        $accessToken = $input->get('token');
        $fb = $this->getFacebook();
        $client = $fb->getOAuth2Client();
        try {
            // Returns a long-lived access token
            $accessTokenLong = $client->getLongLivedAccessToken($accessToken);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // There was an error communicating with Graph
            echo $e->getMessage();
            exit;
        }

        $fb->setDefaultAccessToken($accessTokenLong);

        try {
            $pages = $fb
                    ->get('/me/accounts?fields=name,id,access_token,supports_instant_articles,picture')
                    ->getGraphEdge()
                    ->asArray();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK re	turned an error: ' . $e->getMessage();
            exit;
        }

        $pages = array_filter( $pages, function( $page ) {
            return $page['supports_instant_articles'];
        });  
        
        if (count($pages)) {
            $app->setUserState('facebook.pages', $pages);
            
            $pages = array_map( function( $page ) {
                unset($page['access_token']);
                return $page;
            }, $pages );
            
            return $pages;
        } else {
            return;
        }
    }

    function savePage() {
        $app = JFactory::getApplication();
        $input = $app->input;
        $pageid = $input->get('pageid');
        $pages = $app->getUserState('facebook.pages');
        $page = array();

        foreach ( $pages as $item ) {
            if ( $item['id'] == $pageid ) {
                $page = $item;
                break;
            }
        }
        
        if (count($page)) {
            $app = JFactory::getApplication();
            $params = JComponentHelper::getParams('com_d4jinstant');
            $params->set('appid', $app->getUserState('facebook.appid'));
            $params->set('secret', $app->getUserState('facebook.secret'));
            $params->set('pageid', $page['id']);
            $params->set('access_token', $page['access_token']);
            $params->set('name', $page['name']);
            $data = $params->toString();
            $db = JFactory::getDbo();
            $q = 'UPDATE #__extensions SET params = ' . $db->quote($data) . ' WHERE element = "com_d4jinstant"';
            $db->setQuery($q);
            $db->execute();
            $this->cleanCache('_system', 0);
            $this->cleanCache('_system', 1);
            return true;
        } else {
            return false;
        }
    }

    function saveApp() {
        $app = JFactory::getApplication();
        $input = $app->input;
        $appid = $input->get('appid');
        $secret = $input->get('secret');
        $app->setUserState('facebook.appid', $appid);
        $app->setUserState('facebook.secret', $secret);
        $this->cleanCache('_system', 0);
        $this->cleanCache('_system', 1);
    }

    function resetApp() {
        $app = JFactory::getApplication();
        $app->setUserState('facebook.appid', '');
        $this->cleanCache('_system', 0);
        $this->cleanCache('_system', 1);
    }

}
