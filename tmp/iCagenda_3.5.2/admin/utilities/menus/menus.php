<?php
/**
 *------------------------------------------------------------------------------
 *  iCagenda v3 by Jooml!C - Events Management Extension for Joomla! 2.5 / 3.x
 *------------------------------------------------------------------------------
 * @package     iCagenda
 * @subpackage  utilities
 * @copyright   Copyright (c)2014-2015 Cyril Rezé, Jooml!C - All rights reserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Cyril Rezé (Lyr!C)
 * @link        http://www.joomlic.com
 *
 * @version     3.5.0 2015-02-17
 * @since       3.4.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

/**
 * class icagendaCategories
 */
class icagendaMenus
{
	/**
	 * Function to return all published 'List of Events' menu items
	 *
	 * @access	public static
	 * @param	none
	 * @return	array of menu item info this way : Itemid-mcatid-lang
	 *
	 * @since	3.4.0
	 */
	static public function iClistMenuItemsInfo()
	{
		$app = JFactory::getApplication();

		// List all menu items linking to list of events
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('m.title, m.published, m.id, m.params, m.language')
			->from('`#__menu` AS m')
			->where( "(m.link = 'index.php?option=com_icagenda&view=list') AND (m.published = 1)" );

		if (JLanguageMultilang::isEnabled())
		{
			$query->where('m.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$db->setQuery($query);
		$link = $db->loadObjectList();

		$iC_list_menus = array();

		foreach ($link as $iClistMenu)
		{
			$menuitemid	= $iClistMenu->id;
			$menulang	= $iClistMenu->language;

			if ($menuitemid)
			{
				$menu		= $app->getMenu();
				$menuparams	= $menu->getParams( $menuitemid );
			}

			$mcatid		= $menuparams->get('mcatid');
			$menufilter	= $menuparams->get('time');

			if (is_array($mcatid))
			{
				$mcatid	= implode(',', $mcatid);
			}

			array_push($iC_list_menus, $menuitemid . '_' . $mcatid . '_' . $menulang . '_' . $menufilter);
		}

		return $iC_list_menus;
	}

	/**
	 * Function to return all published 'List of Events' menu items
	 *
	 * @access	public static
	 * @param	none
	 * @return	array of menu item info this way : Itemid-mcatid-lang
	 *
	 * @since	3.4.0
	 */
	static public function iClistMenuItems()
	{
		$app = JFactory::getApplication();

		// List all menu items linking to list of events
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('m.title, m.published, m.id, m.params, m.language')
			->from('`#__menu` AS m')
			->where( "(m.link = 'index.php?option=com_icagenda&view=list') AND (m.published = 1)" );

		if (JLanguageMultilang::isEnabled())
		{
			$query->where('m.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$query->order('m.id ASC');

		$db->setQuery($query);
		$iC_list_menu_items = $db->loadObjectList();

		if ($iC_list_menu_items)
		{
			return $iC_list_menu_items;
		}
		else
		{
			return array();
		}
	}
}
