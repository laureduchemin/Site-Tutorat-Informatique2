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
 * @version     3.5.0 2015-02-23
 * @since       3.4.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

/**
 * class icagendaEvents
 */
class icagendaEvents
{
	/**
	 * Function to return event access (access levels, approval and event access status)
	 *
	 * @access	public static
	 * @param	$id - id of the event
	 * @return	list array of access levels, approval and event access status
	 *
	 * @since	3.4.0
	 */
	static public function eventAccess($id = null)
	{
		// Preparing connection to db
		$db = Jfactory::getDbo();

		// Preparing the query
		$query = $db->getQuery(true);
		$query->select('e.state AS evtState, e.approval AS evtApproval, e.access AS evtAccess')
			->from($db->qn('#__icagenda_events').' AS e')
			->where($db->qn('e.id').' = '.$db->q($id));
		$query->select('v.title AS accessName')
			->join('LEFT', $db->quoteName('#__viewlevels') . ' AS v ON v.id = e.access');
		$db->setQuery($query);
		$eventAccess = $db->loadObject();

		if ($eventAccess)
		{
			return $eventAccess;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function to return feature Icons for an event
	 *
	 * @access	public static
	 * @param	$id - id of the event
	 * @return	list array of feature icons
	 *
	 * @since	3.4.0
	 */
	public static function featureIcons($id = null)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT f.icon, f.icon_alt');
		$query->from('`#__icagenda_feature_xref` AS fx');
		$query->innerJoin("`#__icagenda_feature` AS f ON fx.feature_id=f.id AND f.state=1 AND f.icon<>'-1'");
		$query->where('fx.event_id=' . $id);
		$query->order('f.ordering DESC'); // Order descending because the icons are floated right
		$db->setQuery($query);
		$feature_icons = $db->loadObjectList();

		return $feature_icons;
	}

	/**
	 * Function to return footer list of events
	 *
	 * @since	3.4.0
	 */
	public static function isListOfEvents()
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();
		$list_of_events = $params->get('copy', '');
		$core = $params->get('icsys');
		$string = '<a href="ht';
		$string.= 'tp://icag';
		$string.= 'enda.jooml';
		$string.= 'ic.com" target="_blank" style="font-weight: bold; text-decoration: none !important;">';
		$string.= 'iCagenda';
		$string.= '</a>';
		$icagenda = JText::sprintf('ICAGENDA_THANK_YOU_NOT_TO_REMOVE', $string);
		$default = '&#80;&#111;&#119;&#101;&#114;&#101;&#100;&nbsp;&#98;&#121;&nbsp;';
		$footer = '<div style="text-align: center; font-size: 10px; text-decoration: none"><p>';
		$footer.= preg_match('/iCagenda/',$icagenda) ? $icagenda : $default . $string;
		$footer.= '</p></div>';

		if ($list_of_events || $core == 'core')
		{
			echo $footer;
		}
	}

	/**
	 * DAY in Date Box (list of events)
	 *
	 * @since 3.5.0
	 */
	public static function day($date, $item = null)
	{
		$eventTimeZone	= null;

		$this_date		= JHtml::date($date, 'Y-m-d H:i', $eventTimeZone);
		$day_date		= JHtml::date($date, 'd', $eventTimeZone);
		$day_today		= JHtml::date('now', 'd');
		$date_today		= JHtml::date('now', 'Y-m-d');

		if ($item)
		{
			$weekdays		= $item->weekdays;
			$period			= unserialize($item->period);
			$period			= is_array($period) ? $period : array();
			$is_in_period	= (in_array($this_date, $period)) ? true : false;
			$startdate		= $item->startdatetime;
			$day_startdate	= JHtml::date($startdate, 'd', $eventTimeZone);
			$enddate		= $item->enddatetime;
			$day_enddate	= JHtml::date($enddate, 'd', $eventTimeZone);
		}

		if ($item && $is_in_period
			&& $weekdays == ''
			&& strtotime($startdate) <= strtotime($date_today)
			&& strtotime($enddate) >= strtotime($date_today)
			)
		{
			$day = '';

			if ($day_today > $day_startdate)
			{
//				$day.= '<span style="font-size: 14px; vertical-align: middle">' . $day_startdate . '&nbsp;</span>';
//				$day.= '<span style="font-size: 16px; vertical-align: middle">&#8676;</span>';
			}
			else
			{
//				$day.= '<span style="font-size: 14px; vertical-align: middle; color: transparent; text-shadow: none; text-decoration: none;">' . $day_startdate . '&nbsp;</span>';
//				$day.= '<span style="font-size: 16px; vertical-align: middle; color: transparent; text-shadow: none; text-decoration: none;">&#8676;</span>';
			}

//			$day.= '<span style="border-radius: 10px; padding: 0 5px; border: 2px dotted gray;">' . $day_today . '</span>';
			$day.= '<span style="text-decoration: overline">' . $day_today . '</span>';
//			$day.= $day_today;

			if ($day_today < $day_enddate)
			{
//				$day.= '<span style="font-size: 16px; vertical-align: middle">&#8677;</span>';
//				$day.= '<span style="font-size: 14px; vertical-align: middle">&nbsp;' . $day_enddate . '</span>';
			}
			else
			{
//				$day.= '<span style="font-size: 16px; vertical-align: middle; color: transparent; text-shadow: none; text-decoration: none;">&#8677;</span>';
//				$day.= '<span style="font-size: 14px; vertical-align: middle; color: transparent; text-shadow: none; text-decoration: none;">' . $day_enddate . '&nbsp;</span>';
			}

			return $day;
		}
		else
		{
			return $day_date;
		}
	}

	/**
	 * MONTH SHORT in Date Box (list of events)
	 *
	 * @since 3.5.0
	 */
	public static function dateBox($date, $type, $ongoing = null)
	{
		$datetime_today		= JHtml::date('now', 'Y-m-d H:i');

		$monthshort_date	= iCDate::monthShortJoomla($date);
		$monthshort_today	= iCDate::monthShortJoomla($datetime_today);
		$year_date			= JHtml::date($date, 'Y');
		$year_today			= JHtml::date('now', 'Y');

		if ($ongoing)
		{
			switch($type)
			{
				case 'monthshort': $value = $monthshort_today; break;
				case 'year': $value = $year_today; break;
			}
		}
		else
		{
			switch($type)
			{
				case 'monthshort': $value = $monthshort_date; break;
				case 'year': $value = $year_date; break;
			}
		}

		return $value;
	}

	/**
	 * Function to return time formated depending on AM/PM option
	 * Format Time (eg. 00:00 (AM/PM))
	 * $oldtime to be removed (not used since 2.0.0)
	 *
	 * @since 3.4.1
	 */
	public static function dateToTimeFormat($evt, $oldtime = null)
	{
		$app			= JFactory::getApplication();
		$params			= $app->getParams();
		$timeformat		= $params->get('timeformat', 1);
		$eventTimeZone	= null;

		$date_time		= strtotime(JHtml::date($evt, 'Y-m-d H:i', $eventTimeZone));
 		$t_time			= date('H:i', $date_time);

		$time_format	= ($timeformat == 1) ? '%H:%M' : '%I:%M %p';
		$lang_time		= strftime($time_format, strtotime($t_time));

		$time = ($oldtime != NULL && $t_time == '00:00') ? $oldtime : JText::_($lang_time);

		return $time;
	}
}
