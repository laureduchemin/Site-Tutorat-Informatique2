<?php
/**
 *------------------------------------------------------------------------------
 *  iCagenda v3 by Jooml!C - Events Management Extension for Joomla! 2.5 / 3.x
 *------------------------------------------------------------------------------
 * @package     com_icagenda - mod_iccalendar
 * @copyright   Copyright (c)2012-2015 Cyril Rezé, Jooml!C - All rights reserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Cyril Rezé (Lyr!C) - doorknob
 * @link        http://www.joomlic.com
 *
 * @version 	3.5.3 2015-03-23
 * @since       3.1.9 (1.0)
 *------------------------------------------------------------------------------
*/

/**
 *	iCagenda - iC calendar
 */


// No direct access to this file
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.methods' );
jimport( 'joomla.environment.request' );
jimport('joomla.application.component.helper');

// classe du Module
class modiCcalendarHelper
{
	private function construct($params)
	{
		$this->modid				= $params->get('id');
		$this->template				= $params->get('template');
		$this->format				= $params->get('format');
		$this->date_separator		= $params->get('date_separator');
		$this->setTodayTimezone		= $params->get('setTodayTimezone');
		$this->displayDatesTimezone	= $params->get('displayDatesTimezone');
		$this->filtering_shortDesc	= $params->get('filtering_shortDesc', '');
		$this->catid				= $params->get('mcatid');
		$this->number				= $params->get('number');
		$this->onlyStDate			= $params->get('onlyStDate');
		$this->firstMonth			= $params->get('firstMonth', null);
		$this->month_nav			= $params->get('month_nav', '1');
		$this->year_nav				= $params->get('year_nav', '1');

		$linkid						= JRequest::getInt('Itemid');
		$iccaldate					= JRequest::getVar('iccaldate'); // Get date set in month/year navigation

		$this->itemid				= $linkid;
		$this->mod_iccalendar		= '#mod_iccalendar_'.$this->modid;

		// Features Options
		$this->features_icon_size = $params->get('features_icon_size');
		$this->show_icon_title = $params->get('show_icon_title');

		// Get media path
		$params_media = JComponentHelper::getParams('com_media');
		$image_path = $params_media->get('image_path', 'images');
		$this->features_icon_root = JURI::base() . "{$image_path}/icagenda/feature_icons/{$this->features_icon_size}/";

		// First day of the current month
		$this_month = $this->firstMonth ? date("Y-m-d", strtotime("+1 month", strtotime($this->firstMonth))) : date("Y-m-01");

		if (isset($iccaldate)
			&& ! empty($iccaldate))
		{
			// This should be the first day of a month
			$this->date_start = date('Y-m-01', strtotime($iccaldate));
		}
		else
		{
			$this->date_start	= $this_month;
		}

		// Add filter to restrict the number of events using the 'next' date
		if ($this->date_start > $this_month)
		{
			// Month to be displayed is in the future
			// Events required start from the current month
			$filter_start = $this_month;
		}
		else
		{
			// Month to be displayed is current or past
			// Events required start from the display month
			$filter_start = $this->date_start;
		}

        $this->addFilter('e.next', ''.$filter_start.'','>=');

		// An end date for selection is not possible because it may prevent display of past events where the next
		// scheduled instance of an event is after the end of the display month
//		$filter_end = date('Y-m-d', strtotime('+1 month', strtotime($this->date_start)));
//		$this->addFilter('e.next', "'$filter_end'",'<');


		// Get Array of categories to be displayed
		if (isset($this->catid)
			&& ! empty($this->catid))
		{
			$cat_filter_param = $this->catid;

			if (!is_array($cat_filter_param))
			{
				$catFilter = array($cat_filter_param);
			}
			else
			{
				$catFilter = $cat_filter_param;
			}
			$cats_option = implode(', ', $catFilter);

			if ($catFilter != array(0))
			{
				$this->addFilter('e.catid', '('.$cats_option.')', ' IN ');
			}
		}
	}


	function start($params)
	{
		$this->construct($params);
	}


	function addFilter($key, $var, $for = NULL)
	{
		if ($for == NULL) $for = '=';
		$this->filter[] = $key.$for.$var;
	}


	// Class Method
	function getStamp($params)
	{
		$iCparams		= JComponentHelper::getParams('com_icagenda');
		$eventTimeZone	= null;

		// Itemid Request (automatic detection of the first iCagenda menu-link, by menuID)
		$iC_list_menus	= icagendaMenus::iClistMenuItemsInfo();
		$nb_menu		= count($iC_list_menus);
		$nolink			= $nb_menu ? false : true;

		$app			= JFactory::getApplication();
		$menu			= $app->getMenu();
		$isSef			= $app->getCfg( 'sef' );
		$date_var		= ($isSef == 1) ? '?date=' :'&amp;date=';

		// Check if GD is enabled on the server
		if (extension_loaded('gd') && function_exists('gd_info'))
		{
			$thumb_generator = $iCparams->get('thumb_generator', 1);
		}
		else
		{
			$thumb_generator = 0;
		}

		$datetime_today	= JHtml::date('now', 'Y-m-d H:i');
		$timeformat		= $iCparams->get('timeformat', 1);
		$lang_time		= ($timeformat == 1) ? 'H:i' : 'h:i A';

		// Check if fopen is allowed
		$fopen = true;
		$result = ini_get('allow_url_fopen');

		if (empty($result))
		{
			$fopen = false;
		}

		$this->start($params);

		// Get the database
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		// Build the query
		$query->select('e.*,
				e.place as place_name,
				c.title as cat_title,
				c.alias as cat_alias,
				c.color as cat_color,
				c.ordering as cat_order
			')
    		->from($db->qn('#__icagenda_events').' AS e')
			->leftJoin($db->qn('#__icagenda_category').' AS c ON '.$db->qn('c.id').' = '.$db->qn('e.catid'));

		// Where Category is Published
		$query->where('c.state = 1');

		// Where State is Published
		$query->where('e.state = 1');

		// Where event is Approved
		$query->where('e.approval = 0');

		// Add filters
		if (isset($this->filter))
		{
			foreach ($this->filter as $filter)
			{
				$query->where($filter);
			}
		}

		// Check Access Levels
		$user = JFactory::getUser();
		$userID = $user->id;
		$userLevels = $user->getAuthorisedViewLevels();

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			$userGroups = $user->getAuthorisedGroups();
		}
		else
		{
			$userGroups = $user->groups;
		}

		$userAccess = implode(', ', $userLevels);

		if (!in_array('8', $userGroups))
		{
			$query->where('e.access IN (' . $userAccess . ')');
		}

		// Features - extract the number of displayable icons per event
		$query->select('feat.count AS features');
		$sub_query = $db->getQuery(true);
		$sub_query->select('fx.event_id, COUNT(*) AS count');
		$sub_query->from('`#__icagenda_feature_xref` AS fx');
		$sub_query->innerJoin("`#__icagenda_feature` AS f ON fx.feature_id=f.id AND f.state=1 AND f.icon<>'-1'");
		$sub_query->group('fx.event_id');
		$query->leftJoin('(' . (string) $sub_query . ') AS feat ON e.id=feat.event_id');

		// Registrations total
		$query->select('r.count AS registered, r.date AS reg_date');
		$sub_query = $db->getQuery(true);
		$sub_query->select('r.eventid, sum(r.people) AS count, r.date AS date');
		$sub_query->from('`#__icagenda_registration` AS r');
		$sub_query->where('r.state > 0');
		$sub_query->group('r.eventid');
		$query->leftJoin('(' . (string) $sub_query . ') AS r ON e.id=r.eventid');

//		$query.=' LIMIT 0, 1000';

		// Run the query
		$db->setQuery($query);

		// Invoke the query
		$res = $db->loadObjectList();

		$registrations = icagendaEventsData::registeredList();

		foreach ($res AS $record)
		{
			$record_registered = array();

			foreach ($registrations AS $reg_by_event)
			{
				$ex_reg_by_event = explode('@@', $reg_by_event);

				if ($ex_reg_by_event[0] == $record->id)
				{
					$record_registered[] = $ex_reg_by_event[0] . '@@' . $ex_reg_by_event[1] . '@@' . $ex_reg_by_event[2];
				}
			}

			$record->registered = $record_registered;
		}

		$days = $this->getDays($this->date_start, 'Y-m-d H:i');

		foreach ($res as $r)
		{
			// Extract the feature details, if needed
			$features = array();

			if (is_null($r->features) || empty($this->features_icon_size))
			{
				$r->features = array();
			}
			else
			{
				$r->features = icagendaEvents::featureIcons($r->id);
			}

			if (isset($r->features) && is_array($r->features))
			{
				foreach ($r->features as $feature)
				{
					$features[] = array('icon' => $feature->icon, 'icon_alt' => $feature->icon_alt);
				}
			}

			// list calendar dates
			$next = isset($next) ? $next : '';

			$datemultiplelist	= $this->getDatelist($r->dates, $next);
			$datelist			= $datemultiplelist;

			$AllDates = array();
			$weekdays = isset($r->weekdays) ? $r->weekdays : '';

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
			elseif ($r->weekdays)
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

			// If Single Dates, added to all dates for this event
			$singledates = iCString::isSerialized($r->dates) ? unserialize($r->dates) : array();

			if ((isset ($datemultiplelist)) AND ($datemultiplelist!=NULL) AND (!in_array('0000-00-00 00:00:00', $singledates)))
			{
				$AllDates = array_merge($AllDates, $datemultiplelist);
			}

			$StDate			= JHtml::date($r->startdate, 'Y-m-d H:i', $eventTimeZone);
			$EnDate			= JHtml::date($r->enddate, 'Y-m-d H:i', $eventTimeZone);
			$perioddates	= iCDatePeriod::listDates($r->startdate, $r->enddate, $eventTimeZone);

			$onlyStDate = isset($this->onlyStDate) ? $this->onlyStDate : '';

			// Check the period if individual dates
			$only_startdate		= ($r->weekdays || $r->weekdays == '0') ? false : true;

			if (isset ($perioddates) && $perioddates != NULL)
			{
				if ($onlyStDate == 1)
				{
					array_push($AllDates, $StDate);
				}
				else
				{
					foreach ($perioddates as $Dat)
					{
						if (in_array(date('w', strtotime($Dat)), $WeeksDays))
						{
							$SingleDate = JHtml::date($Dat, 'Y-m-d H:i', $eventTimeZone);

							array_push($AllDates, $SingleDate);
						}
					}
				}
			}

			rsort($AllDates);

			//liste dates next
			$datemlist = $this->getmlist($r->dates, $next);
			$dateplist = $this->getplist($r->period, $next);

			if ($dateplist)
			{
				$datelistcal = array_merge($datemlist, $dateplist);
			}
			else
			{
				$datelistcal = $datemlist;
			}

			$todaytime = time();

			rsort($datelist);
			rsort($datelistcal);

			// requête Itemid
			$iCmenuitem = $params->get('iCmenuitem');

			if (is_numeric($iCmenuitem))
			{
				$linkid = $iCmenuitem;
			}
			else
			{
				// set menu link for each event (itemID) depending of category and/or language
				$onecat = $multicat = '0';
				$link_one = $link_multi = '';

				$item_catid = $r->catid;

				$array_menus_cat_not_set = array();

				foreach ($iC_list_menus AS $iCm)
				{
					$value			= explode('_', $iCm);
					$iCmenu_id		= $value['0'];
					$iCmenu_mcatid	= $value['1'];
					$iCmenu_lang	= $value['2'];
					$iCmenu_filter	= $value['3'];

					$iCmenu_mcatid_array = !is_array ($iCmenu_mcatid) ? explode(',', $iCmenu_mcatid) : '';

					// Filter by Dates
					if ($iCmenu_filter == 2) // Filter by Dates : PAST EVENTS
					{
						$get_this_menu = ($r->next < $datetime_today) ? true : false;
					}
					elseif ($iCmenu_filter == 3) // Filter by Dates : UPCOMING EVENTS
					{
						$get_this_menu = ($r->next > $datetime_today) ? true : false;
					}
					else
					{
						$get_this_menu = true;
					}

					if ($iCmenu_mcatid
						&& $get_this_menu
						&& in_array($iCmenu_lang, array($r->language, '*'))
						)
					{
						$nb_cat_filter = count($iCmenu_mcatid_array);

						for ($i = $r->catid; in_array($i, $iCmenu_mcatid_array); $i++)
						{
							if ($nb_cat_filter == 1)
							{
								$link_one = $iCmenu_id;
							}
							elseif ($nb_cat_filter > 1)
							{
								$link_multi = $iCmenu_id;
							}
						}
					}
					else
					{
						array_push($array_menus_cat_not_set, $iCmenu_id);
					}
				}

				if ($link_one)
				{
					$linkid = $link_one;
				}
				elseif ($link_multi)
				{
					$linkid = $link_multi;
				}
				else
				{
					$linkid = count($array_menus_cat_not_set) ? $array_menus_cat_not_set['0'] : null;
				}
			}

			$eventnumber = NULL;
			$eventnumber = $r->id;
			$event_slug = $r->alias ? $r->id . ':' . $r->alias : $r->id;

			if ( $linkid
				&& !$nolink
				&& JComponentHelper::getComponent('com_icagenda', true)->enabled )
			{
				$urlevent = JRoute::_('index.php?option=com_icagenda&amp;view=list&amp;layout=event&amp;id=' . $event_slug . '&amp;Itemid=' . (int)$linkid);
			}
			else
			{
				$urlevent = '#';
			}


			// Gets Short Description limit, set in global options of the component iCagenda
			$limit = $iCparams->get('ShortDescLimit', '100');

			// Html tags removal Global Option (component iCagenda) - Short Description
			$Filtering_ShortDesc_Global = $iCparams->get('Filtering_ShortDesc_Global', '');
			$HTMLTags_ShortDesc_Global = $iCparams->get('HTMLTags_ShortDesc_Global', array());

			// Get Module Option
			$Filtering_ShortDesc_Local = $this->filtering_shortDesc;

			/**
			 * START Filtering HTML method
			 */
			$limit = is_numeric($limit) ? $limit : false;

			$descdata = $r->desc;
			$desc_full = deleteAllBetween('{', '}', $descdata);

			// Gets length of the short desc, when not filtered
			$limit_not_filtered = substr($desc_full, 0, $limit);
			$text_length = strlen($limit_not_filtered);

			// Gets length of the short desc, after html filtering
			$limit_filtered = preg_replace('/[\p{Z}\s]{2,}/u', ' ', $limit_not_filtered);
			$limit_filtered = strip_tags($limit_filtered);
			$text_short_length = strlen($limit_filtered);

			// Sets Limit + special tags authorized
			$limit_short = $limit + ($text_length - $text_short_length);

			// Replaces all authorized html tags with tag strings
			if (empty($Filtering_ShortDesc_Local)
				&& ($Filtering_ShortDesc_Global == '1') )
			{
				$desc_full = str_replace('+', '@@', $desc_full);
				$desc_full = in_array('1', $HTMLTags_ShortDesc_Global) ? str_replace('<br>', '+@br@', $desc_full) : $desc_full;
				$desc_full = in_array('1', $HTMLTags_ShortDesc_Global) ? str_replace('<br/>', '+@br@', $desc_full) : $desc_full;
				$desc_full = in_array('1', $HTMLTags_ShortDesc_Global) ? str_replace('<br />', '+@br@', $desc_full) : $desc_full;
				$desc_full = in_array('2', $HTMLTags_ShortDesc_Global) ? str_replace('<b>', '+@b@', $desc_full) : $desc_full;
				$desc_full = in_array('2', $HTMLTags_ShortDesc_Global) ? str_replace('</b>', '@bc@', $desc_full) : $desc_full;
				$desc_full = in_array('3', $HTMLTags_ShortDesc_Global) ? str_replace('<strong>', '@strong@', $desc_full) : $desc_full;
				$desc_full = in_array('3', $HTMLTags_ShortDesc_Global) ? str_replace('</strong>', '@strongc@', $desc_full) : $desc_full;
				$desc_full = in_array('4', $HTMLTags_ShortDesc_Global) ? str_replace('<i>', '@i@', $desc_full) : $desc_full;
				$desc_full = in_array('4', $HTMLTags_ShortDesc_Global) ? str_replace('</i>', '@ic@', $desc_full) : $desc_full;
				$desc_full = in_array('5', $HTMLTags_ShortDesc_Global) ? str_replace('<em>', '@em@', $desc_full) : $desc_full;
				$desc_full = in_array('5', $HTMLTags_ShortDesc_Global) ? str_replace('</em>', '@emc@', $desc_full) : $desc_full;
				$desc_full = in_array('6', $HTMLTags_ShortDesc_Global) ? str_replace('<u>', '@u@', $desc_full) : $desc_full;
				$desc_full = in_array('6', $HTMLTags_ShortDesc_Global) ? str_replace('</u>', '@uc@', $desc_full) : $desc_full;
			}
			elseif ( $Filtering_ShortDesc_Local == '2'
				|| (($Filtering_ShortDesc_Global == '') && empty($Filtering_ShortDesc_Local)) )
			{
				$desc_full = '@i@'.$desc_full.'@ic@';
				$limit_short = $limit_short + 7;
			}
			else
			{
				$desc_full = $desc_full;
			}

			// Removes HTML tags
			$desc_nohtml	= strip_tags($desc_full);

			// Replaces all sequences of two or more spaces, tabs, and/or line breaks with a single space
			$desc_nohtml	= preg_replace('/[\p{Z}\s]{2,}/u', ' ', $desc_nohtml);

			// Replaces all spaces with a single +
			$desc_nohtml	= str_replace(' ', '+', $desc_nohtml);

			if (strlen($desc_nohtml) > $limit_short)
			{
				// Cuts full description, to get short description
				$string_cut	= substr($desc_nohtml, 0, $limit_short);

				// Detects last space of the short description
				$last_space	= strrpos($string_cut, '+');

				// Cuts the short description after last space
				$string_ok	= substr($string_cut, 0, $last_space);

				// Counts number of tags converted to string, and returns lenght
				$nb_br			= substr_count($string_ok, '+@br@');
				$nb_plus		= substr_count($string_ok, '@@');
				$nb_bopen		= substr_count($string_ok, '@b@');
				$nb_bclose		= substr_count($string_ok, '@bc@');
				$nb_strongopen	= substr_count($string_ok, '@strong@');
				$nb_strongclose	= substr_count($string_ok, '@strongc@');
				$nb_iopen		= substr_count($string_ok, '@i@');
				$nb_iclose		= substr_count($string_ok, '@ic@');
				$nb_emopen		= substr_count($string_ok, '@em@');
				$nb_emclose		= substr_count($string_ok, '@emc@');
				$nb_uopen		= substr_count($string_ok, '@u@');
				$nb_uclose		= substr_count($string_ok, '@uc@');

				// Replaces tag strings with html tags
				$string_ok	= str_replace('@br@', '<br />', $string_ok);
				$string_ok	= str_replace('@b@', '<b>', $string_ok);
				$string_ok	= str_replace('@bc@', '</b>', $string_ok);
				$string_ok	= str_replace('@strong@', '<strong>', $string_ok);
				$string_ok	= str_replace('@strongc@', '</strong>', $string_ok);
				$string_ok	= str_replace('@i@', '<i>', $string_ok);
				$string_ok	= str_replace('@ic@', '</i>', $string_ok);
				$string_ok	= str_replace('@em@', '<em>', $string_ok);
				$string_ok	= str_replace('@emc@', '</em>', $string_ok);
				$string_ok	= str_replace('@u@', '<u>', $string_ok);
				$string_ok	= str_replace('@uc@', '</u>', $string_ok);
				$string_ok	= str_replace('+', ' ', $string_ok);
				$string_ok	= str_replace('@@', '+', $string_ok);

				$text = $string_ok;

				// Close html tags if not closed
				if ($nb_bclose < $nb_bopen) $text = $string_ok.'</b>';
				if ($nb_strongclose < $nb_strongopen) $text = $string_ok.'</strong>';
				if ($nb_iclose < $nb_iopen) $text = $string_ok.'</i>';
				if ($nb_emclose < $nb_emopen) $text = $string_ok.'</em>';
				if ($nb_uclose < $nb_uopen) $text = $string_ok.'</u>';

				$ic_readmore = '[&#46;&#46;&#46;]';
				$return_text = $text.' '.$ic_readmore;

				$descShort	= $limit ? $return_text : '';
			}
			else
			{
				$desc_full	= $desc_nohtml;
				$desc_full	= str_replace('@br@', '<br />', $desc_full);
				$desc_full	= str_replace('@b@', '<b>', $desc_full);
				$desc_full	= str_replace('@bc@', '</b>', $desc_full);
				$desc_full	= str_replace('@strong@', '<strong>', $desc_full);
				$desc_full	= str_replace('@strongc@', '</strong>', $desc_full);
				$desc_full	= str_replace('@i@', '<i>', $desc_full);
				$desc_full	= str_replace('@ic@', '</i>', $desc_full);
				$desc_full	= str_replace('@em@', '<em>', $desc_full);
				$desc_full	= str_replace('@emc@', '</em>', $desc_full);
				$desc_full	= str_replace('@u@', '<u>', $desc_full);
				$desc_full	= str_replace('@uc@', '</u>', $desc_full);
				$desc_full	= str_replace('+', ' ', $desc_full);
				$desc_full	= str_replace('@@', '+', $desc_full);

				$descShort	= $limit ? $desc_full : '';
			}
			/** END Filtering HTML function */


			/**
			 * Get Thumbnail
			 */

			// START iCthumb

			// Set if run iCthumb
			if (($r->image) AND ($thumb_generator == 1))
			{
				// Generate small thumb if not exist
				$thumb_img = icagendaThumb::sizeSmall($r->image);
			}
			elseif (($r->image) AND ($thumb_generator == 0))
			{
				$thumb_img = $r->image;
			}
			else
			{
				if ($r->image)
				{
					$thumb_img = 'media/com_icagenda/images/nophoto.jpg';
				}
				else
				{
					$thumb_img = '';
				}
			}

			// END iCthumb



			$evtParams = '';
			$evtParams = new JRegistry($r->params);

			// Display Time
			$dp_time = $params->get('dp_time', 1);
			if ($dp_time == 1) {
				$r_time = true;
			} else {
				$r_time = false;
			}

			// Display City
			$dp_city = $params->get('dp_city', 1);
			if ($dp_city == 1) {
				$r_city = $r->city;
			} else {
				$r_city = false;
			}

			// Display Country
			$dp_country = $params->get('dp_country', 1);
			if ($dp_country == 1) {
				$r_country = $r->country;
			} else {
				$r_country = false;
			}

			// Display Venue Name
			$dp_venuename = $params->get('dp_venuename', 1);
			if ($dp_venuename == 1) {
				$r_place = $r->place_name;
			} else {
				$r_place = false;
			}

			// Display Intro Text
			$dp_shortDesc = $params->get('dp_shortDesc', '');

			if ($dp_shortDesc == '1') // Short Description
			{
				$descShort	= $r->shortdesc ? $r->shortdesc : false;
			}
			elseif ($dp_shortDesc == '2') // Auto-Introtext
			{
				$descShort	= $descShort ? $descShort : false;
			}
			elseif ($dp_shortDesc == '0') // Hide
			{
				$descShort	= false;
			}
			else // Auto (First Short Description, if does not exist, Auto-generated short description from the full description. And if does not exist, will use meta description if not empty)
			{
				$short_description = $r->shortdesc ? $r->shortdesc : $descShort;
				$descShort	= $short_description ? $short_description : $r->metadesc;
			}

			// Display Registration Infos
			$dp_regInfos = $params->get('dp_regInfos', 1);

			$maxTickets = ($dp_regInfos == 1) ? $evtParams->get('maxReg', '1000000') : false;

			$event = array(
				'id' => (int)$r->id,
//				'registered' => (int)$registered,
//				'maxTickets' => (int)$maxTickets,
//				'TicketsLeft' => (int)$TicketsLeft,
				'Itemid' => (int)$linkid,
//				'url'=> $urlevent,
				'title' => $r->title,
				'next' => $this->formatDate($r->next),
//				'image' => $r->image,
				'image' => $thumb_img,
				'address' => $r->address,
				'city' => $r_city,
				'country' => $r_country,
				'place' => $r_place,
				'description' => $r->desc,
				'descShort' => $descShort,
				'cat_title' => $r->cat_title,
				'cat_order' => $r->cat_order,
				'cat_color' => $r->cat_color,
				'nb_events' => count($r->id),
				'no_image' => JTEXT::_('MOD_ICCALENDAR_NO_IMAGE'),
				'params' => $r->params,
				'features_icon_size' => $this->features_icon_size,
				'features_icon_root' => $this->features_icon_root,
				'show_icon_title' => $this->show_icon_title,
				'features' => $features,
			);

			// Initialize
			$access='0';
			$control='';

			// Access Control
			$access = $r->access;
			if ($access == '0') $access='1';

			if ( in_array($access, $userLevels) OR in_array('8', $userGroups) )
			{
				$control = $access;
			}

			// Language Control
			$lang = JFactory::getLanguage();
			$eventLang = '';
			$langTag = '';
			$langTag = $lang->getTag();

			if(isset($r->language)) $eventLang=$r->language;
			if($eventLang=='') $eventLang=$langTag;
			if($eventLang=='*') $eventLang=$langTag;

			$events_per_day = array();

			$displaytime	= '';
			if(isset($r->displaytime)) $displaytime = $r->displaytime;

			// Get List of Dates
			if ($control == $access)
			{
				if ($eventLang == $langTag)
				{
					if (is_numeric($linkid) && is_numeric($eventnumber) && !is_array($linkid) && !is_array($eventnumber))
					{
						if (is_array($event))
						{
							foreach ($AllDates as $d)
							{
								$next_date_control	= JHtml::date($d, 'Y-m-d H:i', $eventTimeZone);

								if ($only_startdate && in_array($next_date_control, $perioddates))
								{
									$set_date_in_url = '';
								}
								else
								{
									$set_date_in_url = $date_var . iCDate::dateToAlias($d, 'Y-m-d H:i');
								}

								if ($r_time)
								{
									$time = array(
//										'time' => date($lang_time, $this->mkttime($d)),
										'time' => date($lang_time, strtotime($d)),
										'displaytime' => $displaytime,
										'url'=> $urlevent . $set_date_in_url
									);
								}
								else
								{
									$time = array(
										'time' => '',
										'displaytime' => '',
										'url'=> $urlevent . $set_date_in_url
									);
								}

								$event = array_merge($event, $time);

								$this_date = $r->reg_date ? date('Y-m-d H:i:s', strtotime($d)) : 'period';

								$registrations	= ($dp_regInfos == 1) ? true : false;
								$registered		= ($dp_regInfos == 1)
												? self::getNbTicketsBooked($this_date, $r->registered, $eventnumber, $set_date_in_url)
												: false;
								$maxTickets		= ($maxTickets != '1000000') ? $maxTickets : false;
								$TicketsLeft	= ($dp_regInfos == 1 && $maxTickets)
												? ($maxTickets - self::getNbTicketsBooked($this_date, $r->registered, $eventnumber, $set_date_in_url))
												: false;

								if ($maxTickets)
								{
									$date_sold_out	= ($TicketsLeft <= 0) ? true : false;
								}
								else
								{
									$date_sold_out	= false;
								}

								$reg_infos = array(
									'registrations'	=> $registrations,
									'registered'	=> $registered,
									'maxTickets'	=> $maxTickets,
									'TicketsLeft'	=> $TicketsLeft,
									'date_sold_out'	=> $date_sold_out
								);

								$event = array_merge($event, $reg_infos);

								foreach ($days as $k => $dy)
								{
									if (date('Y-m-d', strtotime($d)) == date('Y-m-d', strtotime($dy['date'])))
									{
										array_push ($days[$k]['events'], $event);
									}
								}
							}
						}
					}
				}
			}
		}

		$i='';

		if ($nolink || !JComponentHelper::getComponent('com_icagenda', true)->enabled)
		{
			do {
				echo '<div style="color:#a40505; text-align: center;"><b>info :</b></div><div style="color:#a40505; font-size: 0.8em; text-align: center;">'.JText::_( 'MOD_ICCALENDAR_COM_ICAGENDA_MENULINK_UNPUBLISHED_MESSAGE' ).'</div>';
			} while ($i > 0);
  		}

		$db = JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('id AS nbevt')->from('`#__icagenda_events` AS e')->where('e.state > 0');
		$db->setQuery($query);
		$nbevt=$db->loadResult();
		$nbevt=count($nbevt);

		if ($nbevt == NULL) {
//			do {
				echo '<div style="font-size: 0.8em; text-align: center;">'.JText::_( 'MOD_ICCALENDAR_NO_EVENT' ).'</div>';
//			} while ($i = 0);
  		}

		return $days;

	}

	public static function getNbTicketsBooked($date, $event_registered, $event_id, $set_date_in_url)
	{
		$event_registered	= is_array($event_registered) ? $event_registered : array();
		$nb_registrations	= 0;

		foreach ($event_registered AS $reg)
		{
			$ex_reg = explode('@@', $reg); // eventid@@date@@people

			if ( ! $date || $date == 'period')
			{
				$nb_registrations = $nb_registrations + $ex_reg[2];
			}
			elseif (date('Y-m-d H:i', strtotime($date)) == date('Y-m-d H:i', strtotime($ex_reg[1])))
			{
				$nb_registrations = $nb_registrations + $ex_reg[2];
			}
			elseif ( ! $set_date_in_url && $ex_reg[1] == 'period' && $event_id == $ex_reg[0])
			{
				$nb_registrations = $nb_registrations + $ex_reg[2];
			}
		}

		return $nb_registrations;
	}


	// test
	function clickDate ($eventdate, $d)
	{
		$eventdate = $d;
		return $eventdate;
	}


	/**
	 * To be moved to a special library
	 */
	// Function to get Format Date (using option format, and translation)
	protected function formatDate ($d)
	{
//		$mkt_date = $this->mkt($d);

		$for = '0';
		// Global Option for Date Format
		$date_format_global = JComponentHelper::getParams('com_icagenda')->get('date_format_global', 'Y - m - d');
		$date_format_global = $date_format_global ? $date_format_global : 'Y - m - d';

		// Menu Option for Date Format
		if(isset($this->format)) $for = $this->format;

		// default
		if (($for == NULL) OR ($for == '0'))
		{
			$for = isset($date_format_global) ? $date_format_global : 'Y - m - d';
		}

		if (!is_numeric($for))
		{
			//update default value, from 2.0.x to 2.1
			if ($for == '%d.%m.%Y') {$for = 'd m Y'; $separator = '.';}
			elseif ($for == '%d.%m.%y') {$for = 'd m y'; $separator = '.';}
			elseif ($for == '%Y.%m.%d') {$for = 'Y m d'; $separator = '.';}
			elseif ($for == '%Y.%b.%d') {$for = 'Y M d'; $separator = '.';}

			elseif ($for == '%d-%m-%Y') {$for = 'd m Y'; $separator = '-';}
			elseif ($for == '%d-%m-%y') {$for = 'd m y'; $separator = '-';}
			elseif ($for == '%Y-%m-%d') {$for = 'Y m d'; $separator = '-';}
			elseif ($for == '%Y-%b-%d') {$for = 'Y M d'; $separator = '-';}

			elseif ($for == '%d/%m/%Y') {$for = 'd m Y'; $separator = '/';}
			elseif ($for == '%d/%m/%y') {$for = 'd m y'; $separator = '/';}
			elseif ($for == '%Y/%m/%d') {$for = 'Y m d'; $separator = '/';}
			elseif ($for == '%Y/%b/%d') {$for = 'Y M d'; $separator = '/';}

			elseif ($for == '%d %B %Y') {$for = 'd F Y';}
			elseif ($for == '%d %b %Y') {$for = 'd M Y';}

			elseif ($for == '%A, %d %B %Y') {$for = 'l, _ d _ Fnosep _ Y';}
			elseif ($for == '%a %d %b %Y') {$for = 'D _ d _ Mnosep _ Y';}
			elseif ($for == '%A, %B %d, %Y') {$for = 'l, _ Fnosep _ d, _ Y';}
			elseif ($for == '%a, %b %d, %Y') {$for = 'D, _ Mnosep _ d, _ Y';}


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
		}


		// NEW DATE FORMAT GLOBALIZED 2.1.7

		$lang = JFactory::getLanguage();
		$langTag = $lang->getTag();
		$langName = $lang->getName();
		if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_icagenda/globalization/'.$langTag.'.php'))
		{
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

		// Explode components of the date
		$exformat = explode (' ', $for);
		$format='';

		// Day with no 0 (test if Windows server)
		$dayj = '%e';
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
			$dayj = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $dayj);
		}

		// Date Formatting using strings of Joomla Core Translations (update 3.1.4)
//		$dateFormat=date('Y-m-d H:i', $mkt_date);
		$dateFormat = date('Y-m-d H:i', strtotime($d));

		$separator = isset($this->date_separator) ? $this->date_separator : ' ';

		foreach ($exformat as $k=>$val)
		{
			switch ($val)
			{
				// day (v3)
				case 'd': $val=date("d", strtotime("$dateFormat")); break;
				case 'j': $val=strftime("$dayj", strtotime("$dateFormat")); break;
				case 'D': $val=JText::_(date("D", strtotime("$dateFormat"))); break;
				case 'l': $val=JText::_(date("l", strtotime("$dateFormat"))); break;
//				case 'dS': $val=strftime("%d", strtotime("$dateFormat")).'<sup>'.date("S", strtotime("$dateFormat")).'</sup>'; break;
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
				case '&#1088;.': $val = '&#1088;.'; break;



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


	// Function to get TimeZone offset
	function get_timezone_offset($remote_tz, $origin_tz = null)
	{
		if($origin_tz === null)
		{
			if(!is_string($origin_tz = date_default_timezone_get()))
			{
				return false; // A UTC timestamp was returned -- bail out!
			}
		}
		$origin_dtz = new DateTimeZone($origin_tz);
		$remote_dtz = new DateTimeZone($remote_tz);
		$origin_dt = new DateTime("now", $origin_dtz);
		$remote_dt = new DateTime("now", $remote_dtz);
		$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
		return $offset;
	}


	// Génération des jours du mois
	function getDays ($d, $f)
	{
		//update default value, from 1.2.2 to 1.2.3
		if ($f == 'd-m-Y') {
			$f = '%d-%m-%Y';
		}

		// détermine le mois et l'année
		$ex_data	= explode('-', $d);
		$month		= $ex_data[1];
//		$month		= $ex_data[1]+1;
		$year		= $ex_data[0];
		$jour		= $ex_data[2];
		$days_month	= $year . '-' . $month . '-00';

		// Génération du Calendrier
		$days = date("d", mktime(0, 0, 0, $month+1, 0, $year));
//		$days = date("d", strtotime($days_month));
		$list = array();


		//
		// Setting function of the visitor Time Zone
		//
		$today=time();

		$config = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$joomla_offset = $config->get('offset');
		} else {
			$joomla_offset = $config->getValue('config.offset');
		}

		$opt_TimeZone = '';
		$displayDatesTimezone = '0';
		if (isset($this->setTodayTimezone)) $opt_TimeZone = $this->setTodayTimezone;
//		if (isset($this->displayDatesTimezone)) $displayDatesTimezone = $this->displayDatesTimezone;

		$gmt_today = gmdate('Y-m-d H:i:s', $today);
		$today_timestamp = strtotime($gmt_today);

		$GMT_timezone = 'Etc/UTC';

		if($opt_TimeZone == 'SITE')
		{
			// Joomla Server Time Zone
			$visitor_timezone = $joomla_offset;
			$offset = $this->get_timezone_offset($GMT_timezone, $visitor_timezone);
			$visitor_today = date('Y-m-d H:i:s', ($today_timestamp+$offset));
			$UTCsite = $offset / 3600;
			if ($UTCsite > 0) $UTCsite = '+'.$UTCsite;
			if ($displayDatesTimezone == '1') {
				echo '<small>'.JHtml::date('now', 'Y-m-d H:i:s', true).' UTC'.$UTCsite.'</small><br />';
			}
		}
		elseif ($opt_TimeZone == 'UTC')
		{
			// UTC Time Zone
			$offset = 0;
			$visitor_today = date('Y-m-d H:i:s', ($today_timestamp+$offset));
			$UTC = $offset / 3600;
			if ($UTC > 0) $UTC = '+'.$UTC;
			if ($displayDatesTimezone == '1') {
				echo '<small>'.gmdate('Y-m-d H:i:s', $today).' UTC'.$UTC.'</small><br />';
			}
		}
		else
		{
			$visitor_today = date('Y-m-d H:i:s', ($today_timestamp));
		}

		$date_today=str_replace(' ', '-', $visitor_today);
		$date_today=str_replace(':', '-', $date_today);
		$ex_data=explode('-', $date_today);
		$v_month=$ex_data[1];
		$v_year=$ex_data[0];
		$v_day=$ex_data[2];
		$v_hours=$ex_data[3];
		$v_minutes=$ex_data[4];

		for($a = 1; $a <= $days; $a++)
		{
			if (($a == $v_day) && ($month == $v_month) && ($year == $v_year))
			{
				$classDay = 'style_Today';
			}
			else
			{
				$classDay = 'style_Day';
			}

//			$datejour = date('Y-m-d', mktime(0, 0, 0, $month, $a, $year));
			$this_date_a = $year . '-' . $month . '-' . $a;

			$datejour = date('Y-m-d', strtotime($this_date_a));

//			$list[$a]['date'] = date('Y-m-d H:i', mktime(0, 0, 0, $month, $a, $year));
//			$list[$a]['dateFormat'] = strftime($f, mktime(0, 0, 0, $month, $a, $year));
			$list[$a]['date']		= date('Y-m-d H:i', strtotime($this_date_a));
			$list[$a]['dateFormat']	= strftime($f, strtotime($this_date_a));
			$list[$a]['dateTitle']	=  $this->formatDate($datejour);
//			$list[$a]['week'] = date('N', mktime(0, 0, 0, $month, $a, $year));
			$list[$a]['week']		= date('N', strtotime($this_date_a));

			$list[$a]['day']		= "<div class='".$classDay."'>" . $a . "</div>";

			// Set cal_date
			$list[$a]['this_day']	= substr($list[$a]['date'], 0, 10);

			// Added in 2.1.2 (change in NAME_day.php)
			$list[$a]['ifToday']	= $classDay;
			$list[$a]['Days']		= $a;
			//
			$list[$a]['month']		= $month;
			$list[$a]['year']		= $year;
			$list[$a]['events']		= array();
//			$list[$a]['plus'] = "$stamp->events[1]['cat_color']";

		}

		return $list;
	}
	/***/

	/**
	 * liste des dates pour un évènement
	 */
	private function getDatelist($dates, $next)
	{
//		$dates	= unserialize($dates);
		$dates = iCString::isSerialized($dates) ? unserialize($dates) : array();
		$da		= array();

		foreach ($dates as $d)
		{
//			$d = $this->mkttime($d);

			if (strtotime($d) >= strtotime($next))
			{
//				array_push($da, date('Y-m-d H:i', $d));
				array_push($da, date('Y-m-d H:i', strtotime($d)));
			}
		}

		return $da;
	}

	private function getPeriodlist($period, $next)
	{
		if ($period)
		{
//			$period	= unserialize($period);
			$period = iCString::isSerialized($period) ? unserialize($period) : array();
			$da		= array();

			foreach ($period as $d)
			{
//				$d = $this->mkttime($d);

				if (strtotime($d) >= strtotime($next))
				{
//					array_push($da, date('Y-m-d H:i', $d));
					array_push($da, date('Y-m-d H:i', strtotime($d)));
				}
			}
		}
		else
		{
			$da = NULL;
		}

		return $da;
	}

	private function getmlist($dates, $next)
	{
//		$dates	= unserialize($dates);
		$dates = iCString::isSerialized($dates) ? unserialize($dates) : array();
		$da		= array();

		foreach($dates as $d)
		{
//			$d = $this->mkttime($d);

			if (strtotime($d) >= strtotime($next))
			{
//				array_push($da, date('Y-m-d H:i', $d));
				array_push($da, date('Y-m-d H:i', strtotime($d)));
			}
		}

		return $da;
	}

	private function getplist($period, $next)
	{
		if ($period)
		{
			$period	= unserialize($period);
			$da		= array();

			foreach ($period as $d)
			{
//				$d = $this->mkttime($d);

				if (strtotime($d) >= strtotime($next))
				{
//					array_push($da, date('Y-m-d H:i', $d));
					array_push($da, date('Y-m-d H:i', strtotime($d)));
				}
			}
		}
		else
		{
			$da = NULL;
		}

		return $da;
	}

	// Format Date
//	private function mkt($data)
//	{
//		$data=str_replace(' ', '-', $data);
//		$data=str_replace(':', '-', $data);
//		$ex_data=explode('-', $data);
//		$ris=mktime('0', '0', '0', $ex_data['1'], $ex_data['2'], $ex_data['0']);
//		return strftime($ris);
//	}

//	private function mkttime($data)
//	{
//		$data=str_replace(' ', '-', $data);
//		$data=str_replace(':', '-', $data);
//		$ex_data=explode('-', $data);
//		if (isset($ex_data['3'])) {
//			$ris=mktime($ex_data['3'], $ex_data['4'], '00', $ex_data['1'], $ex_data['2'], $ex_data['0']);
//		} else {
//			$ris=mktime('00', '00', '00', $ex_data['1'], $ex_data['2'], $ex_data['0']);
//		}
//		return strftime($ris);
//	}

	//
//	private function addDay ($mkt)
//	{
//		return $mkt+82800;
//	}



	/***/

	/** Systeme de navigation **/
	function getNav($date_start, $modid)
	{
		$app = JFactory::getApplication();
		$isSef = $app->getCfg( 'sef' );

		// Return Current URL
		$url = JUri::getInstance()->toString();
		$url = preg_replace('/&iccaldate=[^&]*/', '', $url);
		$url = preg_replace('/\?iccaldate=[^\?]*/', '', $url);

		$separator = strpos($url, '?') !== false ? '&amp;' : '?';
		$url = htmlspecialchars($url);
//		$separator = ($isSef == 1) ? '?' : '&';

		$ex_date = explode('-', $date_start);
//		$mkt_date = $this->mkt($date_start);
		$year = $ex_date[0];
		$month = $ex_date[1];
		$day = 1;

		if ($month != 1)
		{
			$backMonth = $month-1;
			$backYear = $year;
		}
		elseif ($month == 1)
		{
			$backMonth = 12;
			$backYear = $year-1;
		}

		if ($month != 12)
		{
			$nextMonth = $month+1;
			$nextYear = $year;
		}
		elseif ($month == 12)
		{
			$nextMonth = 1;
			$nextYear = $year+1;
		}

		$backYYear = $year-1;
		$nextYYear = $year+1;

		// Set Navigation Arrows
		$backY = '<a class="backicY icagendabtn_'.$modid.'" href="'.$url.$separator.'iccaldate='.$backYYear.'-'.$month.'-'.$day.'" rel="nofollow"><span class="iCicon iCicon-backicY"></span></a>';
		$back = '<a class="backic icagendabtn_'.$modid.'" href="'.$url.$separator.'iccaldate='.$backYear.'-'.$backMonth.'-'.$day.'" rel="nofollow"><span class="iCicon iCicon-backic"></span></a>';

		$next = '<a class="nextic icagendabtn_'.$modid.'" href="'.$url.$separator.'iccaldate='.$nextYear.'-'.$nextMonth.'-'.$day.'" rel="nofollow"><span class="iCicon iCicon-nextic"></span></a>';
		$nextY = '<a class="nexticY icagendabtn_'.$modid.'" href="'.$url.$separator.'iccaldate='.$nextYYear.'-'.$month.'-'.$day.'" rel="nofollow"><span class="iCicon iCicon-nexticY"></span></a>';

		if (!$this->month_nav) $back = $next = '';
		if (!$this->year_nav) $backY = $nextY = '';

	/** translate the month in the calendar module -- Leland Vandervort **/
//		$dateFormat = date('d-M-Y', $mkt_date);
		$dateFormat = date('d-M-Y', strtotime($date_start));

		// split out the month and year to obtain translation key for JText using joomla core translation
		$t_day = strftime("%d", strtotime("$dateFormat"));
		$t_month = date("F", strtotime("$dateFormat"));
		$t_year = strftime("%Y", strtotime("$dateFormat"));

		$lang = JFactory::getLanguage();
		$langTag = $lang->getTag();
		$yearBeforeMonth = array('ar-AA', 'ja-JP');

		if (in_array($langTag, $yearBeforeMonth))
		{
			$monthBeforeYear = 0;
		}
		else
		{
			$monthBeforeYear = 1;
		}
		/**
		 * Get monthBeforeYear metadata param from Current Language xml file
		 *
		 * This will load strings from language packs to set prefix, suffix, separator for month and year if needed.
		 * If not needed by a language, the translation string could be empty, or a copy/paste of en-GB source translation
		 * (usage of a sentence to help understanding on a translation platform such as Transifex)
		 *
		 * This feature has been proposed to Joomla core recently, so not yet available officially : https://github.com/joomla/joomla-cms/pull/2809
		 */

//		if(version_compare(JVERSION, '3.2', 'ge')) {
//			$monthBeforeYear = JFactory::getLanguage()->getMonthBeforeYear();
//		} else {
//			$monthBeforeYear = 1;
//		}

		/**
		 * Get prefix, suffix and separator for month and year in calendar title
		 */

		// Separator Month/Year
		$separator_month_year = JText::_('SEPARATOR_MONTH_YEAR');
		if ($separator_month_year == 'CALENDAR_SEPARATOR_MONTH_YEAR_FACULTATIVE')
		{
			$separator_month_year = ' ';
		}
		elseif ($separator_month_year == 'NO_SEPARATOR')
		{
			$separator_month_year = '';
		}

		// Prefix Month (Facultative)
		$prefix_month = JText::_('PREFIX_MONTH');
		if ($prefix_month == 'CALENDAR_PREFIX_MONTH_FACULTATIVE')
		{
			$prefix_month = '';
		}

		// Suffix Month (Facultative)
		$suffix_month = JText::_('SUFFIX_MONTH');
		if ($suffix_month == 'CALENDAR_SUFFIX_MONTH_FACULTATIVE')
		{
			$suffix_month = '';
		}

		// Prefix Year (Facultative)
		$prefix_year = JText::_('PREFIX_YEAR');
		if ($prefix_year == 'CALENDAR_PREFIX_YEAR_FACULTATIVE')
		{
			$prefix_year = '';
		}

		// Suffix Year (Facultative)
		$suffix_year = JText::_('SUFFIX_YEAR');
		if ($suffix_year == 'CALENDAR_SUFFIX_YEAR_FACULTATIVE')
		{
			$suffix_year = '';
		}

		$SEP	= $separator_month_year;
		$PM		= $prefix_month;
		$SM		= $suffix_month;
		$PY		= $prefix_year;
		$SY		= $suffix_year;

		// Get MONTH_CAL string or if not translated, use MONTHS
		$array_months = array(
			'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
			'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'
		);

		$cal_string = $t_month.'_CAL';
//		$missing_cal_string = function_exists('mb_strtoupper') ? JText::_(mb_strtoupper($cal_string, 'UTF-8')) : JText::_(strtoupper($cal_string));
		$missing_cal_string = iCFilterOutput::stringToJText($cal_string);


		if ( in_array( $missing_cal_string, $array_months) )
		{
			// if MONTHS_CAL strings not translated in current language, use MONTHS strings
			$month_J = JText::_( $t_month );
		}
		else
		{
			// Use MONTHS_CAL strings when translated in current language
			$month_J = JText::_( $t_month.'_CAL' );
		}

		// Set Calendar Title
		if ($monthBeforeYear == 0) {
			$title = $PY . $t_year . $SY . $SEP . $PM . $month_J . $SM;
		} else {
			$title = $PM . $month_J . $SM . $SEP . $PY . $t_year . $SY;
		}

		// Set Nav Bar for calendar
		$html='<div class="icnav">'.$backY.$back.$nextY.$next.'<div class="titleic">'.$title.'</div></div>';
		$html.='<div style="clear:both"></div>';

		return $html;
	}

//	public function getMonthBeforeYear()
//	{
//		return (int) (isset($this->metadata['monthBeforeYear']) ? $this->metadata['monthBeforeYear'] : 1);
//	}


}

/**
 * Process a string in a JOOMLA_TRANSLATION_STRING standard.
 * This method processes a string and replaces all accented UTF-8 characters by unaccented
 * ASCII-7 "equivalents" and the string is uppercase. Spaces replaced by underscore.
 *
 * @param   string  $string  String to process
 *
 * @return  string  Processed string
 *
 * @since   3.3.3
 */


function deleteAllBetween($start, $end, $string)
{
	$startPos = strpos($string, $start);
	$endPos = strpos($string, $end);
	if (!$startPos || !$endPos)
	{
		return $string;
	}

	$textToDelete = substr($string, $startPos, ($endPos + strlen($end)) - $startPos);

	return str_replace($textToDelete, '', $string);
}

/* Removed to iC Library

function stringToJText($string)
{
	// Remove any '-' from the string since they will be used as concatenaters
	$str = str_replace('_', ' ', $string);

	$lang = JFactory::getLanguage();
	$str = $lang->transliterate($str);

	// Trim white spaces at beginning and end of translation string and make uppercase
	$str = trim(JString::strtoupper($str));

	// Remove any duplicate whitespace, and ensure all characters are alphanumeric
	$str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '_', $str);

	// Trim spaces at beginning and end of translation string
	$str = trim($str, '_');

	return $str;
}



function activeColor($color)
{
	#convert hexadecimal to RGB
	if(!is_array($color) && preg_match("/^[#]([0-9a-fA-F]{6})$/",$color)){
		$hex_R = substr($color,1,2);
		$hex_G = substr($color,3,2);
		$hex_B = substr($color,5,2);
		$RGB = hexdec($hex_R).",".hexdec($hex_G).",".hexdec($hex_B);
		return $RGB;
	}
}
*/

class cal
{
	public $data;
	public $template;
	public $t_calendar;
	public $t_day;
	public $nav;
	public $fontcolor;
	private $header_text;

	function __construct ($data, $t_calendar, $t_day, $nav,
		$mon, $tue, $wed, $thu, $fri, $sat, $sun,
		$firstday,
		$calfontcolor, $OneEventbgcolor, $Eventsbgcolor, $bgcolor, $bgimage, $bgimagerepeat,
		$na, $nb, $nc, $nd, $ne, $nf, $ng,
		$moduleclass_sfx, $modid, $template, $ictip_ordering, $header_text)
	{
		$this->data = $data;
		$this->t_calendar = $t_calendar;
		$this->t_day = $t_day;
		$this->nav = $nav;
		$this->mon = $mon;
		$this->tue = $tue;
		$this->wed = $wed;
		$this->thu = $thu;
		$this->fri = $fri;
		$this->sat = $sat;
		$this->sun = $sun;
		$this->na = $na;
		$this->nb = $nb;
		$this->nc = $nc;
		$this->nd = $nd;
		$this->ne = $ne;
		$this->nf = $nf;
		$this->ng = $ng;
		$this->firstday = $firstday;
		$this->calfontcolor = $calfontcolor;
		$this->OneEventbgcolor = $OneEventbgcolor;
		$this->Eventsbgcolor = $Eventsbgcolor;
		$this->bgcolor = $bgcolor;
		$this->bgimage = $bgimage;
		$this->bgimagerepeat = $bgimagerepeat;
		$this->moduleclass_sfx = $moduleclass_sfx;
		$this->modid = $modid;
		$this->template = $template;
		$this->ictip_ordering = $ictip_ordering;
		$this->header_text = $header_text;


	}

	function days ()
	{
		$this_calfontcolor	= str_replace(' ', '', $this->calfontcolor);
		$calfontcolor		= !empty($this_calfontcolor) ? 'color:'.$this->calfontcolor.';' : '';
		$this_bgcolor		= str_replace(' ', '', $this->bgcolor);
		$bgcolor			= !empty($this_bgcolor) ? 'background-color:'.$this->bgcolor.';' : '';
		$this_bgimage		= str_replace(' ', '', $this->bgimage);
		$bgimage			= !empty($this_bgimage) ? 'background-image:url(\''.$this->bgimage.'\');' : '';
		$this_bgimagerepeat	= str_replace(' ', '', $this->bgimagerepeat);
		$bgimagerepeat		= !empty($this_bgimagerepeat) ? 'background-repeat:'.$this->bgimagerepeat.';' : '';
		$iCcal_style = '';
		if ( !empty($this_calfontcolor)
			OR !empty($this_bgcolor)
			OR !empty($this_bgimage)
			OR !empty($this_bgimagerepeat) )
		{
			$iCcal_style.= 'style="';
		}
		if ( !empty($this_calfontcolor) )
		{
			$iCcal_style.= $calfontcolor;
		}
		if ( !empty($this_bgcolor) )
		{
			$iCcal_style.= $bgcolor;
		}
		if ( !empty($this_bgimage) )
		{
			$iCcal_style.= $bgimage;
		}
		if ( !empty($this_bgimagerepeat)
			&& !empty($this_bgimage) )
		{
			$iCcal_style.= $bgimagerepeat;
		}
		if (empty($this_bgcolor)
			&& empty($this_bgimage))
		{
			$iCcal_style.= 'background-color: transparent; background-image: none';
		}
		$iCcal_style.= '"';

		// Verify Hex color strings
		$OneEventbgcolor = preg_match('/^#[a-f0-9]{6}$/i', $this->OneEventbgcolor) ? $this->OneEventbgcolor : '';
		$Eventsbgcolor = preg_match('/^#[a-f0-9]{6}$/i', $this->Eventsbgcolor) ? $this->Eventsbgcolor : '';


		echo '<div class="'.$this->template.' iccalendar '.$this->moduleclass_sfx.'" '.$iCcal_style.' id="'.$this->modid.'">';


		if ($this->firstday=='0') {
			echo '<div id="mod_iccalendar_'.$this->modid.'">
			<div class="icagenda_header">'.$this->header_text.'
			</div>'.$this->nav.'
			<table id="icagenda_calendar" style="width:100%;">
				<thead>
					<tr>
						<th style="width:14.2857143%;background:'.$this->sun.';">'.JText::_('SUN').'</th>
						<th style="width:14.2857143%;background:'.$this->mon.';">'.JText::_('MON').'</th>
						<th style="width:14.2857143%;background:'.$this->tue.';">'.JText::_('TUE').'</th>
						<th style="width:14.2857143%;background:'.$this->wed.';">'.JText::_('WED').'</th>
						<th style="width:14.2857143%;background:'.$this->thu.';">'.JText::_('THU').'</th>
						<th style="width:14.2857143%;background:'.$this->fri.';">'.JText::_('FRI').'</th>
						<th style="width:14.2857143%;background:'.$this->sat.';">'.JText::_('SAT').'</th>
					</tr>
				</thead>
		';
		}
		elseif ($this->firstday=='1') {
			echo '<div id="mod_iccalendar_'.$this->modid.'">
			<div class="icagenda_header">'.$this->header_text.'
			</div>'.$this->nav.'
			<table id="icagenda_calendar" style="width:100%;">
				<thead>
					<tr>
						<th style="width:14.2857143%;background:'.$this->mon.';">'.JText::_('MON').'</th>
						<th style="width:14.2857143%;background:'.$this->tue.';">'.JText::_('TUE').'</th>
						<th style="width:14.2857143%;background:'.$this->wed.';">'.JText::_('WED').'</th>
						<th style="width:14.2857143%;background:'.$this->thu.';">'.JText::_('THU').'</th>
						<th style="width:14.2857143%;background:'.$this->fri.';">'.JText::_('FRI').'</th>
						<th style="width:14.2857143%;background:'.$this->sat.';">'.JText::_('SAT').'</th>
						<th style="width:14.2857143%;background:'.$this->sun.';">'.JText::_('SUN').'</th>
					</tr>
				</thead>
		';
		}

		switch ($this->data[1]['week']){
			case $this->na:
				break;
			default:
				echo '<tr><td colspan="'.($this->data[1]['week']-$this->firstday).'"></td>';
				break;
		}

		foreach ($this->data as $d){
			$stamp= new day($d);

			if ($this->firstday=='0') {
				switch($stamp->week){
					case $this->na:
						echo '<tr><td style="background:'.$this->sun.';">';
						break;
					case $this->nb:
						echo '<td style="background:'.$this->mon.';">';
						break;
					case $this->nc:
						echo '<td style="background:'.$this->tue.';">';
						break;
					case $this->nd:
						echo '<td style="background:'.$this->wed.';">';
						break;
					case $this->ne:
						echo '<td style="background:'.$this->thu.';">';
						break;
					case $this->nf:
						echo '<td style="background:'.$this->fri.';">';
						break;
					case $this->ng:
						echo '<td style="background:'.$this->sat.';">';
						break;
					default:
						echo '<td>';
						break;
				}
			}

			if ($this->firstday=='1') {
				switch($stamp->week){
					case $this->na:
						echo '<tr><td style="background:'.$this->mon.';">';
						break;
					case $this->nb:
						echo '<td style="background:'.$this->tue.';">';
						break;
					case $this->nc:
						echo '<td style="background:'.$this->wed.';">';
						break;
					case $this->nd:
						echo '<td style="background:'.$this->thu.';">';
						break;
					case $this->ne:
						echo '<td style="background:'.$this->fri.';">';
						break;
					case $this->nf:
						echo '<td style="background:'.$this->sat.';">';
						break;
					case $this->ng:
						echo '<td style="background:'.$this->sun.';">';
						break;
					default:
						echo '<td>';
						break;
				}
			}
			$count_events = count($stamp->events);

			if ($OneEventbgcolor
				AND $OneEventbgcolor != ' '
				AND $count_events == '1')
			{
				$bg_day = $OneEventbgcolor;
			}
			elseif ($Eventsbgcolor
				AND $Eventsbgcolor != ' '
				AND $count_events > '1')
			{
				$bg_day = $Eventsbgcolor;
			}
			else
			{
				$bg_day = isset($stamp->events[0]['cat_color']) ? $stamp->events[0]['cat_color'] : '#d4d4d4';
			}

//			$bgcolor ='';
//			if (isset($bg_day)) {
//				$RGB = explode(",",activeColor($bg_day));
//				$c = array($RGB[0], $RGB[1], $RGB[2]);
//				$bgcolor = array_sum($c);
//			}
//			if ($bgcolor > '600') {
//				$bgcolor='bright';
//			} else {
//				$bgcolor='';
//			}
			$bgcolor = iCColor::getBrightness($bg_day);
			$bgcolor = ($bgcolor == 'bright') ? 'ic-bright' : 'ic-dark';
			$order='first';

			$multi_events = isset($stamp->events[1]['cat_color']) ? 'icmulti' : '';

			// Ordering by time New Theme Packs (since 3.2.9)
			$events = $stamp->events;

			// Option for Ordering is not yet finished. This developpement is in brainstorming...
			$ictip_ordering = '1';
			$ictip_ordering = $this->ictip_ordering;

			if ($ictip_ordering == '1_ASC-1_ASC' OR $ictip_ordering == '1_ASC-1_DESC') $ictip_ordering = '1_ASC';
			if ($ictip_ordering == '2_ASC-2_ASC' OR $ictip_ordering == '2_ASC-2_DESC') $ictip_ordering = '2_ASC';
			if ($ictip_ordering == '1_DESC-1_ASC' OR $ictip_ordering == '1_DESC-1_DESC') $ictip_ordering = '1_DESC';
			if ($ictip_ordering == '2_DESC-2_ASC' OR $ictip_ordering == '2_DESC-2_DESC') $ictip_ordering = '2_DESC';

			// Create Functions for Ordering
			$newfunc_1_ASC_2_ASC = create_function('$a, $b', 'if ($a["time"] == $b["time"]){ return strcasecmp($a["cat_title"], $b["cat_title"]); } else { return strcasecmp($a["time"], $b["time"]); }');
			$newfunc_1_ASC_2_DESC = create_function('$a, $b', 'if ($a["time"] == $b["time"]){ return strcasecmp($b["cat_title"], $a["cat_title"]); } else { return strcasecmp($a["time"], $b["time"]); }');
			$newfunc_1_DESC_2_ASC = create_function('$a, $b', 'if ($a["time"] == $b["time"]){ return strcasecmp($a["cat_title"], $b["cat_title"]); } else { return strcasecmp($b["time"], $a["time"]); }');
			$newfunc_1_DESC_2_DESC = create_function('$a, $b', 'if ($a["time"] == $b["time"]){ return strcasecmp($b["cat_title"], $a["cat_title"]); } else { return strcasecmp($b["time"], $a["time"]); }');

			$newfunc_2_ASC_1_ASC = create_function('$a, $b', 'if ($a["cat_title"] == $b["cat_title"]){ return strcasecmp($a["time"], $b["time"]); } else { return strcasecmp($a["cat_title"], $b["cat_title"]); }');
			$newfunc_2_ASC_1_DESC = create_function('$a, $b', 'if ($a["cat_title"] == $b["cat_title"]){ return strcasecmp($b["time"], $a["time"]); } else { return strcasecmp($a["cat_title"], $b["cat_title"]); }');
			$newfunc_2_DESC_1_ASC = create_function('$a, $b', 'if ($a["cat_title"] == $b["cat_title"]){ return strcasecmp($a["time"], $b["time"]); } else { return strcasecmp($b["cat_title"], $a["cat_title"]); }');
			$newfunc_2_DESC_1_DESC = create_function('$a, $b', 'if ($a["cat_title"] == $b["cat_title"]){ return strcasecmp($b["time"], $a["time"]); } else { return strcasecmp($b["cat_title"], $a["cat_title"]); }');

			$newfunc_1_ASC = create_function('$a, $b', 'return strcasecmp($a["time"], $b["time"]);');
			$newfunc_2_ASC = create_function('$a, $b', 'return strcasecmp($a["cat_title"], $b["cat_title"]);');

			$newfunc_1_DESC = create_function('$a, $b', 'return strcasecmp($b["time"], $a["time"]);');
			$newfunc_2_DESC = create_function('$a, $b', 'return strcasecmp($b["cat_title"], $a["cat_title"]);');

			// Order by time - Old Theme Packs (before 3.2.9) : Update Theme Pack to get all options
			usort($stamp->events, $newfunc_1_ASC_2_ASC);

			// Time ASC and if same time : Category Title ASC (default)
			if ($ictip_ordering == '1_ASC-2_ASC')
			{
				usort($events, $newfunc_1_ASC_2_ASC);
			}
			// Time ASC and if same time : Category Title DESC
			if ($ictip_ordering == '1_ASC-2_DESC')
			{
				usort($events, $newfunc_1_ASC_2_DESC);
			}
			// Time DESC and if same time : Category Title ASC
			if ($ictip_ordering == '1_DESC-2_ASC')
			{
				usort($events, $newfunc_1_DESC_2_ASC);
			}
			// Time DESC and if same time : Category Title DESC
			if ($ictip_ordering == '1_DESC-2_DESC')
			{
				usort($events, $newfunc_1_DESC_2_DESC);
			}

			// Category Title ASC and if same category : Time ASC
			if ($ictip_ordering == '2_ASC-1_ASC')
			{
				usort($events, $newfunc_2_ASC_1_ASC);
			}
			// Category Title ASC and if same category : Time DESC
			if ($ictip_ordering == '2_ASC-1_DESC')
			{
				usort($events, $newfunc_2_ASC_1_DESC);
			}
			// Category Title DESC and if same category : Time ASC
			if ($ictip_ordering == '2_DESC-1_ASC')
			{
				usort($events, $newfunc_2_DESC_1_ASC);
			}
			// Category Title DESC and if same category : Time DESC
			if ($ictip_ordering == '2_DESC-1_DESC')
			{
				usort($events, $newfunc_2_DESC_1_DESC);
			}

			// If main ordering and sub-ordering on Time : set TIME ASC (with no sub-ordering)
			if ($ictip_ordering == '1_ASC')
			{
				usort($events, $newfunc_1_ASC);
			}
			// If main ordering and sub-ordering on Category Title : set CATEGORY TITLE ASC (with no sub-ordering)
			if ($ictip_ordering == '2_ASC')
			{
				usort($events, $newfunc_2_ASC);
			}


			// Load tempalte for day infotip
			require $this->t_day;

			switch('week'){
				case $this->ng:
					echo '</td></tr>';
					break;
				default:
					echo '</td>';
					break;
			}
		}

		switch ($stamp->week){
			case $this->ng:
				break;
			default:
				echo '<td colspan="'.(7-$stamp->week).'"></td></tr>';
				break;
		}

		echo '</table></div>';

		echo '</div>';

	}

}

class day
{
	public $date;
	public $week;
	public $day;
	public $month;
	public $year;
	public $events;
	public $fontcolor;

	function __construct ($day)
	{
		foreach ($day as $k=>$v){
			$this->$k=$v;
		}
	}
}
