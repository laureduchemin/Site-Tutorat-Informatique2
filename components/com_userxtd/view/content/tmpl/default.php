<?php
/**
 * Part of joomla34 project. 
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

/**
 * @var \Windwalker\View\Engine\PhpEngine $this
 */

$params = $this->params = $data->params;

foreach ($data as $k => $v)
{
	$this->$k = $v;
}

// Templates
$app = JFactory::getApplication();
$layout = JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_content/category/blog_item.php';

if (!is_file($layout))
{
	$layout = \Windwalker\Helper\PathHelper::getSite('com_content') . '/views/category/tmpl/blog_item.php';
}

?>
<div class="row-fluid">
	<div class="span12">
		<?php
		$article = new stdClass;
		$article->created_by = $data->user->id;
		echo \Userxtd\Content\UserInfo::createInfoBox(\Windwalker\DI\Container::getInstance('com_userxtd'), $article);
		?>

		<?php foreach ($data->items as $item): ?>
			<?php
			$this->item = $item;
			$this->item->readmore = true;

			$this->params->set('access-view', true);
			?>
			<div class="article-content userxtd-content">
				<?php include $layout; ?>
			</div>
		<?php endforeach; ?>

		<hr />

		<?php echo $data->pagination->getListFooter(); ?>
	</div>
</div>