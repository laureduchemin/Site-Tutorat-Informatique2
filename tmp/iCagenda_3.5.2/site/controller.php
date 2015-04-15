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
 * @version 	3.3.0 2014-03-05
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

require_once(JPATH_COMPONENT_SITE . '/helpers/iCicons.class.php');

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
 * Controller class for iCagenda.
 */
class iCagendaController extends iCJController
{
	public function display($cachable = false, $urlparams = false)
	{
		$paramsC 	= JComponentHelper::getParams('com_icagenda');
		$cache 		= $paramsC->get( 'enable_cache', 0 );
		$cachable 	= false;

		if ($cache == 1) {
			$cachable 	= true;
		}

		$document 	= JFactory::getDocument();

		$safeurlparams = array('catid'=>'INT','id'=>'INT','Itemid'=>'INT','cid'=>'ARRAY','year'=>'INT','month'=>'INT','limit'=>'INT','limitstart'=>'INT',			'showall'=>'INT','return'=>'BASE64','filter'=>'STRING','filter_order'=>'CMD','filter_order_Dir'=>'CMD','filter-search'=>'STRING','print'=>'BOOLEAN','lang'=>'CMD');

		parent::display($cachable,$safeurlparams);

		return $this;
	}
}
