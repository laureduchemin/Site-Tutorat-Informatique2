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
 * @version     3.4.0 2014-10-03
 * @since       3.3.3
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport( 'joomla.filesystem.path' );
jimport('joomla.form.formfield');

class JFormFieldModal_evt extends JFormField
{
	protected $type='modal_evt';

	protected function getInput()
	{

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('e.title, e.state, e.id, e.params')
			->from('`#__icagenda_events` AS e');
//		$query->leftJoin('LEFT', '#__icagenda_registration AS r ON r.eventid=e.id');
//		$query->where('(e.id = r.eventid)');
		$db->setQuery($query);
		$events = $db->loadObjectList();
		$class = JRequest::getVar('class');
		$id = JRequest::getVar('id', null);
		$eventid = JRequest::getVar('eventid', $this->value);


		$html = '<div style="margin-bottom: 10px">';
		$html.= '<select id="'.$this->id.'_id"'.$class.' name="'.$this->name.'"  onChange="Refresh(this.value)">';

		if (!$id || !$this->value)
		{
			$html.= '<option value="" selected="selected">'.JText::_('COM_ICAGENDA_SELECT_EVENT').'</option>';
		}

		$typeReg = '';

		foreach ($events as $e)
		{
			if ($e->state == '1')
			{
				$html.='<option value="'.$e->id.'"';
				if ($eventid == $e->id)
				{
					$eventparam = new JRegistry($e->params);
					$typeReg = $eventparam->get('typeReg', 1);
					$html.='selected="selected"';
				}
				$html.='>'.$e->title.'</option>';
			}
//			else
//			{
//				$html.='<option value="'.$e->id.'"></option> ';

//				$html.='<option value="'.$e->id.'"';

//				if ($eventid == $e->id)
//				{
//					$eventparam = new JRegistry($e->params);
//					$typeReg = $eventparam->get('typeReg', 1);
//					$html.='selected="selected"';
//				}

//				if ($e->state == '1')
//				{
//					$html.='>'.$e->title.'</option>';
//				}
//				else
//				{
//					$html.='>'.$e->title.' (NOT PUBLISHED)</option>';
//				}
//			}
		}
		$html.='</select>';
		$html.='</div>';

		$id_display = $id ? '&id='.$id : '';


		$html.='<script type="text/javascript">
				function Refresh(id){
				location.href="' . JURI::base() . 'index.php?option=com_icagenda&view=registration&layout=edit'.$id_display.'&eventid=" + id
				}
				</script>';

		$by_date_opt = '<br /><strong>'.JText::_('COM_ICAGENDA_REG_BY_INDIVIDUAL_DATE').'</strong>';
		$all_period_opt = '<br /><strong>'.JText::_('COM_ICAGENDA_REG_FOR_ALL_DATES').'</strong>';
		$by_date_and_all_period_opt = '<br /><strong>'.JText::_('COM_ICAGENDA_REG_BY_DATE_OR_PERIOD').'</strong>';

		if ($typeReg == 1)
		{
			$html.='<div class="alert alert-info">';
			$html.='<small>'.JText::sprintf('COM_ICAGENDA_REGISTRATION_TYPE_FOR_THIS_EVENT', $by_date_opt).'</small>';
			$html.='</div>';
		}
		elseif ($typeReg == 2)
		{
			$html.='<div class="alert alert-info">';
			$html.='<small>'.JText::sprintf('COM_ICAGENDA_REGISTRATION_TYPE_FOR_THIS_EVENT', $all_period_opt).'</small>';
			$html.='</div>';
		}
		else
		{
			$html.='<div class="alert alert-info">';
			$html.='<small>'.JText::sprintf('COM_ICAGENDA_REGISTRATION_TYPE_FOR_THIS_EVENT', $by_date_and_all_period_opt).'</small>';
			$html.='</div>';
		}

		return $html;
	}
}
