<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

JLoader::register('D4jImporter', JPATH_ADMINISTRATOR . '/components/com_d4jinstant/helpers/importer.php');

$app = JFactory::getApplication();
$menu = $app->getMenu();
$home = $menu->getDefault();
$importer = new D4jImporter;
?>
<?php if(count($this->list)): ?>

<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
  <channel>
    <title><?php echo $home->title ?></title>
    <link><?php echo JUri::root() ?></link>
    <description>
			<?php echo $home->params->get('menu-meta_description') ?>
    </description>
    <language><?php echo $app->getLanguage()->getTag() ?></language>
    <lastBuildDate><?php echo date("Y-m-d\TH:i:s") ?></lastBuildDate>
		<?php foreach($this->list as $item): ?>
    <item>
      <title><?php echo $item->title ?></title>
      <link><?php echo $item->url ?></link>
      <guid><?php echo md5($item->id) ?></guid>
      <pubDate><?php echo gmdate("Y-m-d\TH:i:s", strtotime($item->publish_up)) ?></pubDate>
      <author><?php echo $item->author ?></author>
      <content:encoded>
        <![CDATA[
						<?php 
						ob_start();
						$instant_article = $importer->getInstantArticle($item);
						ob_end_clean();
						echo $instant_article->render('', true);
						?>
        ]]>
      </content:encoded>
    </item>
		<?php endforeach; ?>

  </channel>
</rss>

<?php endif;