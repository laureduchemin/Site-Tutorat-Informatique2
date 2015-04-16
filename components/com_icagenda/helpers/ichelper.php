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
 * @version     3.5.0 2015-02-20
 * @since       3.2.8
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.application.component.modelitem');
jimport( 'joomla.html.parameter' );
jimport( 'joomla.registry.registry' );

jimport('joomla.user.helper');
jimport('joomla.access.access');

class iCModeliChelper extends JModelItem
{
	// SubTitle Events list
	public static function iCheader($total, $getpage, $arrowtext, $number_per_page, $pagination)
	{
		// loading iCagenda PARAMS
		$app		= JFactory::getApplication();
		$iCparams	= $app->getParams();

		$time = $iCparams->get('time', '1');
		$headerList = $iCparams->get('headerList', '');

		if ($time == '0')
		{
			// COM_ICAGENDA_ALL
			$header_title	= JText::_( 'COM_ICAGENDA_HEADER_ALL_TITLE' );
			$header_many	= JText::sprintf( 'COM_ICAGENDA_HEADER_ALL_MANY_EVENTS', $total );
			$header_one		= JText::sprintf( 'COM_ICAGENDA_HEADER_ALL_ONE_EVENT', $total );
			$header_noevt	= JText::_( 'COM_ICAGENDA_HEADER_ALL_NO_EVENT' );
		}
		elseif ($time == '1')
		{
			// COM_ICAGENDA_OPTION_TODAY_AND_UPCOMING
			$header_title	= JText::_( 'COM_ICAGENDA_HEADER_TODAY_AND_UPCOMING_TITLE' );
			$header_many	= JText::sprintf( 'COM_ICAGENDA_HEADER_TODAY_AND_UPCOMING_MANY_EVENTS', $total );
			$header_one		= JText::sprintf( 'COM_ICAGENDA_HEADER_TODAY_AND_UPCOMING_ONE_EVENT', $total );
			$header_noevt	= JText::_( 'COM_ICAGENDA_HEADER_TODAY_AND_UPCOMING_NO_EVENT' );
		}
		elseif ($time == '2')
		{
			// COM_ICAGENDA_OPTION_PAST
			$header_title	= JText::_( 'COM_ICAGENDA_HEADER_PAST_TITLE' );
			$header_many	= JText::sprintf( 'COM_ICAGENDA_HEADER_PAST_MANY_EVENTS', $total );
			$header_one		= JText::sprintf( 'COM_ICAGENDA_HEADER_PAST_ONE_EVENT', $total );
			$header_noevt	= JText::_( 'COM_ICAGENDA_HEADER_PAST_NO_EVENT' );
		}
		elseif ($time == '3')
		{
			// COM_ICAGENDA_OPTION_FUTURE
			$header_title	= JText::_( 'COM_ICAGENDA_HEADER_UPCOMING_TITLE' );
			$header_many	= JText::sprintf( 'COM_ICAGENDA_HEADER_UPCOMING_MANY_EVENTS', $total );
			$header_one		= JText::sprintf( 'COM_ICAGENDA_HEADER_UPCOMING_ONE_EVENT', $total );
			$header_noevt	= JText::_( 'COM_ICAGENDA_HEADER_UPCOMING_NO_EVENT' );
		}
		elseif ($time == '4')
		{
			// COM_ICAGENDA_OPTION_TODAY
			$header_title	= JText::_( 'COM_ICAGENDA_HEADER_TODAY_TITLE' );
			$header_many	= JText::sprintf( 'COM_ICAGENDA_HEADER_TODAY_MANY_EVENTS', $total );
			$header_one		= JText::sprintf( 'COM_ICAGENDA_HEADER_TODAY_ONE_EVENT', $total );
			$header_noevt	= JText::_( 'COM_ICAGENDA_HEADER_TODAY_NO_EVENT' );
		}

		$report = $report2 = '';

		if ($total == 1)
		{
			$report.= '<span class="ic-subtitle-string">' . $header_one . '</span>';
		}
		if ($total == 0)
		{
			$report.= '<span class="ic-subtitle-string">' . $header_noevt . '</span>';
		}
		if ($total > 1)
		{
			$report.= '<span class="ic-subtitle-string">' . $header_many . '</span>';
		}

		$num = $number_per_page;

		// No display if number does not exist
		if ($num == NULL)
		{
			$pages = 1;
		}
		else
		{
			$pages = ceil($total/$num);
		}

		$page_nb = $getpage;

		if (JRequest::getVar('page') == NULL)
		{
			$page_nb = 1;
		}

		if ($pages <= 1)
		{
			$report2.= '';
		}
		else
		{
			$report2.= ' <span class="ic-subtitle-pages"> - '.JText::_( 'COM_ICAGENDA_EVENTS_PAGE' ).' '.$page_nb.' / '.$pages.'</span>';
		}

		// Tag for header title depending of show_page_heading setting
		$app = JFactory::getApplication();
		$menuItem = $app->getMenu()->getActive();

    	if (is_object($menuItem)
    		&& $menuItem->params->get('show_page_heading', 1))
    	{
			$tag = 'h2';
		}
		else
		{
			$tag = 'h1';
		}

		// Display Header title/subtitle (options)
		if ($headerList == 1)
		{
			$header = '<div class="ic-header-container">';
			$header.= '<' . $tag . ' class="ic-header-title">' . $header_title . '</' . $tag . '>';
			$header.= '<div class="ic-header-subtitle">' . $report . ' ' . $report2 . '</div>';
		}
		elseif ($headerList == 2)
		{
			$header = '<div class="ic-header-container">';
			$header.= '<' . $tag . ' class="ic-header-title">' . $header_title . '</' . $tag . '>';
		}
		elseif ($headerList == 3)
		{
			$header = '<div class="ic-header-container">';
			$header.= '<div class="ic-header-subtitle">' . $report . ' ' . $report2 . '</div>';
		}
		elseif ($headerList == 4)
		{
			$header = '<div>';
		}

		$header.='</div>';
		$header.= '<br/>';

		return $header;
	}

	// Navigator Events list
	public static function pagination ($count_items, $getpage, $arrowtext, $number_per_page, $pagination)
	{
		// If number of pages < or = 1, no display of pagination
		if (($count_items / $number_per_page) <= 1)
		{
			$nav = '';
		}
		else
		{
			// first check whether there are elements of those selected
			$ctrlNext = ($count_items > $number_per_page) ? 1 : NULL;
			$ctrlBack = ($getpage && $getpage > 1) ? 1 : NULL;

			$num = $number_per_page;

			// No display if number not exist
			$pages = ($num == NULL) ? 1 : ceil($count_items / $number_per_page);

			$nav = '<div class="navigator">';

			// in the case of text next/prev
			$textnext = ($arrowtext == 1) ? JText::_( 'JNEXT' ) : '';
			$textback = ($arrowtext == 1) ? JText::_( 'JPREV' ) : '';

			$parentnav = JRequest::getInt('Itemid');

			$mainframe = JFactory::getApplication();
			$isSef = $mainframe->getCfg( 'sef' );

			if ($isSef == '1')
			{
				$urlpage = JRoute::_(JURI::current().'?');
			}
			elseif ($isSef == '0')
			{
				$urlpage = 'index.php?option=com_icagenda&amp;view=list&amp;Itemid='.(int)$parentnav.'&amp;';
			}

			if ($pages >= 2)
			{
				if ($ctrlBack != NULL)
				{
					if ($getpage && $getpage<$pages) {
						$pageBack=$getpage-1;
						$pageNext=$getpage+1;
						$nav.='<a class="icagenda_back iCtip" href="'.JRoute::_($urlpage.'page='.$pageBack).'" title="'.$textback.'"><span class="iCicon iCicon-backic"></span> '.$textback.'&nbsp;</a>';
						$nav.='<a class="icagenda_next iCtip" href="'.JRoute::_($urlpage.'page='.$pageNext).'" title="'.$textnext.'">&nbsp;'.$textnext.' <span class="iCicon iCicon-nextic"></span></a>';
					}
					else {
						$pageBack=$getpage-1;
						$nav.='<a class="icagenda_back iCtip" href="'.JRoute::_($urlpage.'page='.$pageBack).'" title="'.$textback.'"><span class="iCicon iCicon-backic"></span> '.$textback.'&nbsp;</a>';
					}
				}
				if ($ctrlNext!=NULL){
					if(!$getpage){
						$pageNext=2;
					}
					else{
						$pageNext=$getpage+1;
						$pageBack=$getpage-1;
					}
					if (empty($pageBack)) {
						$nav.='<a class="icagenda_next iCtip" href="'.JRoute::_($urlpage.'page='.$pageNext).'" title="'.$textnext.'">&nbsp;'.$textnext.' <span class="iCicon iCicon-nextic"></span></a>';
					}
				}
			}

			if ($pagination == 1) {

				/* Pagination */

				if (empty($pageBack)) {
					$nav.='<div style="text-align:left">[ ';
				} elseif (($getpage && $getpage==$pages)){
					$nav.='<div style="text-align:right">[ ';
				} else {
					$nav.='<div style="text-align:center">[ ';
				}

				/* Boucle sur les pages */
				for ($i = 1 ; $i <= $pages ; $i++) {

					if ($i==1 || (($getpage-5) < $i && $i < ($getpage+5)) || $i==$pages)
					{
						if ($i == $pages && $getpage < ($pages-5))
						{
							$nav.= '...';
						}

						if ($i == $getpage)
						{
							$nav.= ' <b>' . $i . '</b>';
						}
						else
						{
							$nav.= ' <a href="' . $urlpage . 'page=' . $i . '"';
							$nav.= ' class="iCtip"';
							$nav.= ' title="' . JText::sprintf( 'COM_ICAGENDA_EVENTS_PAGE_PER_TOTAL', $i, $pages ) . '">';
							$nav.= $i;
							$nav.= '</a>';
						}

						if ($i == 1 && $getpage > 6)
						{
							$nav.= '...';
						}
					}
				}

				$nav.=' ]</div>';
			}

			$nav.='</div>';
		}

		return $nav;
	}

	// Function to get Format Date (using option format, and translation)
	public static function formatDate ($d)
	{
		$iCModeliChelper = new iCModeliChelper();
//		$mkt_date= $iCModeliChelper->mkt($d);

		// get Format
		$for = '0';
		// Global Option for Date Format
		$date_format_global = JComponentHelper::getParams('com_icagenda')->get('date_format_global', 'Y - m - d');
		$date_format_global = $date_format_global ? $date_format_global : 'Y - m - d';

		$for = JFactory::getApplication()->getParams()->get('format');

		// default
		if (($for == NULL) OR ($for == '0'))
		{
			$for = isset($date_format_global) ? $date_format_global : 'Y - m - d';
		}

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
		if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_icagenda/globalization/'.$langTag.'.php')){

			$langTag='en-GB';
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

		// Day with no 0 (test if Windows server)
//		$dayj = '%e';
//		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
//			$dayj = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $dayj);
//		}

		// Explode components of the date
		$ex_format	= explode (' ', $for);
		$dateFormat	= date('Y-m-d', strtotime($d));
		$separator	= JFactory::getApplication()->getParams()->get('date_separator', ' ');

		// Date Formatting using strings of Joomla Core Translations
		$format = '';

		foreach ($ex_format as $k => $val)
		{
			switch ($val)
			{
				// Day
				case 'd':
					$val = date("d", strtotime("$dateFormat"));
					break;

				case 'j':
					$val = date("j", strtotime("$dateFormat"));
					break;

				case 'D':
					$val = JText::_(date("D", strtotime("$dateFormat")));
					break;

				case 'l':
					$val = JText::_(date("l", strtotime("$dateFormat")));
					break;

				case 'S':
					$val = '<sup>' . date("S", strtotime("$dateFormat")) . '</sup>';
					break;

				case 'dS':
					$val = date("d", strtotime("$dateFormat")) . '<sup>' . date("S", strtotime("$dateFormat")) . '</sup>';
					break;

				case 'jS':
					$val = date("j", strtotime("$dateFormat")) . '<sup>' . date("S", strtotime("$dateFormat")) . '</sup>';
					break;

				// Month
				case 'm':
					$val = date("m", strtotime("$dateFormat"));
					break;

				case 'F':
					$val = JText::_(date('F', strtotime($dateFormat)));
					break;

				case 'M':
					$val = JText::_(date('F', strtotime($dateFormat)) . '_SHORT');
					break;

				case 'n':
					$val = $separator . date("n", strtotime("$dateFormat")) . $separator;
					break;

				// year (v3)
				case 'Y':
					$val = date("Y", strtotime("$dateFormat"));
					break;

				case 'y':
					$val = date("y", strtotime("$dateFormat"));
					break;

				// Separators of the components and space
				case '*':
					$val = $separator;
					break;

				case '_':
					$val = ' ';
					break;

//				case '/': $val='/'; break;
//				case '.': $val='.'; break;
//				case '-': $val='-'; break;
//				case ',': $val=','; break;
//				case 'the': $val='the'; break;
//				case 'gada': $val='gada'; break;
//				case 'de': $val='de'; break;
//				case 'г.': $val='г.'; break;
//				case 'den': $val='den'; break;
//				case 'ukp.': $val = '&#1088;.'; break;

				// Day in the Week
				case 'N':
					$val = date('N', strtotime("$dateFormat"));
					break;

				case 'w':
					$val = date('w', strtotime("$dateFormat"));
					break;

				// Day of the Year
				case 'z':
					$val = date('z', strtotime("$dateFormat"));
					break;

				// Week
				case 'W':
					$val = date("W", strtotime("$dateFormat"));
					break;

				// Default
				default:
					$val;
					break;
			}

			$format.= ($k !== 0) ? '' . $val : $val;
		}

		return $format;
	}


	// Set Date format for url
	public static function eventUrlDate($evt)
	{
		$evt_explode	= explode(' ', $evt);
		$dateday		= $evt_explode['0'] . '-' . str_replace(':', '-', $evt_explode['1']);

		return $dateday;
	}


	// Get Next Date (or Last Date)
	public static function nextDate ($evt, $i)
	{
		$eventTimeZone = null;

//		$singledates	= unserialize($i->dates); // returns array
		$singledates	= iCString::isSerialized($i->dates) ? unserialize($i->dates) : array(); // returns array
//		$period			= unserialize($i->period); // returns array
		$period			= iCString::isSerialized($i->period) ? unserialize($i->period) : array(); // returns array
		$startdatetime	= $i->startdatetime;
		$enddatetime	= $i->enddatetime;
		$weekdays		= $i->weekdays;

		$site_today_date	= JHtml::date('now', 'Y-m-d');
		$UTC_today_date		= JHtml::date('now', 'Y-m-d', $eventTimeZone);

		$next_date			= JHtml::date($evt, 'Y-m-d', $eventTimeZone);
		$next_datetime		= JHtml::date($evt, 'Y-m-d H:i', $eventTimeZone);

		$start_date			= JHtml::date($i->startdatetime, 'Y-m-d', $eventTimeZone);
		$end_date			= JHtml::date($i->enddatetime, 'Y-m-d', $eventTimeZone);

		// Check if date from a period with weekdays has end time of the period set in next.
		$time_next_datetime	= JHtml::date($next_datetime, 'H:i', $eventTimeZone);
		$time_startdate		= JHtml::date($i->startdatetime, 'H:i', $eventTimeZone);
		$time_enddate		= JHtml::date($i->enddatetime, 'H:i', $eventTimeZone);

		if ($next_date == $site_today_date
			&& $time_next_datetime == $time_enddate)
		{
			$next_datetime = $next_date . ' ' . $time_startdate;
		}

		if ( $period != NULL
			&& in_array($next_datetime, $period) )
		{
			$next_is_in_period = true;
		}
		else
		{
			$next_is_in_period = false;
		}

		// Highlight event in progress
		if ($next_date == $site_today_date)
		{
			$start_span	= '<span class="ic-next-today">';
			$end_span	= '</span>';
		}
		else
		{
			$start_span = $end_span = '';
		}

		$separator = '<span class="ic-datetime-separator"> - </span>';

		// Format Next Date
		if ( $next_is_in_period
			&& ($start_date == $end_date || $weekdays != null) )
		{
			// Next in the period & (same start/end date OR one or more weekday selected)
			$nextDate = $start_span;
			$nextDate.= '<span class="ic-period-startdate">';
			$nextDate.= self::formatDate($evt);
			$nextDate.= '</span>';

			if ($i->displaytime == 1)
			{
//				if (in_array($next_datetime, $singledates))
//				{
					$nextDate.= ' <span class="ic-single-starttime">' . icagendaEvents::dateToTimeFormat($startdatetime) . '</span>';

					if ( icagendaEvents::dateToTimeFormat($startdatetime) != icagendaEvents::dateToTimeFormat($enddatetime) )
					{
						$nextDate.= $separator . '<span class="ic-single-endtime">' . icagendaEvents::dateToTimeFormat($enddatetime) . '</span>';
					}
//				}
//				else
//				{
//					$nextDate.= ' <span class="ic-single-starttime">' . self::eventTime($next_datetime, $i) . '</span>';
//				}
			}

			$nextDate.= $end_span;
		}
		elseif ( $next_is_in_period
			&& ($weekdays == null) )
		{
			// Next in the period & different start/end date & no weekday selected
			$start	= '<span class="ic-period-startdate">';
			$start	.= self::formatDate($startdatetime);
			$start	.= '</span>';

			$end	= '<span class="ic-period-enddate">';
			$end	.= self::formatDate($enddatetime);
			$end	.= '</span>';

			if ($i->displaytime == 1)
			{
				$start		.= ' <span class="ic-period-starttime">' . icagendaEvents::dateToTimeFormat($startdatetime) . '</span>';
				$end		.= ' <span class="ic-period-endtime">' . icagendaEvents::dateToTimeFormat($enddatetime) . '</span>';
			}

			$nextDate = $start_span . $start . $separator . $end . $end_span;
		}
		else
		{
			// Next is a single date
			$nextDate = $start_span;
			$nextDate.= '<span class="ic-single-next">';
			$nextDate.= self::formatDate($evt);
			$nextDate.= '</span>';

			if ($i->displaytime == 1)
			{
				$nextDate.= ' <span class="ic-single-starttime">' . icagendaEvents::dateToTimeFormat($evt) . '</span>';
			}

			$nextDate.= $end_span;
		}

		return $nextDate;
	}


	// Day of the week, Full - From Joomla language file xx-XX.ini (eg. Saturday)
	public static function weekday($i)
	{
		$l_full_weekday	= date("l", strtotime($i));
		$weekday		= JText::_($l_full_weekday);

		return $weekday;
	}

	// Day of the week, Short - From Joomla language file xx-XX.ini (eg. Sat)
	public static function weekdayShort($i)
	{
		$l_short_weekday	= date("D", strtotime($i));
		$weekdayShort		= JText::_($l_short_weekday);

		return $weekdayShort;
	}


	/**
	 * MONTHS
	 */

	// Function used for special characters
	function substr_unicode($str, $s, $l = null) {
    	return join("", array_slice(
		preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $s, $l));
	}

	// Format Month (eg. December)
	public static function month($i)
	{
		$l_full_month	= date("F", strtotime($i));
		$lang_month		= JText::_($l_full_month);

		return $lang_month;
	}


	// Format Month Numeric - (eg. 07)
	public static function monthNum ($i){
		$iCModeliChelper = new iCModeliChelper();

		return $iCModeliChelper->formatDate($i, 'm');
	}


	/**
	 * YEAR
	 */

	// Format Year Numeric - (eg. 2013)
	public static function year($i)
	{
		return date('Y', strtotime($i));
	}

	// Format Year Short Numeric - (eg. 13)
	public static function yearShort($i)
	{
		return date('y', strtotime($i));
	}


	// Read More Button
	public static function readMore ($url, $desc, $content = ''){
		$limit = '100';
		$iCparams = JComponentHelper::getParams('com_icagenda');
		$limitGlobal = $iCparams->get('limitGlobal', 0);

		if ($limitGlobal == 1) {
			$limit = $iCparams->get('ShortDescLimit');
		}
		if ($limitGlobal == 0) {
			$customlimit=$iCparams->get('limit');
			if (is_numeric($customlimit)){
				$limit=$customlimit;
			} else {
				$limit = $iCparams->get('ShortDescLimit');
			}
		}
		if (is_numeric($limit)) {
			$limit = $limit;
		} else {
			$limit = '1';
		}
		$readmore='';
		if ($limit <= 1) {
			$readmore='';
		} else {
			$readmore=$content;
		}
		$text=preg_replace('/<img[^>]*>/Ui', '', $desc);
		if(strlen($text)>$limit){
			$string_cut=substr($text, 0,$limit);
			$last_space=strrpos($string_cut,' ');
			$string_ok=substr($string_cut, 0,$last_space);
			$text=$string_ok.' ';
			$url=$url;
			$text='<a href="'.$url.'" class="more">'.$readmore.'</a>';
		}else{
			$text='';
		}
		return $text;
	}
}
