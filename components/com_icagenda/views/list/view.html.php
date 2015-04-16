<?php
/**
 *------------------------------------------------------------------------------
 *  iCagenda v3 by Jooml!C - Events Management Extension for Joomla! 2.5 / 3.x
 *------------------------------------------------------------------------------
 * @package     com_icagenda
 * @copyright   Copyright (c)2012-2015 Cyril Rezé, Jooml!C - All rights reserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Cyril Rezé (Lyr!C)
 * @link        http://www.joomlic.com
 *
 * @version     3.5.2 2015-03-11
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.application.component.helper');

/**
 * HTML View class - iCagenda.
 */
class icagendaViewList extends JViewLegacy
{
	protected $params;
	protected $data;
	protected $getAllDates;
	protected $form;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app				= JFactory::getApplication();
		$document			= JFactory::getDocument();
		$this->params		= $app->getParams();

		// loading data
		$this->data			= $this->get('Data');
		$this->getAllDates	= icagendaEventsData::getAllDates();
		$this->form			= $this->get('Form'); // Registration Form

		$params	= $this->params;

		// Menu Options
		$this->atlist		= $params->get('atlist', 0);
		$this->template		= $params->get('template');
		$this->title		= $params->get('title');
		$this->number		= $params->get('number', 5);
		$this->orderby		= $params->get('orderby', 2);
		$this->time			= $params->get('time', 1);

		// Component Options
		$this->iconPrint_global			= $params->get('iconPrint_global', 0);
		$this->iconAddToCal_global		= $params->get('iconAddToCal_global', 0);
		$this->iconAddToCal_options		= $params->get('iconAddToCal_options', 0);
		$this->copy						= $params->get('copy');
		$this->navposition				= $params->get('navposition', 1);
		$this->arrowtext				= $params->get('arrowtext', 1);
		$this->GoogleMaps				= $params->get('GoogleMaps', 1);
		$this->pagination				= $params->get('pagination', 1);
		$this->day_display_global		= $params->get('day_display_global', 1);
		$this->month_display_global		= $params->get('month_display_global', 1);
		$this->year_display_global		= $params->get('year_display_global', 1);
		$this->time_display_global		= $params->get('time_display_global', 0);
		$this->venue_display_global		= $params->get('venue_display_global', 1);
		$this->city_display_global		= $params->get('city_display_global', 1);
		$this->country_display_global	= $params->get('country_display_global', 1);
		$this->shortdesc_display_global	= $params->get('shortdesc_display_global', '');
		$this->statutReg				= $params->get('statutReg', 0);
		$this->dates_display			= $params->get('datesDisplay', 1);
		$this->reg_captcha				= $params->get('reg_captcha', 0);
		$this->reg_form_validation		= $params->get('reg_form_validation', '');

		$this->cat_description	= ($params->get('displayCatDesc_menu', 'global') == 'global')
								? $params->get('CatDesc_global', '0')
								: $params->get('displayCatDesc_menu', '');

		$cat_options			= ($params->get('displayCatDesc_menu', 'global') == 'global')
								? $params->get('CatDesc_checkbox', '')
								: $params->get('displayCatDesc_checkbox', '');
		$this->cat_options		= is_array($cat_options) ? $cat_options : array();

		$this->pageclass_sfx	= htmlspecialchars($params->get('pageclass_sfx'));

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$vcal = $app->input->get('vcal');

		if ($vcal)
		{
			$tpl = 'vcal';
		}

		// Process the content plugins.
		JPluginHelper::importPlugin('content');

		if (version_compare(JVERSION, '3.0', 'ge')) // J3
		{
			$this->dispatcher	= JEventDispatcher::getInstance();
		}
		else // J2.5
		{
			$this->dispatcher	= JDispatcher::getInstance();
		}

		$this->_prepareDocument();

		parent::display($tpl);

		icagendaEvents::isListOfEvents();

		$jlayout		= JRequest::getCmd('layout', '');
		$layouts_array	= array('event', 'registration');
		$layout			= in_array($jlayout, $layouts_array) ? $jlayout : '';

		JHtml::stylesheet( 'components/com_icagenda/add/css/style.css' ); // To Be Removed

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			// Joomla 2.5
			$document->addStyleSheet( JURI::base( true ) . '/components/com_icagenda/add/css/icagenda.j25.css' );

			JHtml::_('behavior.mootools');

			// load jQuery, if not loaded before
			$scripts = array_keys($document->_scripts);
			$scriptFound = false;

			for ($i = 0; $i < count($scripts); $i++)
			{
				if (stripos($scripts[$i], 'jquery.min.js') !== false
					|| stripos($scripts[$i], 'jquery.js') !== false)
				{
					$scriptFound = true;
				}
			}

			// jQuery Library Loader
			if (!$scriptFound)
			{
				// load jQuery, if not loaded before
				if (!$app->get('jquery'))
				{
					$app->set('jquery', true);

					// Add jQuery Library
					$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js');
					$document->addScript( JURI::base( true ) . '/components/com_icagenda/js/jquery.noconflict.js');
				}
			}
		}
		else
		{
			// Joomla 3
			JHtml::_('bootstrap.framework');
			JHtml::_('jquery.framework');
		}

		// Loading Script tipTip used for iCtips
		JHtml::script( 'com_icagenda/jquery.tipTip.js', false, true );

		if (!$layout || $layout == 'list')
		{
			// Add RSS Feeds
			$menu = $app->getMenu()->getActive()->id;

			$feed = 'index.php?option=com_icagenda&amp;view=list&amp;Itemid=' . (int) $menu . '&amp;format=feed';
			$rss = array(
				'type'    =>  'application/rss+xml',
				'title'   =>   'RSS 2.0');

			$document->addHeadLink(JRoute::_($feed.'&amp;type=rss'), 'alternate', 'rel', $rss);
		}
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway 	= $app->getPathway();
		$title 		= null;

		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description', ''))
		{
			$this->document->setDescription($this->params->get('menu-meta_description', ''));
		}

		if ($this->params->get('menu-meta_keywords', ''))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords', ''));
		}

		if ($app->getCfg('MetaTitle') == '1'
			&& $this->params->get('menupage_title', ''))
		{
			$this->document->setMetaData('title', $this->params->get('page_title', ''));
		}
	}
}
