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
 * @version     3.4.1 2015-01-30
 * @since       2.0.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.filesystem.path' );
jimport('joomla.form.formfield');

/**
 * Form Field to load a enddate datetime picker input
 *
 * @since	2.0.0
 */
class JFormFieldModal_enddate extends JFormField
{
	protected $type = 'modal_enddate';

	protected function getInput()
	{
		$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';

		$html ='<input type="text" id="enddate"' . $class . ' name="' . $this->name . '" value="' . $this->value . '"/>';

		return $html;
	}
}
