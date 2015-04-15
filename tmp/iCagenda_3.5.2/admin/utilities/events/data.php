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
 * @version     3.5.1 2015-02-27
 * @since       3.5.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

/**
 * class icagendaEventsData
 */
class icagendaEventsData
{
	/**
	 * ALL DATES
	 *
	 * @since 3.5.0
	 */

	public static function getAllDates()
	{
		$app = JFactory::getApplication();
		$params = $app->getParams();

		// Get Settings
		$selection_of_events	= $params->get('time', 1);
		$filterTime				= $params->get('time', 1);
		$datesDisplay			= $params->get('datesDisplay', 1);
		$orderby				= $params->get('orderby', 2);
		$mcatid					= $params->get('mcatid');

		// Set vars
		$nodate 		= '0000-00-00 00:00:00';
		$ic_nodate		= '0000-00-00 00:00';
		$eventTimeZone	= null;
		$datetime_today	= JHtml::date('now', 'Y-m-d H:i'); // Joomla Time Zone
		$date_today		= JHtml::date('now', 'Y-m-d'); // Joomla Time Zone

		// Get Data
		$db		= Jfactory::getDbo();
		$query	= $db->getQuery(true);
        $query->select('e.next, e.dates, e.startdate, e.enddate, e.period, e.weekdays, e.displaytime, e.id, e.catid');
        $query->from('#__icagenda_events AS e');
		$query->leftJoin('`#__icagenda_category` AS c ON c.id = e.catid');

		// CATEGORY STATE Filtering
		$query->where('c.state = 1');

		// EVENT STATE Filtering
		$query->where('e.state = 1');

		// CATEGORY Filtering
		$mcatid = is_array($mcatid) ? $mcatid : array();
		$selcat = implode(', ', $mcatid);

		if (!in_array('0', $mcatid)
			&& count($mcatid)
			)
		{
			$query->where('e.catid IN (' . $selcat . ')');
		}

		// FEATURES Filtering
		$query->where(self::getFeaturesFilter());

		// LANGUAGE Filtering
		$query->where('e.language IN (' . $db->q(JFactory::getLanguage()->getTag()) . ',' . $db->q('*') . ')');

		// ACCESS Filtering
		$user		= JFactory::getUser();
		$userID		= $user->id;
		$userLevels	= $user->getAuthorisedViewLevels();
		$userGroups	= $user->groups;
		$groupid	= JComponentHelper::getParams('com_icagenda')->get('approvalGroups', array("8"));
		$groupid	= is_array($groupid) ? $groupid : array($groupid);

		if (!in_array('8', $userGroups) )
		{
			$useraccess	= implode(', ', $userLevels);
			$query->where('e.access IN (' . $useraccess . ')');
		}

		// APPROVAL RIGHTS Filtering
		if (!array_intersect($userGroups, $groupid)
			&& !in_array('8', $userGroups))
		{
			$query->where('e.approval <> 1');
		}
		else
		{
			$query->where('e.approval < 2');
		}

		$db->setQuery($query);
		$list = $db->loadObjectList();

		$list_all_dates = array();

		foreach ($list AS $i)
		{
			$i_id			= $i->id;
			$i_startdate	= $i->startdate;
			$i_enddate		= $i->enddate;
			$i_weekdays		= $i->weekdays;
			$i_dates		= $i->dates;
			$i_displaytime	= $i->displaytime;

			// Declare AllDates array
			$AllDatesDisplay	= array();

			// Get WeekDays Array
			$WeeksDays			= iCDatePeriod::weekdaysToArray($i_weekdays);

			// If Single Dates, added each one to All Dates for this event
			$singledates		= unserialize($i_dates);
			$singleDatesArray	= array();

			$no_filtering = '0';

			foreach ($singledates as $sd)
			{
				$date_Dat	= JHtml::date($sd, 'Y-m-d', $eventTimeZone);
				$SingleDate	= JHtml::date($sd, 'Y-m-d H:i', $eventTimeZone);

				$isValid = iCDate::isDate($sd);

				if ($isValid)
				{
					if ($datesDisplay == 1) // All Dates for each event
					{
						$singleDatesArray[] = $SingleDate . '_' . $i_id;
					}

					// Current Today
					elseif ($filterTime == 4
						&& strtotime($SingleDate) >= strtotime($date_today)
						)
					{
						$singleDatesArray[] = $SingleDate . '_' . $i_id;
					}

					// Upcoming Events
					elseif ($filterTime == 3
						&& strtotime($SingleDate) > strtotime($datetime_today)
						)
					{
						$singleDatesArray[] = $SingleDate . '_' . $i_id;
					}

					// Past event
					elseif ($filterTime == 2
						&& strtotime($SingleDate) < strtotime($datetime_today)
						)
					{
						$singleDatesArray[] = $SingleDate . '_' . $i_id;
					}

					// Current and Upcoming Events
					elseif ($filterTime == 1
						&& strtotime($SingleDate) > strtotime($datetime_today)
						)
					{
						$singleDatesArray[] = $SingleDate . '_' . $i_id;
					}

					// All Dates
					elseif (!$filterTime)
					{
						// All Upcoming dates
						if (strtotime($SingleDate) >= strtotime($datetime_today))
						{
							$no_filtering = $no_filtering + 1;

							$singleDatesArray[] = $SingleDate . '_' . $i_id;
						}

						// If no Upcoming dates, get the last date
						elseif ($no_filtering == 0
							&& strtotime($SingleDate) < strtotime($datetime_today))
						{
							$no_filtering = $no_filtering + 1;

							$singleDatesArray[] = $SingleDate . '_' . $i_id;
						}
					}
				}
			}

			if ($datesDisplay == 2
				&& $filterTime == 2
				&& count($singleDatesArray) > 0) // Past Events
			{
				$AllDatesDisplay[] = max($singleDatesArray);
			}
			elseif ($datesDisplay == 2
				&& count($singleDatesArray) > 0)
			{
				$AllDatesDisplay[] = min($singleDatesArray);
			}
			else
			{
				$AllDatesDisplay = array_merge($AllDatesDisplay, $singleDatesArray);
			}

			// If Period Dates, added each one to All Dates for this event (filter week Days, and if date not null)
			$StDate = JHtml::date($i_startdate, 'Y-m-d H:i', $eventTimeZone);
			$EnDate = JHtml::date($i_enddate, 'Y-m-d H:i', $eventTimeZone);

			$date_startdate	= JHtml::date($i_startdate, 'Y-m-d', $eventTimeZone);
			$date_enddate	= JHtml::date($i_enddate, 'Y-m-d', $eventTimeZone);
			$time_startdate	= JHtml::date($i_startdate, 'H:i', $eventTimeZone);
			$time_enddate	= JHtml::date($i_enddate, 'H:i', $eventTimeZone);

			$perioddates = iCDatePeriod::listDates($i_startdate, $i_enddate, $eventTimeZone);

			$period_array = array();

			foreach ($perioddates AS $date_in_weekdays)
			{
				$datetime_period_date = JHtml::date($date_in_weekdays, 'Y-m-d H:i', $eventTimeZone);

				if (in_array(date('w', strtotime($datetime_period_date)), $WeeksDays)
					&& iCDate::isDate($date_in_weekdays))
				{
					$period_array[] = $datetime_period_date;
				}
			}

			$only_startdate = ($i_weekdays || $i_weekdays == '0') ? false : true;

			if (isset($period_array)
				&& ($period_array != NULL && $period_array)
				)
			{
				if ($only_startdate)
				{
					$AllDatesDisplay[] = $StDate . '_' . $i_id;
				}
				else
				{
					$dp = 0;
					$count_period = count($period_array);
					$cp = 0;
					$no_filtering = 0;

					foreach ($period_array as $Dat)
					{
						$date_Dat	= JHtml::date($Dat, 'Y-m-d', $eventTimeZone);
						$SingleDate	= JHtml::date($Dat, 'Y-m-d H:i', $eventTimeZone);

						if (in_array(date('w', strtotime($Dat)), $WeeksDays)
							&& $dp == 0
							)
						{
							// Current Today and Upcoming Today
							if ($filterTime == 4
								&& strtotime($date_Dat) == strtotime($date_today))
							{
								if ($i_displaytime == 1
									&& strtotime($date_Dat . ' ' . $time_enddate) >= strtotime($datetime_today))
								{
									$dp = ($datesDisplay == 2) ? $dp+1 : 0;

									$AllDatesDisplay[] = $SingleDate . '_' . $i_id;
								}
								else
								{
									$dp = ($datesDisplay == 2) ? $dp+1 : 0;

									$AllDatesDisplay[] = $SingleDate . '_' . $i_id;
								}
							}

							// Upcoming
							elseif ($filterTime == 3
								&& strtotime($SingleDate) > strtotime($datetime_today))
							{
								$dp = ($datesDisplay == 2) ? $dp+1 : 0;

								$AllDatesDisplay[] = $SingleDate . '_' . $i_id;
							}

							// Past
							elseif ($filterTime == 2
								&& strtotime($date_Dat) < strtotime($date_today))
							{
								$dp = ($datesDisplay == 2) ? $dp+1 : 0;

								$AllDatesDisplay[] = $SingleDate . '_' . $i_id;
							}

							// Current Today and Upcoming
							elseif ($filterTime == 1)
							{
								if ($i_displaytime == 1
									&& strtotime($date_Dat . ' ' . $time_enddate) >= strtotime($datetime_today))
								{
									$dp = ($datesDisplay == 2) ? $dp+1 : 0;

									$AllDatesDisplay[] = $SingleDate . '_' . $i_id;
								}
								elseif ($i_displaytime != 1
									&& strtotime($date_Dat) >= strtotime($date_today))
								{
									$dp = ($datesDisplay == 2) ? $dp+1 : 0;

									$AllDatesDisplay[] = $SingleDate . '_' . $i_id;
								}
							}

							// No Filtering
							elseif (!$filterTime)
							{
								// All Upcoming dates
								if (strtotime($SingleDate) >= strtotime($datetime_today))
								{
									$dp = ($datesDisplay == 2) ? $dp+1 : 0;
									$no_filtering = ($datesDisplay == 2) ? $no_filtering+1 : 0;

									$AllDatesDisplay[] = $SingleDate . '_' . $i_id;
								}

								// If no Upcoming dates, get the last date
								elseif ($no_filtering != 0 && $datesDisplay == 2
									&& strtotime($SingleDate) < strtotime($datetime_today))
								{
									$dp = $dp+1;

									$AllDatesDisplay[] = $SingleDate . '_' . $i_id;
								}

								// If display All Dates, get the last dates
								elseif ( $datesDisplay == 1
									&& strtotime($SingleDate) < strtotime($datetime_today))
								{
									$dp = 0;

									$AllDatesDisplay[] = $SingleDate . '_' . $i_id;
								}
							}
						}
					}
				}
			}

			// If not All Dates display (select only one date for each event)
			if ( $datesDisplay == 2
				&& count($AllDatesDisplay) > 0 )
			{
				$ex_min 	= explode('_', min($AllDatesDisplay));
				$min_date	= $ex_min[0];

				if ($filterTime != '4')
				{
					// min date is upcoming
					if ( $min_date >= $datetime_today )
					{
						$AllDatesDisplay = array(min($AllDatesDisplay));
					}

					// All events
					elseif ($filterTime == '0')
					{
						// min date in Period
						if (in_array($min_date, $period_array)
							&& $min_date >= $datetime_today )
						{
							$AllDatesDisplay = array(min($AllDatesDisplay));
						}

						// min date is Single date and not past
						elseif ( !in_array($min_date, $period_array)
							&& ($min_date > $datetime_today) )
						{
							$AllDatesDisplay = array(min($AllDatesDisplay));
						}

						// min date is Single date and past
						else
						{
							$AllDatesDisplay = array(max($AllDatesDisplay));
						}
					}
					else
					{
						$AllDatesDisplay = array(max($AllDatesDisplay));
					}
				}
				else
				{
					$AllDatesDisplay = array(min($AllDatesDisplay));
				}
			}

			$AllDatesFilterTime = array();

			foreach ($AllDatesDisplay as $fD)
			{
				$ex_date		= explode('_', $fD);
				$get_date		= $ex_date['0'];
				$date_get_date	= JHtml::date($get_date, 'Y-m-d', $eventTimeZone);
				$time_enddate	= JHtml::date($EnDate, 'H:i', $eventTimeZone);

				// (0) Filter Dates : All Dates
				if ($filterTime == 0)
				{
					// Period with no weekdays selected
					if ( in_array($get_date, $perioddates)
						&& $only_startdate
						&& !in_array($StDate . '_' . $i_id, $AllDatesFilterTime)
						 )
					{
						$AllDatesFilterTime[] = $StDate . '_' . $i_id;
					}

					// Period with weekdays selected
					elseif ( in_array($get_date, $perioddates)
						&& !$only_startdate
						 )
					{
						$AllDatesFilterTime[] = $fD;
					}

					// Single Dates
					elseif ( !in_array($get_date, $perioddates) )
					{
						$AllDatesFilterTime[] = $fD;
					}
				}

				// (1) Filter Dates : Ongoing and Upcoming
				elseif ($filterTime == 1)
				{
					// Period with no weekdays selected
					if (in_array($get_date, $perioddates)
						&& $only_startdate
						&& strtotime($EnDate) >= strtotime($datetime_today)
						&& !in_array($StDate . '_' . $i_id, $AllDatesFilterTime)
						)
					{
						$AllDatesFilterTime[] = $StDate . '_' . $i_id;
					}

					// Period with weekdays selected
					elseif (in_array($get_date, $perioddates)
						&& !$only_startdate
						)
					{
						// If display time, control end time of the day
						if ($i_displaytime == 1
							&& strtotime($date_get_date . ' ' . $time_enddate) >= strtotime($datetime_today))
						{
							$AllDatesFilterTime[] = $fD;
						}

						// If do not display time, control start time of the day
						elseif ($i_displaytime != 1
							&& strtotime($date_get_date) >= strtotime($date_today))
						{
							$AllDatesFilterTime[] = $fD;
						}
					}

					// Single Dates
					elseif (!in_array($get_date, $perioddates)
						&& strtotime($get_date) >= strtotime($datetime_today)
						)
					{
						$AllDatesFilterTime[] = $fD;
					}
				}

				// (2) Filter Dates : Past Dates
				elseif ($filterTime == 2)
				{
					// Period with no weekdays selected
					if ( in_array($get_date, $perioddates)
						&& $only_startdate
						&& (strtotime($EnDate) < strtotime($datetime_today))
						 )
					{
						$AllDatesFilterTime[] = $fD;
					}

					// Period with weekdays selected
					elseif ( in_array($get_date, $perioddates)
						&& !$only_startdate
						&&  strtotime($get_date) < strtotime($datetime_today)
						&&  strtotime($date_get_date . ' ' . $time_enddate) < strtotime($datetime_today)
						 )
					{
						$AllDatesFilterTime[] = $fD;
					}

					// Single Dates
					elseif ( !in_array($get_date, $perioddates)
						&& strtotime($get_date) < strtotime($datetime_today)
						 )
					{
						$AllDatesFilterTime[] = $fD;
					}
				}

				// (3) Filter Dates : Upcoming
				elseif ($filterTime == 3)
				{
					// Period with no weekdays selected
					if (in_array($get_date, $perioddates)
						&& $only_startdate
						&& (strtotime($StDate) > strtotime($datetime_today))
						)
					{
						$AllDatesFilterTime[] = $fD;
					}

					// Period with weekdays selected
					elseif (in_array($get_date, $perioddates)
						&& !$only_startdate
						&&  strtotime($get_date) > strtotime($datetime_today)
						&&  strtotime($date_get_date . ' ' . $time_startdate) > strtotime($datetime_today)
						)
					{
						$AllDatesFilterTime[] = $fD;
					}

					// Single Dates
					elseif (!in_array($get_date, $perioddates)
						&& strtotime($get_date) > strtotime($datetime_today)
						)
					{
						$AllDatesFilterTime[] = $fD;
					}
				}

				// (4) Filter Dates : Ongoing Events today
				elseif ($filterTime == 4)
				{
					// Period with no weekdays selected
					if (in_array($get_date, $perioddates)
						&& $only_startdate
						&& strtotime($EnDate) > strtotime($datetime_today)
						&& strtotime($StDate) < (strtotime($date_today) + 86400)
						)
					{
						$AllDatesFilterTime[] = $date_get_date . ' ' . $time_startdate . '_' . $i_id;
					}

					// Period with weekdays selected
					elseif ( in_array($get_date, $perioddates)
						&& !$only_startdate
						&& ( strtotime($get_date) == strtotime($date_today)
						&& strtotime($date_get_date . ' ' . $time_enddate) < (strtotime($date_today) + 86400) )
						 )
					{
						$AllDatesFilterTime[] = $fD;
					}

					// Single Dates
					elseif ( !in_array($get_date, $perioddates)
						&& ( strtotime($get_date) >= strtotime($datetime_today)
						&& strtotime($get_date) < (strtotime($date_today) + 86400) )
						 )
					{
						$AllDatesFilterTime[] = $fD;
					}
				}
			}

			$list_all_dates = array_merge($list_all_dates, $AllDatesFilterTime);
		}

		if ($orderby == 2)
		{
			sort($list_all_dates);
		}
		else
		{
			rsort($list_all_dates);
		}

		return $list_all_dates;
	}

	/**
	 * Returns the element of a SQL query WHERE clause to support filtering the selection of Events using Event Features
	 *
	 * Controlled by menu parameters:
	 *  features_filter - array of Feature IDs
	 *  features_incl_excl - indicates whether the Feature IDs are to be used to include or exclude Events
	 *  features_any_all - indicates whether any Feature ID or all Feature IDs required to include or exclude an Event
	 *
	 * One or more sub-queries is referenced in a WHERE clause with IN() or NOT IN() to include or exclude Events.
	 *
	 * If any Feature ID in isolation is to include or exclude Event records then a single sub-query is used that
	 * uses a simple inner join between the feature and feature_xref tables to identify the distinct set of Event IDs
	 * linked to any one of the spacific Feature IDs.
	 *
	 * If all Feature IDs combined are required to include or exclude Events then separate sub-queries are used for
	 * each of the spacific Feature IDs. For this case, a more efficient option is available involving a direct join
	 * with either an inner or outer join, according to whether records are being included or excluded but this
	 * puts an unreasonable constraint on the overall syntax of the query.
	 */
	public static function getFeaturesFilter()
	{
		// get the application object
		$app = JFactory::getApplication();
		$params = $app->getParams();

		// Initialise a return value that can be included harmlessly in a WHERE clause, if necessary
		$filter = ' TRUE ';
		$featureids = $params->get('features_filter', '');

		if (is_array($featureids) && !empty($featureids))
		{
			$db = Jfactory::getDbo();
			$incl_excl = $params->get('features_incl_excl', '1') == '1' ? '' : 'NOT';

			if ($params->get('features_any_all', '1') == '1')
			{
				// Any single Feature ID will include or exclude events
				// Create comma separated list of Feature IDs
				$featureids = implode(',', $featureids);
				// Create a single sub-query
				$sub_query = $db->getQuery(true);
				$sub_query->select('fx.event_id')
					->from('#__icagenda_feature_xref AS fx')
					->innerJoin("#__icagenda_feature AS f ON fx.feature_id=f.id AND f.state=1 AND f.show_filter=1 AND f.id IN($featureids)");
				// Join the sub-query to the main query
				$filter = "(e.id $incl_excl IN(" . (string) $sub_query . '))';
			}
			else
			{
				// All Feature IDs combined will include or exclude events
				// Create a separate sub-query for each of the Feature IDs
				$sub_queries = array();

				foreach ($featureids as $featureid)
				{
					$sub_query = $db->getQuery(true);
					$sub_query->select('fx.event_id')
						->from('#__icagenda_feature_xref AS fx')
						->innerJoin("#__icagenda_feature AS f ON fx.feature_id=f.id AND f.state=1 AND f.show_filter=1 AND f.id=$featureid");
					$sub_queries[] = "e.id $incl_excl IN(" . (string) $sub_query . ')';
				}

				// Combine the sub-queries depending on inclusion or exclusion of events
				$filter = "(" . implode($incl_excl == 'NOT' ? " \nOR " : " \nAND ", $sub_queries) . ')';
			}
		}

		return $filter;
	}

	/**
	 * Return Array of all registrations from an event (date@@people)
	 * date : registered date
	 * people : nb of tickets for this registration
	 *
	 * @since	3.5.0
	 */
	public static function registeredList($id = null)
	{
		// Registrations total
		$db		= Jfactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('r.date AS date, r.eventid AS eventid, r.people AS people');
		$query->from('`#__icagenda_registration` AS r');
		$query->where('r.state = 1');

		if ($id)
		{
			$query->where('r.eventid = ' . $db->q($id));
		}

		$db->setQuery($query);
		$result = $db->loadObjectList();

		$registeredList = array();

		foreach ($result AS $r)
		{
			$reg_date = $r->date ? $r->date : 'period';
			$registeredList[] = $r->eventid . '@@' . $reg_date . '@@' . $r->people;
		}

		return $registeredList;
	}

	/**
	 * Return list of all dates (singles and period) from an event
	 *
	 * @since	3.5.0 (Not Yet Used)
	 */
	public static function thisEventDates($id)
	{
		// Set vars
		$nodate			= '0000-00-00 00:00:00';
		$ic_nodate		= '0000-00-00 00:00';
		$eventTimeZone	= null;

		// Get Data
		$db		= Jfactory::getDbo();
		$query	= $db->getQuery(true);
        $query->select('e.next, e.dates, e.startdate, e.enddate, e.period, e.weekdays, e.displaytime, e.id');
        $query->from('#__icagenda_events AS e');
		$query->leftJoin('`#__icagenda_category` AS c ON c.id = e.catid');
		$query->where('c.state = 1');
		$query->where('e.id = ' . $db->q($id));
		$db->setQuery($query);
		$result = $db->loadObjectList();

		// Get Data
		$tId			= $id;
		$tDates			= $result->dates;
		$tStartdate		= $result->startdate;
		$tEnddate		= $result->enddate;
		$tWeekdays		= $result->weekdays;

		// Declare AllDates array
		$thisEventDates = array();

		// Get WeekDays Array
		$WeeksDays = iCDatePeriod::weekdaysToArray($tWeekdays);

		// If Single Dates, added each one to All Dates for this event
		$singledates = unserialize($tDates);

		foreach ($singledates as $sd)
		{
			$isValid = iCDate::isDate($sd);

			if ($isValid)
			{
				array_push($thisEventDates, $sd);
			}
		}

		$perioddates = iCDatePeriod::listDates($tStartdate, $tEnddate, $eventTimeZone);

		if (isset ($perioddates)
			&& $perioddates != NULL)
		{
			foreach ($perioddates as $Dat)
			{
				if (in_array(date('w', strtotime($Dat)), $WeeksDays))
				{
					$isValid = iCDate::isDate($Dat);

					if ($isValid)
					{
						$SingleDate = JHtml::date($Dat, 'Y-m-d H:i:s', $eventTimeZone);

						array_push($thisEventDates, $SingleDate);
					}
				}
			}
		}

		return $thisEventDates;
	}
}
