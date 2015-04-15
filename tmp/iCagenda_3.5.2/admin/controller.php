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
 * @version     3.1.0 2013-07-26
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

if(!class_exists('iCJController')) {
   if(version_compare(JVERSION,'3.0.0','ge')) {
      class iCJController extends JControllerLegacy {
      };
   } else {
      jimport('joomla.application.component.controller');
      class iCJController extends JController {};
   }
}

/**
 * Controller class - iCagenda.
 */
class iCagendaController extends iCJController
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/icagenda.php';

		// Set Input J3
		$iCinput =JFactory::getApplication()->input;

		// Load the submenu.
		if(version_compare(JVERSION, '3.0', 'lt')) {
			iCagendaHelper::addSubmenu(JRequest::getCmd('view', 'icagenda'));
			$view = JRequest::getCmd('view', 'icagenda');
			JRequest::setVar('view', $view);
		} else {
			iCagendaHelper::addSubmenu($iCinput->get('view', 'icagenda'));
			$view = $iCinput->get('view', 'icagenda');
			$iCinput->set('view', $view);
		}

		parent::display();

		return $this;
	}
}
