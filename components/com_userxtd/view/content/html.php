<?php
/**
 * Part of Component Userxtd files.
 *
 * @copyright   Copyright (C) 2014 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Registry\Registry;
use Windwalker\View\Html\ItemHtmlView;

// No direct access
defined('_JEXEC') or die;

/**
 * Userxtd Users view
 *
 * @since 1.0
 */
class UserxtdViewContentHtml extends ItemHtmlView
{
	/**
	 * The component prefix.
	 *
	 * @var  string
	 */
	protected $prefix = 'userxtd';

	/**
	 * The component option name.
	 *
	 * @var string
	 */
	protected $option = 'com_userxtd';

	/**
	 * The text prefix for translate.
	 *
	 * @var  string
	 */
	protected $textPrefix = 'COM_USERXTD';

	/**
	 * The item name.
	 *
	 * @var  string
	 */
	protected $name = 'content';

	/**
	 * The item name.
	 *
	 * @var  string
	 */
	protected $viewItem = 'content';

	/**
	 * The list name.
	 *
	 * @var  string
	 */
	protected $viewList = 'content';

	/**
	 * prepareRender
	 *
	 * @return  void
	 */
	protected function prepareRender()
	{
		\Windwalker\Helper\LanguageHelper::loadLanguage('com_content');

		// Fix for 3.3.6
		JHtml::addIncludePath(\Windwalker\Helper\PathHelper::getSite('com_content') . '/helpers');

		parent::prepareRender();
	}

	/**
	 * Prepare data hook.
	 *
	 * @return  void
	 */
	protected function prepareData()
	{
		require_once JPATH_SITE . '/components/com_content/helpers/route.php';

		$app  = JFactory::getApplication();

		$data = $this->getData();
		$data->params = JComponentHelper::getParams('com_content');
		$data->user   = $user = JUser::getInstance($app->input->getUsername('username'));

		JModelLegacy::addIncludePath(\Windwalker\Helper\PathHelper::getSite('com_content') . '/models');
		$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		$model->setState('params', $data->params);

		$access     = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));

		$model->setState('filter.published', 1);
		$model->setState('filter.access', $access);
		$model->setState('filter.author_id', (int) $user->id);
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'DESC');
		$model->setState('list.limit', 10);

		$data->items = $model->getItems();
		$data->pagination = $model->getPagination();

		foreach ($data->items as &$item)
		{
			$item->slug    = $item->id . ':' . $item->alias;
			$item->catslug = $item->catid . ':' . $item->category_alias;

			$item->params = $data->params;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
			}
			else
			{
				$item->link = JRoute::_('index.php?option=com_users&view=login');
			}

			$item->parent_slug = ($item->parent_alias) ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

			// No link for ROOT category
			if ($item->parent_alias == 'root')
			{
				$item->parent_slug = null;
			}

			$item->event   = new stdClass;

			$dispatcher = JEventDispatcher::getInstance();

			// Old plugins: Ensure that text property is available
			if (!isset($item->text))
			{
				$item->text = $item->introtext;
			}

			$app->input->set('option', 'com_content');
			$app->input->set('view', 'category');
			$app->input->set('layout', 'blog');

			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array ('com_content.category', &$item, &$item->params, 0));

			// Old plugins: Use processed text as introtext
			$item->introtext = $item->text;

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_content.category', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_content.category', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_content.category', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));

			$app->input->set('option', 'com_userxtd');
			$app->input->set('view', 'content');
			$app->input->set('layout', 'default');
		}

		$this->setTitle();
	}

	/**
	 * Set title of this page.
	 *
	 * @param string $title Page title.
	 * @param string $icons Title icon.
	 *
	 * @return  void
	 */
	protected function setTitle($title = null, $icons = 'stack')
	{
		$app    = JFactory::getApplication();
		$config = JFactory::getConfig();
		$menus  = $app->getMenu();
		$title  = null;
		$user   = $this->getData()->user;

		$document = \JFactory::getDocument();

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->data->params->def('page_heading', $this->data->params->get('page_title', $menu->title));
		}
		else
		{
			$this->data->params->def('page_heading', JText::sprintf('COM_USERXTD_USERS_ARTICLES_TITLE', $user->name));
		}

		$title = $this->data->params->get('page_title', JText::sprintf('COM_USERXTD_USERS_ARTICLES_TITLE', $user->name));

		if (empty($title))
		{
			$title = $config->get('sitename');
		}
		elseif ($config->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $config->get('sitename'), $title);
		}
		elseif ($config->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $config->get('sitename'));
		}

		$document->setTitle($title);

		if ($this->data->params->get('menu-meta_description'))
		{
			$document->setDescription($this->data->params->get('menu-meta_description'));
		}

		if ($this->data->params->get('menu-meta_keywords'))
		{
			$document->setMetadata('keywords', $this->data->params->get('menu-meta_keywords'));
		}

		if ($this->data->params->get('robots'))
		{
			$document->setMetadata('robots', $this->data->params->get('robots'));
		}
	}
}
