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
 * @version     3.0 2013-06-04
 * @since       2.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport( 'joomla.filesystem.path' );
jimport('joomla.form.formfield');

class JFormFieldModal_mailinglist extends JFormField
{
	protected $type='modal_mailinglist';

	protected function getInput()
	{

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('e.*')->from('`#__icagenda_events` AS e');
		$db->setQuery($query);
		$evs = $db->loadObjectList();

		// Set Input J3
		$iCinput =JFactory::getApplication()->input;


		$events=array();

		foreach ($evs as $e){
			$qr = $db->getQuery(true);
			$qr->select('r.email, r.eventid, r.state, r.date')->from('`#__icagenda_registration` AS r')->where('r.eventid='.$e->id);
			$db->setQuery($qr);
			$r = $db->loadObjectList();
			$list='';
if(version_compare(JVERSION, '3.0', 'lt')) {
			$date=JRequest::getVar('date');
			$class=JRequest::getVar('class');
} else {
			$date=$iCinput->get('date');
			$class=$iCinput->get('class');
}

			foreach ($r as $v){
				if(isset($v->state)){$userpub= $v->state;} else {$userpub='0';}
				if(isset($v->date))$userdate= $v->date;
				if ($userpub > 0) {
					$list.=$v->email.', ';
//					$date.=$v->date.', ';
				}
			}
			$id= $e->id;
			if(isset($v->eventid)){$regist= $v->eventid;} else {$regist='0';}
			if(isset($v->state))$userpub= $v->state;

			if (($id == $regist) AND ($userpub > 0)) {
			$events[$e->id]=array(
				'title'=>$e->title,
				'id'=>$regist,
//				'next'=>$e->next,
				'list'=>$list,
				'date'=>$date
			);
			}
		}

		$html= '
			<select id="'.$this->eventid.'_id"'.$class.' name="'.$this->name.'">';
		foreach ($events as $k=>$v){
			$html.='<option value="'.$v['list'].'"';
			if ($this->value == $v['list']){
				$html.='selected="selected"';
			}
//			$html.='>'.$v['title'].' | '.$v['next'].'</option>';
			$html.='>['.$v['id'].'] '.$v['title'].'</option>';
		}
		$html.='</select>';

		return $html;
	}
}
