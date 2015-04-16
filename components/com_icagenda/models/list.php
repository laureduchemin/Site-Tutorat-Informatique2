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
 * @version     3.5.3 2015-03-20
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport( 'joomla.filesystem.path' );

// Load file helpers

if(!class_exists('iCModelItem'))require(JPATH_COMPONENT.'/helpers/icmodel.php');
if(!class_exists('iCModeliChelper'))require(JPATH_COMPONENT.'/helpers/ichelper.php');


/**
 * icagenda Model
 */
class icagendaModelList extends iCModelItem
{
	/**
	 * Get Form - Registration
	 *
	 * @since	3.4.1
	 */
	public function getForm()
	{
	    $form = JForm::getInstance('submit', JPATH_COMPONENT . '/models/forms/registration.xml');

		if (empty($form))
		{
			return false;
		}

	    return $form;
	}

	/**
	 * Get Params
	 *
	 * @since	1.0
	 */
	public function getData()
	{
		$this->startiCModel();
		$user = JFactory::getUser();
		$userid=$user->get('id');

		$regpost = JRequest::get( 'post' );

		if (JRequest::getVar('event'))
		{
			$this->registration($regpost);
		}

		// Import params
		$app = JFactory::getApplication();
		$icpar = $app->getParams();

		// filters
		$this->addFilter('state', 1);

		$id = JRequest::getInt('id');
		$Itemid = JRequest::getInt('Itemid');

		if ($id)
		{
			$this->addFilter('id', $id);
		}
		else
		{
			if(JRequest::getVar('key', '', 'post')) $this->addFilter('key', JRequest::getVar('key', '','post'));
			if($icpar->get('mcatid')) $this->addFilter('e.catid', $icpar->get('mcatid'));
			if($icpar->get('place')) $this->addFilter('e.place', $icpar->get('place'));
			if($icpar->get('address')) $this->addFilter('e.address', $icpar->get('address'));
			if($icpar->get('time')) $this->addFilter('next', $icpar->get('time'));
		}

		$this->addOption('Itemid', $icpar->get('itemid', $Itemid));

		// Get Option Menu and Global for Type of Display for List of Events (all dates, or only next date, for each event)
		$this->addOption('datesDisplay', $icpar->get('datesDisplay', 1));

		$this->addOption('filterTime', $icpar->get('time', 1));

		// Menu Options
		$this->addOption('number', $icpar->get('number', 5));

		// App Options
		$this->addOption('orderby', $icpar->get('orderby', 2));
		$this->addOption('mcatid', $icpar->get('mcatid', array('0')));
		$this->addOption('format', $icpar->get('format', 0));

		// Global Options
		$this->addOption('addthis', $icpar->get('addthis', ''));
		$this->addOption('atevent', $icpar->get('atevent', 1));
		$this->addOption('atfloat', $icpar->get('atfloat', ''));
		$this->addOption('aticon', $icpar->get('aticon', ''));
		$this->addOption('emailRequired', $icpar->get('emailRequired', 0));
		$this->addOption('limit', $icpar->get('limit', 100));
		$this->addOption('limitGlobal', $icpar->get('limitGlobal', 0));
		$this->addOption('maxRlist', $icpar->get('maxRlist', ''));
		$this->addOption('participantList', $icpar->get('participantList', ''));
		$this->addOption('participantSlide', $icpar->get('participantSlide', ''));
		$this->addOption('phoneRequired', $icpar->get('phoneRequired', 0));
		$this->addOption('RegButtonText', $icpar->get('RegButtonText', ''));
		$this->addOption('statutReg', $icpar->get('statutReg', ''));
		$this->addOption('timeformat', $icpar->get('timeformat', 1));

		if($icpar->get('participantDisplay')) $this->addOption('participantDisplay', $icpar->get('participantDisplay'));
		if($icpar->get('fullListColumns')) $this->addOption('fullListColumns', $icpar->get('fullListColumns'));
		if($icpar->get('targetLink')) $this->addOption('targetLink', $icpar->get('targetLink'));
		if($icpar->get('arrowtext')) $this->addOption('arrowtext', $icpar->get('arrowtext'));
		if($icpar->get('accessReg')) $this->addOption('accessReg', $icpar->get('accessReg'));
		if($icpar->get('limitRegEmail')) $this->addOption('limitRegEmail', $icpar->get('limitRegEmail'));
		if($icpar->get('limitRegDate')) $this->addOption('limitRegDate', $icpar->get('limitRegDate'));
		if($icpar->get('maxReg')) $this->addOption('maxReg', $icpar->get('maxReg'));
		if($icpar->get('regEmailUser')) $this->addOption('regEmailUser', $icpar->get('regEmailUser'));
		if($icpar->get('emailUserSubjectPeriod')) $this->addOption('emailUserSubjectPeriod', $icpar->get('emailUserSubjectPeriod'));
		if($icpar->get('emailUserBodyPeriod')) $this->addOption('emailUserBodyPeriod', $icpar->get('emailUserBodyPeriod'));
		if($icpar->get('emailUserSubjectDate')) $this->addOption('emailUserSubjectDate', $icpar->get('emailUserSubjectDate'));
		if($icpar->get('emailUserBodyDate')) $this->addOption('emailUserBodyDate', $icpar->get('emailUserBodyDate'));
		if($icpar->get('headerList')) $this->addOption('headerList', $icpar->get('headerList'));

		if($icpar->get('date_format')) $this->addOption('date_format', $icpar->get('date_format'));
		if($icpar->get('date_separator')) $this->addOption('date_separator', $icpar->get('date_separator'));
		if($icpar->get('m_width')) $this->addOption('m_width', $icpar->get('m_width'));
		if($icpar->get('m_height')) $this->addOption('m_height', $icpar->get('m_height'));


		// Struture
		$structure = array(
//			'container'=>array(
//				'header'=>'',
//				'navigator'=>'',
//			),
			'items' => array(
				'item' => array(
					'accessReg'=>'',
					'approval'=>'',
					'eventAllDates'=>'',
					'eventHasPeriod'=>'',
					'infoDetails'=>'',
					'statutReg'=>'',

					'BackArrow'=>'',
					'BackURL'=>'',
					'id'=>'',
					'Itemid'=>'',
					'metaTitle'=>'',
					'metaDesc'=>'',
					'metaAsShortDesc'=>'',
					'state'=>'',
					'weekday'=>'',
					'weekdayShort'=>'',
					'timeformat'=>'',
					'participantList'=>'',
					'participantSlide'=>'',
					'participantDisplay'=>'',
					'fullListColumns'=>'',
					'participantListTitle'=>'',
					'arrowtext'=>'',
					'navposition'=>'',
					'headerList'=>'',
					'title'=>'',
					'titlebar'=>'',
					'ManagerIcons'=>'',
					'url'=>NULL,
					'Event_Link'=>'',
					'cat_id'=>'',
					'cat_title'=>'',
					'cat_color'=>'',
					'fontColor'=>'',
					'cat_desc'=>'',
					'shortdesc'=>'',
					'desc'=>'',
					'shortDescription'=>'',
					'description'=>'',
					'descShort'=>'',
					'image'=>'',
					'imageTag'=>'',
					'file'=>'',
					'fileTag'=>'',

					'displaytime'=>'',
					'next'=>'',
					'nextDate'=>'',
					'period'=>'',
					'startdatetime'=>'',
					'enddatetime'=>'',
					'nextControl'=>'',

					'start_datetime'=>'',
					'end_datetime'=>'',
					'dates'=>'',

					'startDate'=>'',
					'startDay'=>'',

					'endDate'=>'',
					'endDay'=>'',
					'endMonth'=>'',
					'endMonthNum'=>'',
					'endYear'=>'',

					'startTime'=>'',
					'endTime'=>'',

					'periodDates'=>'',
					'dateText'=>'',
					'periodDisplay'=>'',
					'periodControl'=>'',
					'weekdays'=>'',
					'day'=>'',
					'maxNbTickets'=>'',
					'ticketsCouldBeBooked'=>'',
					'registeredForThisDate'=>'',
					'maxReg'=>'',
					'maxRlist'=>'',
					'emailRequired'=>'',
					'phoneRequired'=>'',

					'month'=>'',
					'monthNum'=>'',

					'year'=>'',
					'yearShort'=>'',
					'evenTime'=>'',
					'dateFormat'=>'',
					'datelistMkt'=>'',
					'datelist'=>'',
					'datelistUl'=>'',
					'time'=>'',
					'address'=>'',
					'name'=>'',
					'email'=>'',
					'contact_name'=>'',
					'contact_email'=>'',
					'emailLink'=>'',
					'phone'=>'',
					'website'=>'',
					'websiteLink'=>'',
					'targetLink'=>'',
					'place_name'=>'',
					'place_desc'=>'',
					'city'=>'',
					'country'=>'',
					'coordinate'=>'',
					'lat'=>'',
					'lng'=>'',
					'map'=>'',
					'share'=>'',
					'share_event'=>'',
					'limitRegEmail'=>'',
					'limitRegDate'=>'',

					'gcalendarUrl'=>'',
					'yahoocalendarUrl'=>'',
					'wlivecalendarUrl'=>'',

					'registrations'=>'',

					'registered'=>'',
					'totalRegistered'=>'',
					'registeredUsers'=>'',
					'reg'=>'',
					'regUrl'=>'',
					'iCagendaRegForm'=>'',
					'typeReg'=>'',
					'regEmailUser'=>'',
					'emailUserSubjectPeriod'=>'',
					'emailUserBodyPeriod'=>'',
					'emailUserSubjectDate'=>'',
					'emailUserBodyDate'=>'',
					'language'=>'',
					'params'=>'',

					'gcalendarLink'=>'',
					'loadEventCustomFields'=>'',
					'features'=>'',

					'periodTest'=>'', // DEPRECATED
					'titleLink'=>'', // DEPRECATED
// REMOVED 3.5.3	'placeLeft'=>'',
				)
			)
		);

		return $this->getItems($structure);
	}

	/**
	 * Get Records.
	 *
	 * @return	object list.
	 * @since	3.3.8
	 */
	public function getRecords()
	{
		// Get the current user for authorisation checks
		$user	= JFactory::getUser();

		// Get Params for current view
		$app	= JFactory::getApplication();
		$params	= $app->getParams();

		// Select the required fields from the table.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('e.*')
			->from($db->qn('#__icagenda_events') . ' AS e');

		// Join over the language
		$query->select('l.title AS language_title')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = e.language');

		// Join over the users for the checked out user.
//		$query->select('uc.name AS editor');
//		$query->join('LEFT', '#__users AS uc ON uc.id=e.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = e.access');

		// Join the category
		$query->select('c.title AS category, c.color AS catcolor');
		$query->join('LEFT', '#__icagenda_category AS c ON c.id = e.catid');
		$query->where('c.state = 1');

		// Join over the users for the author.
//		$query->select('ua.name AS author_name, ua.username AS author_username')
//			->join('LEFT', '#__users AS ua ON ua.id = e.created_by');

		// Filter by published state
		$query->where('e.state = 1');

		// Event is approved
		$query->where('e.approval <> 1');

		// Filter by access level.
		$access_levels = implode(',', $user->getAuthorisedViewLevels());

		$query->where('e.access IN (' . $db->q($access_levels) . ')');
//			->where('c.access IN (' . $db->q($access_levels) . ')'); // To be added later, when access integrated to category

		// Filter by language
		$query->where('e.language in (' . $db->q(JFactory::getLanguage()->getTag()) . ',' . $db->q('*') . ')');

		// Filter by Features
		$query->where(icagendaEventsData::getFeaturesFilter());

		// Filter by dates
		$dates_filter	= $params->get('time', 1); // Default Current and Upcoming Events

		// Get today date and datetime based on Joomla Config Timezone.
		$datetime_today	= JHtml::date('now', 'Y-m-d H:i:s');
		$date_today		= JHtml::date('now', 'Y-m-d');

		if (!empty($dates_filter))
		{
			// COM_ICAGENDA_OPTION_TODAY_AND_UPCOMING
			if ($dates_filter == '1')
			{
				$where_current_upcoming = $db->qn('e.next') . ' >= ' . $db->q($date_today);
				$where_current_upcoming.= ' OR (' . $db->qn('e.next') . ' < ' . $db->q($datetime_today) . ' AND ' . $db->qn('e.startdate') . ' <> "0000-00-00 00:00:00" AND ' . $db->qn('e.enddate') . ' > ' . $db->q($datetime_today) . ')';

				$query->where($where_current_upcoming);
			}

			// COM_ICAGENDA_OPTION_PAST
			elseif ($dates_filter == '2')
			{
				$where_past = '(';

				// Period dates with no weekdays filter
				$where_past.= $db->qn('e.next') . ' < ' . $db->q($datetime_today) . ')';
				$where_past.= ' AND (' . $db->qn('e.enddate') . ' < ' . $db->q($datetime_today);

				$where_past.= ' )';

				$query->where($where_past);
			}

			// COM_ICAGENDA_OPTION_FUTURE
			elseif ($dates_filter == '3')
			{
				$where_upcoming = '(';
				$where_upcoming.= $db->qn('e.next') . ' > ' . $db->q($datetime_today);
				$where_upcoming.= ' )';

				$query->where($where_upcoming);
			}

			// COM_ICAGENDA_OPTION_TODAY
			elseif ($dates_filter == '4')
			{
				$where_today = '( ';

				// One day dates filter
				$where_today.= ' (';
				$where_today.= ' (' . $db->qn('e.next') . ' >= ' . $db->q($datetime_today) . ')';
				$where_today.= ' AND (' . $db->qn('e.next') . ' < ' . $db->q($date_today) . ' + INTERVAL 1 DAY)';
				$where_today.= ' )';

				// Period dates with no weekdays filter
				$where_today.= ' OR ( ';
				$where_today.= ' (' . $db->qn('e.next') . ' > ' . $db->q($date_today) . ')';
				$where_today.= ' AND (' . $db->qn('e.weekdays') . ' = "")';
				$where_today.= ' AND ' . $db->qn('e.enddate') . ' <> "0000-00-00 00:00:00" AND (' . $db->qn('e.enddate') . ' >= ' . $db->q($date_today) . ')';
				$where_today.= ' AND ' . $db->qn('e.startdate') . ' <> "0000-00-00 00:00:00" AND (' . $db->qn('e.startdate') . ' < ' . $db->q($date_today) . ')';
				$where_today.= ' )';

				$where_today.= ' )';

				$query->where($where_today);
			}
		}

		// Order Next Date DESC
		$orderby	= $params->get('orderby', 2); // Default ASC
		$ordering	= ($orderby == 1) ? 'DESC' : 'ASC';

		$query->order('e.next ' . $ordering);

		// Tell the database connector what query to run.
		$db->setQuery($query);

		// Invoke the query or data retrieval helper.
		$db_list = $db->loadObjectList();

		return $db_list;
	}


   /**
    * Load Google Maps Scripts.
    *
    * @since	3.5.0
    */
	public static function loadGMapScripts()
	{
		// Google Maps api V3
		$document				= JFactory::getDocument();
		$scripts				= array_keys($document->_scripts);
		$mapsgooglescriptFound	= false;

		for ($i = 0; $i < count($scripts); $i++)
		{
    		if ( stripos($scripts[$i], 'maps.googleapis.com') !== false
    			&& stripos($scripts[$i], 'maps.gstatic.com') !== false )
			{
				$mapsgooglescriptFound = true;
			}
		}

		$doclang	= JFactory::getDocument();
		$curlang	= $doclang->language;
		$lang		= substr($curlang, 0, 2);

		if (!$mapsgooglescriptFound)
		{
			$document->addScript('https://maps.googleapis.com/maps/api/js?sensor=false&librairies=places&language=' . $lang);
		}

		$document->addScript( JURI::base( true ) . '/components/com_icagenda/js/icmap.js' );
	}


   /**
    * Get the return URL.
    *
    * @return	string   The return URL.
    * @since	1.0
    */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}
}
