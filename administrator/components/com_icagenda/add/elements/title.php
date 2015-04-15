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
 * @version     3.4.0 2014-06-02
 * @since       1.2.3
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

// Test if translation is missing, set to en-GB by default
$language = JFactory::getLanguage();
$language->load('com_icagenda', JPATH_ADMINISTRATOR, 'en-GB', true);
$language->load('com_icagenda', JPATH_ADMINISTRATOR, null, true);


class JFormFieldTitle extends JFormField
{
	protected $type = 'Title';

	protected function getInput()
	{
		return ' ';
	}

	protected function getLabel()
	{
		$html = array();

    	// Affichage texte

		$document = JFactory::getDocument();
		$document->addStyleSheet( JURI::base().'components/com_icagenda/add/css/icagenda.css' );
		$document->addStyleSheet( JURI::root( true ) . '/media/com_icagenda/icicons/style.css' );

		if(version_compare(JVERSION, '3.0', 'lt'))
		{
			$document->addStyleSheet( JURI::base().'components/com_icagenda/add/css/template.css' );
			$document->addStyleSheet( JURI::base().'components/com_icagenda/add/css/icagenda.j25.css' );
			JHTML::_('behavior.framework');

			// load jQuery, if not loaded before
			$scripts = array_keys($document->_scripts);
			$scriptFound = false;
			$scriptuiFound = false;

			for ($i = 0; $i < count($scripts); $i++)
			{
				if (stripos($scripts[$i], 'jquery.min.js') !== false)
				{
					$scriptFound = true;
				}
				// load jQuery, if not loaded before as jquery
				if (stripos($scripts[$i], 'jquery.js') !== false)
				{
					$scriptFound = true;
				}
				if (stripos($scripts[$i], 'jquery-ui.min.js') !== false)
				{
					$scriptuiFound = true;
				}
			}

			// jQuery Library Loader
			if (!$scriptFound)
			{
				// load jQuery, if not loaded before
				if (!JFactory::getApplication()->get('jquery'))
				{
					JFactory::getApplication()->set('jquery', true);
					// add jQuery
					$document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js');
					$document->addScript( JURI::root( true ) . '/media/com_icagenda/js/jquery.noconflict.js' );
				}
			}

			if (!$scriptuiFound)
			{
				$document->addScript('https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');
			}

			$document->addScript( JURI::root( true ) . '/media/com_icagenda/js/template.js' );
		}

    	$label = $this->element['label'];
		$label = $this->translateLabel ? JText::_($label) : $label;

    	$style = $this->element['style'];
		$style = $this->translateLabel ? JText::_($style) : $style;

    	$class = $this->element['class'];
		$class = $this->translateLabel ? JText::_($class) : $class;


		// Contruction
    	$html[] = "<div class='";
    	$html[] = $class;
    	$html[] = "' ";
    	$html[] = "style='";
    	$html[] = $style;
    	$html[] = "display:block;clear:both;'>";
    	$html[] = $label;
    	$html[] = "</div>";

    	return implode('',$html);
	}
}
