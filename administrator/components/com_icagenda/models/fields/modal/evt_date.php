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
 * @version     3.4.0 2014-11-09
 * @since       3.3.3
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport( 'joomla.filesystem.path' );
jimport('joomla.form.formfield');

class JFormFieldModal_evt_date extends JFormField
{
	protected $type='modal_evt_date';

	protected function getInput()
	{
		$id = JRequest::getVar('id', '0');
		$class = JRequest::getVar('class');

		if ($id != 0)
		{
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			$query->select('r.date AS date, r.eventid AS eventid')
				->from('`#__icagenda_registration` AS r');
			$query->where('('.$id.' = r.id)');
			$db->setQuery($query);
			$result =  $db->loadObject();

			$event_id = $result->eventid;
			$saveddate = $result->date;
		}
		else
		{
			$event_id = '';
			$saveddate = '';
		}

		// Test if date saved in in datetime data format
		$date_is_datetime_sql = false;
		$array_ex_date = array('-', ' ', ':');
		$d_ex = str_replace($array_ex_date, '-', $saveddate);
		$d_ex = explode('-', $d_ex);
		if (count($d_ex) > 4)
		{
			if (   strlen($d_ex[0]) == 4
				&& strlen($d_ex[1]) == 2
				&& strlen($d_ex[2]) == 2
				&& strlen($d_ex[3]) == 2
				&& strlen($d_ex[4]) == 2   )
			{
				$date_is_datetime_sql = true;
			}
		}

		// Test if registered date before 3.3.3 could be converted
		// Control if new date format (Y-m-d H:i:s)
		$input = trim($saveddate);
		$is_valid = date('Y-m-d H:i:s', strtotime($input)) == $input;

		if ($is_valid
			&& strtotime($saveddate))
		{
			$date_get = explode (' ', $saveddate);
			$saved_date = $date_get['0'];
			$saved_time = date('H:i:s', strtotime($date_get['1']));
		}
		else
		{
			// Explode to test if stored in old format in database
			$ex_saveddate = explode (' - ', $saveddate);

			$saved_date = isset($ex_saveddate['0']) ? trim($ex_saveddate['0']) : '';
			$saved_time = isset($ex_saveddate['1']) ? trim(date('H:i:s', strtotime($ex_saveddate['1']))) : '';
		}

		$data_eventid = $event_id;

		$eventid_url = JRequest::getVar('eventid', '');

//		if ( (!$is_valid) && (!$eventid_url || $eventid_url == $data_eventid) && ($event_id) )
		if ( !$date_is_datetime_sql && $saveddate )
		{
			$saveddate_text = '"<b>'.$saveddate.'</b>"';
			echo '<div class="ic-alert ic-alert-note"><span class="iCicon-info"></span> <strong>' . JText::_('NOTICE') . '</strong><br />'
				. JText::sprintf('COM_ICAGENDA_REGISTRATION_ERROR_DATE_CONTROL', $saveddate_text) . '</div>';
		}

		$event_id = isset($event_id) ? $eventid_url : '';

		$this->AllDates($saved_date, $saved_time, $event_id, $saveddate, $data_eventid, $date_is_datetime_sql);
	}


	protected function AllDates ($saved_date, $saved_time, $event_id, $saveddate, $data_eventid, $date_is_datetime_sql)
	{
		// Preparing connection to db
		$db	= JFactory::getDbo();

		// Preparing the query
		$query = $db->getQuery(true);

		// Selectable items
		$query->select('next AS next, dates AS dates,
						startdate AS startdate, enddate AS enddate, weekdays AS weekdays,
						id AS id, state AS state, access AS access, params AS params');
		$query->from('`#__icagenda_events` AS e');
//		$query->where(' e.id = '.$event_id);

		$list_of_dates_id = $event_id ? $event_id : $data_eventid;

		if ($list_of_dates_id)
		{
			$query->where('('.$list_of_dates_id.' = e.id)');
		}
		$db->setQuery($query);

		$allnext = $db->loadObjectList();

		foreach ($allnext as $i)
		{
			// Get Data
			$tNext		= $i->next;
			$tDates		= $i->dates;
			$tId		= $i->id;
			$tState		= $i->state;
			$tEnddate	= $i->enddate;
			$tStartdate	= $i->startdate;
			$tWeekdays	= $i->weekdays;

			$eventparam = new JRegistry($i->params);
			$typeReg = $eventparam->get('typeReg');

			// Declare AllDates array
			$AllDates = array();

			// Get WeekDays setting
			if (isset($tWeekdays)) {$weekdays = $tWeekdays;} else {$weekdays = '';}

			$weekdays = explode (',', $weekdays);
			$weekdaysarray = array();

			foreach ($weekdays as $wed)
			{
				array_push($weekdaysarray, $wed);
			}

			if (in_array('', $weekdaysarray))
			{
				$arrayWeekDays = array(0,1,2,3,4,5,6);
			}
			elseif ($tWeekdays)
			{
				$arrayWeekDays = $weekdaysarray;
			}
			elseif (in_array('0', $weekdaysarray))
			{
				$arrayWeekDays = $weekdaysarray;
			}
			else
			{
				$arrayWeekDays = array(0,1,2,3,4,5,6);
			}
			$WeeksDays = $arrayWeekDays;

			// If Single Dates, added each one to All Dates for this event
			$singledates = unserialize($tDates);

			foreach($singledates as $sd)
			{
				if($sd && $sd != '0000-00-00 00:00' && $sd != '0000-00-00 00:00:00')
				{
					array_push($AllDates, $sd);
				}
			}

			// If Period Dates, added each one to All Dates for this event (filter week Days, and if date not null)
			$perioddates = '';
			$StDate = date('Y-m-d H:i', strtotime($tStartdate));
			$EnDate = date('Y-m-d H:i', strtotime($tEnddate));

			$no_date = '0000-00-00 00:00:00';
			if ( ($tStartdate != $no_date)
				&& ($tEnddate != $no_date) ) $perioddates = $this->getDatesPeriod($StDate, $EnDate);

			$onlyStDate='';
			if (isset($this->onlyStDate)) $onlyStDate=$this->onlyStDate;

			if ((isset ($perioddates)) AND ($perioddates!=NULL))
			{
				foreach ($perioddates as $Dat)
				{
					if (in_array(date('w', strtotime($Dat)), $WeeksDays))
					{
						// May not work in php < 5.2.3 (should return false if date null since 5.2.4)
						$datevalid = strtotime($Dat);
						if ($datevalid || $Dat != '0000-00-00 00:00' || $Dat != '0000-00-00 00:00:00')
						{
							$SingleDate = date('Y-m-d H:i', strtotime($Dat));
							array_push($AllDates, $SingleDate);
						}
					}
				}
			}
		}
		$today = time();

		// get Time Format
		$timeformat = JComponentHelper::getParams('com_icagenda')->get('timeformat', '1');

		if ($timeformat == 1)
		{
			$lang_time = 'H:i';
		}
		else
		{
			$lang_time = 'h:i A';
		}

		if (!empty($AllDates))
		{
			sort($AllDates);
		}

		$eventid_url = JRequest::getVar('eventid', '');


		echo '<div>';
		echo '<select type="hidden" name="'.$this->name.'">';

		if (!$eventid_url || $eventid_url == $data_eventid)
		{
			$date_value = $saveddate;
		}
		else
		{
			$date_value = '';
		}

		$selected = !strtotime($saveddate) ? ' selected="selected"' : '';

		$reg_datetime = date('Y-m-d H:i:s', strtotime($saved_date . ' ' . $saved_time));

		$is_valid = date('Y-m-d H:i:s', strtotime($reg_datetime)) == $saveddate;

		$if_old_value = !$is_valid ? $saveddate : '';

		echo '<option value="'.$if_old_value.'">- ' . JText::_( 'COM_ICAGENDA_REGISTRATION_NO_DATE_SELECTED' ) . ' -</option>';

		$date_exist = false;

		if ($list_of_dates_id)
		{
			foreach($AllDates as $date)
			{
				if ($date && $date != '0000-00-00 00:00' && $date != '0000-00-00 00:00:00')
				{
					$value_datetime = date('Y-m-d H:i:s', strtotime($date));

					echo '<option value="' . $value_datetime . '" ';
					if ($reg_datetime == $value_datetime)
					{
						$date_exist = true;
						echo 'selected="selected"';
					}
					echo '>'.$this->formatDate($date).' - '.date($lang_time, strtotime($date)).'</option>';
				}
			}
		}
		echo '</select>';
		echo '</div>';

//		if ($is_valid
//			&& !$date_exist
//			&& $saved_date != ''
//			&& (!$eventid_url || $eventid_url == $data_eventid) )
//		{
		if (!empty($AllDates) && !in_array(date('Y-m-d H:i', strtotime($saveddate)), $AllDates) && $date_is_datetime_sql )
		{
			$date_no_longer_exists = '<strong>"'.$saveddate.'"</strong>';
			echo '<div class="alert alert-error"><strong>' . JText::_('COM_ICAGENDA_FORM_WARNING') . '</strong><br /><small>' . JText::sprintf('COM_ICAGENDA_REGISTRATION_DATE_NO_LONGER_EXISTS', $date_no_longer_exists) . '</small></div>';
		}

	}


	// Function to get Format Date (using option format, and translation)
	protected function formatDate ($d)
	{
		$mkt_date = strtotime($d);

		// get Format
		$for = JComponentHelper::getParams('com_icagenda')->get('date_format_global', '0');

		// default
		if (($for == NULL) OR ($for == '0')) $for = 'Y - m - d';

		if (!is_numeric($for))
		{
			// update format values, from 2.0.x to 2.1
			if ($for == 'l, d Fnosep Y') {$for = 'l, _ d _ Fnosep _ Y';}
			elseif ($for == 'D d Mnosep Y') {$for = 'D _ d _ Mnosep _ Y';}
			elseif ($for == 'l, Fnosep d, Y') {$for = 'l, _ Fnosep _ d, _ Y';}
			elseif ($for == 'D, Mnosep d, Y') {$for = 'D, _ Mnosep _ d, _ Y';}

			// update format values, from release 2.1.6 and before, to 2.1.7 (using globalization)
			elseif ($for == 'd m Y') {$for = 'd * m * Y';}
			elseif ($for == 'd m y') {$for = 'd * m * y';}
			elseif ($for == 'Y m d') {$for = 'Y * m * d';}
			elseif ($for == 'Y M d') {$for = 'Y * M * d';}
			elseif ($for == 'd F Y') {$for = 'd * F * Y';}
			elseif ($for == 'd M Y') {$for = 'd * M * Y';}
			elseif ($for == 'd msepb') {$for = 'd * m';}
			elseif ($for == 'msepa d') {$for = 'm * d';}
			elseif ($for == 'Fnosep _ d, _ Y') {$for = 'F _ d , _ Y';}
			elseif ($for == 'Mnosep _ d, _ Y') {$for = 'M _ d , _ Y';}
			elseif ($for == 'l, _ d _ Fnosep _ Y') {$for = 'l , _ d _ F _ Y';}
			elseif ($for == 'D _ d _ Mnosep _ Y') {$for = 'D _ d _ M _ Y';}
			elseif ($for == 'l, _ Fnosep _ d, _ Y') {$for = 'l , _ F _ d, _ Y';}
			elseif ($for == 'D, _ Mnosep _ d, _ Y') {$for = 'D , _ M _ d, _ Y';}
			elseif ($for == 'd _ Fnosep') {$for = 'd _ F';}
			elseif ($for == 'Fnosep _ d') {$for = 'F _ d';}
			elseif ($for == 'd _ Mnosep') {$for = 'd _ M';}
			elseif ($for == 'Mnosep _ d') {$for = 'M _ d';}
			elseif ($for == 'Y. F d.') {$for = 'Y . F d .';}
			elseif ($for == 'Y. M. d.') {$for = 'Y . M . d .';}
			elseif ($for == 'Y. F d., l') {$for = 'Y . F d . , l';}
			elseif ($for == 'F d., l') {$for = 'F d . , l';}
		}

		// NEW DATE FORMAT GLOBALIZED 2.1.7

		$lang = JFactory::getLanguage();
		$langTag = $lang->getTag();
		$langName = $lang->getName();
		if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_icagenda/globalization/'.$langTag.'.php'))
		{
			$langTag = 'en-GB';
		}

		$globalize = JPATH_ADMINISTRATOR.'/components/com_icagenda/globalization/'.$langTag.'.php';
		$iso = JPATH_ADMINISTRATOR.'/components/com_icagenda/globalization/iso.php';

		if (is_numeric($for)) {
			require $globalize;
		} else {
			require $iso;
		}

		// Load Globalization Date Format if selected
		if ($for == '1') {$for = $datevalue_1;}
		elseif ($for == '2') {$for = $datevalue_2;}
		elseif ($for == '3') {$for = $datevalue_3;}
		elseif ($for == '4') {$for = $datevalue_4;}
		elseif ($for == '5') {
			if (($langTag == 'en-GB') OR ($langTag == 'en-US')) {
				$for = $datevalue_5;
			} else {
				$for = $datevalue_4;
			}
		}
		elseif ($for == '6') {$for = $datevalue_6;}
		elseif ($for == '7') {$for = $datevalue_7;}
		elseif ($for == '8') {$for = $datevalue_8;}
		elseif ($for == '9') {
			if ($langTag == 'en-GB') {
				$for = $datevalue_9;
			} else {
				$for = $datevalue_7;
			}
		}
		elseif ($for == '10') {
			if ($langTag == 'en-GB') {
				$for = $datevalue_10;
			} else {
				$for = $datevalue_8;
			}
		}
		elseif ($for == '11') {$for = $datevalue_11;}
		elseif ($for == '12') {$for = $datevalue_12;}

		// Explode components of the date
		$exformat = explode (' ', $for);
		$format='';
		$separator = ' ';

		// Day with no 0 (test if Windows server)
		$dayj = '%e';
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
    		$dayj = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $dayj);
		}

		// Date Formatting using strings of Joomla Core Translations (update 3.1.4)
		$dateFormat=date('d-M-Y', $mkt_date);
		if (isset($this->date_separator)) $separator = $this->date_separator;
		foreach($exformat as $k=>$val){
			switch($val){

				// day (v3)
				case 'd': $val=date("d", strtotime("$dateFormat")); break;
				case 'j': $val=strftime("$dayj", strtotime("$dateFormat")); break;
				case 'D': $val=JText::_(date("D", strtotime("$dateFormat"))); break;
				case 'l': $val=JText::_(date("l", strtotime("$dateFormat"))); break;
				case 'dS': $val=strftime(stristr(PHP_OS,"win") ? "%#d" : "%e", strtotime("$dateFormat")).'<sup>'.date("S", strtotime("$dateFormat")).'</sup>'; break;
				case 'jS': $val=strftime("$dayj", strtotime("$dateFormat")).'<sup>'.date("S", strtotime("$dateFormat")).'</sup>'; break;

				// month (v3)
				case 'm': $val=date("m", strtotime("$dateFormat")); break;
				case 'F': $val=JText::_(date("F", strtotime("$dateFormat"))); break;
				case 'M': $val=JText::_(date("F", strtotime("$dateFormat")).'_SHORT'); break;
				case 'n': $val=date("n", strtotime("$dateFormat")); break;

				// year (v3)
				case 'Y': $val=date("Y", strtotime("$dateFormat")); break;
				case 'y': $val=date("y", strtotime("$dateFormat")); break;

				// separators of the components (v2)
				case '*': $val=$separator; break;
				case '_': $val=' '; break;
				case '/': $val='/'; break;
				case '.': $val='.'; break;
				case '-': $val='-'; break;
				case ',': $val=','; break;
				case 'the': $val='the'; break;
				case 'gada': $val='gada'; break;
				case 'de': $val='de'; break;
				case 'г.': $val='г.'; break;
				case 'den': $val='den'; break;
				case 'ukp.': $val = '&#1088;.'; break;


				// day
				case 'N': $val=strftime("%u", strtotime("$dateFormat")); break;
				case 'w': $val=strftime("%w", strtotime("$dateFormat")); break;
				case 'z': $val=strftime("%j", strtotime("$dateFormat")); break;

				// week
				case 'W': $val=date("W", strtotime("$dateFormat")); break;

				// month
				case 'n': $val=$separator.date("n", strtotime("$dateFormat")).$separator; break;

				// time
				case 'H': $val=date("H", strtotime("$dateFormat")); break;
				case 'i': $val=date("i", strtotime("$dateFormat")); break;


				default: $val=''; break;
			}
			if($k!=0)$format.=''.$val;
			if($k==0)$format.=$val;
		}
		return $format;
	}

	function getDatesPeriod($startdate, $enddate)
	{
		if (class_exists('DateInterval'))
		{
			// Create array with all dates of the period - PHP 5.3+
			$start = new DateTime($startdate);

			$interval = '+1 days';
			$date_interval = DateInterval::createFromDateString($interval);

			$timestartdate = date('H:i', strtotime($startdate));
			$timeenddate = date('H:i', strtotime($enddate));
			if ($timeenddate <= $timestartdate)
			{
				$end = new DateTime("$enddate +1 days");
			}
			else
			{
				$end = new DateTime($enddate);
			}

			// Retourne toutes les dates.
			$perioddates = new DatePeriod($start, $date_interval, $end);
			$out = array();
		}
		else
		{
			// Create array with all dates of the period - PHP 5.2
			if (($startdate != $nodate) && ($enddate != $nodate))
			{
				$start = new DateTime($startdate);

				$timestartdate = date('H:i', strtotime($startdate));
				$timeenddate = date('H:i', strtotime($enddate));
				if ($timeenddate <= $timestartdate)
				{
					$end = new DateTime("$enddate +1 days");
				}
				else
				{
					$end = new DateTime($enddate);
				}
				while($start < $end)
				{
					$out[] = $start->format('Y-m-d H:i');
					$start->modify('+1 day');
				}
			}
		}

		// Prépare serialize.
		if (!empty($perioddates))
		{
			foreach($perioddates as $dt)
			{
				$out[] = (
					$dt->format('Y-m-d H:i')
				);
			}
		}
		return $out;
	}
}
