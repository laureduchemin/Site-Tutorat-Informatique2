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
 * @version     3.5.0 2015-02-25
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport( 'joomla.filesystem.path' );
jimport('joomla.form.formfield');

/**
 * Supports unlimited modal datetime picker (add / delete).
 *
 * @package		iCagenda
 * @subpackage	com_icagenda
 * @since		1.0
 */
class JFormFieldModal_date extends JFormField
{
	protected $type = 'modal_date';

	protected function getInput()
	{
		$id = JRequest::getInt('id');
		$class = !empty($this->class) ? ' ' . $this->class : '';

		$session = JFactory::getSession();
		$datesDB = $session->get('ic_submit_dates', '');

		if ($id && empty($datesDB))
		{
			$db	= JFactory::getDBO();
			$db->setQuery(
				'SELECT a.dates' .
				' FROM #__icagenda_events AS a' .
				' WHERE a.id = '.(int) $id
			);
			$datesDB = $db->loadResult();
		}

		$dates = iCString::isSerialized($datesDB) ? unserialize($datesDB) : false;

		$html = '<table id="dTable" style="border:0px">';

		$html.= '<thead>';
		$html.= '<tr>';
		$html.= '<th width="70%">';
		$html.= JText::_('COM_ICAGENDA_TB_DATE');
		$html.= '</th>';
		$html.= '<th width="30%">';
		$html.= JText::_('COM_ICAGENDA_TB_ACT');
		$html.= '</th>';
		$html.= '</tr>';
		$html.= '</thead>';

		if ($dates
			&& $dates != array('0000-00-00 00:00'))
		{
			foreach ($dates as $date)
			{
				$html.= '<tr>';
				$html.= '<td>';
				$html.= '<input class="ic-date-input" type="text" name="d" value="' . $date . '" />';
				$html.= '</td>';
				$html.= '<td>';
				$html.= '<a class="del" href="#">' . JText::_('COM_ICAGENDA_DELETE_DATE') . '</a>';
				$html.= '</td>';
				$html.= '</tr>';
			}

			// clear the data so we don't process it again
			$session->clear('ic_submit_dates');
		}
		else
		{
			$html.= '<tr>';
			$html.= '<td>';
			$html.= '<input class="ic-date-input" type="text" name="d" value="0000-00-00 00:00" />';
			$html.= '</td>';
			$html.= '<td>';
			$html.= '<a class="del" href="#">' . JText::_('COM_ICAGENDA_DELETE_DATE') . '</a>';
			$html.= '</td>';
			$html.= '</tr>';
		}

		$html.= '</table>';

		$html.= '<a id="add" href="#">'.JText::_('COM_ICAGENDA_ADD_DATE').'</a><br/>';

		$html.= '<input type="hidden"';
		$html.= ' class="date' . $class . '"';
		$html.= ' id="' . $this->id . '_id"';
		$html.= ' name="' . $this->name . '"';
		$html.= ' value=\''.$datesDB.'\'';
		$html.= '/>';

		return $html;
	}
}
