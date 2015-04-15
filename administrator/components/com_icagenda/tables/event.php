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
 * @version     3.4.1 2015-01-25
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

/**
 * event Table class
 */
class iCagendaTableevent extends JTable
{
	/**
	 * @var string $features  Property for the comma separated list of feature IDs. This needs to be specified because
	 *                        there is no column for features in the events table
	 */
	protected $features = null;

	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$_db)
	{
		parent::__construct('#__icagenda_events', 'id', $_db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param	array		Named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.3
	 */
	public function bind($array, $ignore = '')
	{
		// Serialize Single Dates
		$dev_option = '0';

		// Set Vars
		$eventTimeZone	= null;
		$nodate			= '0000-00-00 00:00:00';
		$date_today		= JHtml::date('now', 'Y-m-d'); // Joomla Time Zone

        if (iCString::isSerialized($array['dates']))
		{
			$dates = unserialize($array['dates']);
		}
		elseif ($dev_option == '1') // DEV.
		{
			$dates = $this->setDatesOptions($array['dates']);
		}
		else
		{
			$dates = $this->getDates($array['dates']);
		}

		$dates = ($dates == array('')) ? array('0000-00-00 00:00') : $dates;

		rsort($dates);

		if ($dev_option == '1') // DEV.
		{
			$array['dates']	= $array['dates'];
		}
		else
		{
			$array['dates']	= serialize($dates);
		}

		/**
		 * Set Week Days
		 */
		if (!isset($array['weekdays']))
		{
			$array['weekdays'] = '';
		}
		elseif (is_array($array['weekdays']))
		{
			$array['weekdays'] = implode(',', $array['weekdays']);
		}

		// Return the dates of the period.
		$startdate	= ($array['startdate'] == NULL) ? $nodate : $array['startdate'];
		$enddate	= ($array['enddate'] == NULL) ? $nodate : $array['enddate'];

		if (($startdate == $nodate) && ($enddate != $nodate))
		{
			$enddate = $nodate;
		}

		if (strtotime($startdate) > strtotime($enddate))
		{
			$errorperiod = '1';
		}
		else
		{
			$errorperiod = '';

			$period_all_dates_array	= iCDatePeriod::listDates($startdate, $enddate, $eventTimeZone);
			$WeeksDays				= iCDatePeriod::weekdaysToArray($array['weekdays']);

			$period_array = array();

			foreach ($period_all_dates_array AS $date_in_weekdays)
			{
				$datetime_period_date = JHtml::date($date_in_weekdays, 'Y-m-d H:i', $eventTimeZone);

				if (in_array(date('w', strtotime($datetime_period_date)), $WeeksDays))
				{
					array_push($period_array, $datetime_period_date);
				}
			}
		}

		// Serialize Period Dates
		if (($startdate != $nodate) && ($enddate != $nodate))
		{
			if ($errorperiod != '1')
			{
				$array['period'] = serialize($period_array);

				if (iCString::isSerialized($array['period']))
				{
					$period = unserialize($array['period']);
				}
				else
				{
					$period = $this->getPeriod($array['period']);
				}

				rsort($period);

				$array['period'] = serialize($period);
			}
			else
			{
				$array['period'] = '';
			}
		}
		else
		{
			$array['period'] = '';
		}

		// Set Next Date
		$NextDates	= $this->getNextDates($dates);
		$NextPeriod	= isset($period)
					? $this->getNextPeriod($period, $array['weekdays'])
					: $this->getNextDates($dates);

		$date_NextDates		= JHtml::date($NextDates, 'Y-m-d', $eventTimeZone);
		$date_NextPeriod	= JHtml::date($NextPeriod, 'Y-m-d', $eventTimeZone);
		$time_NextDates		= JHtml::date($NextDates, 'H:i', $eventTimeZone);
		$time_NextPeriod	= JHtml::date($NextPeriod, 'H:i', $eventTimeZone);

		// Control the next date
		if ((strtotime($date_NextDates) >= strtotime($date_today)) && (strtotime($date_NextPeriod) >= strtotime($date_today)))
		{
			if (strtotime($date_NextDates) < strtotime($date_NextPeriod))
			{
				$array['next'] = $this->getNextDates($dates);
			}
			if (strtotime($date_NextDates) > strtotime($date_NextPeriod))
			{
				$array['next'] = $this->getNextPeriod($period, $array['weekdays']);
			}
			if (strtotime($date_NextDates) == strtotime($date_NextPeriod))
			{
				if (strtotime($time_NextDates) >= strtotime($time_NextPeriod))
				{
					if (isset($period))
					{
						$array['next'] = $this->getNextPeriod($period, $array['weekdays']);
					}
					else
					{
						$array['next'] = $this->getNextDates($dates);
					}
				}
				else
				{
					$array['next'] = $this->getNextDates($dates);
				}
			}
		}
		elseif ((strtotime($date_NextDates) < strtotime($date_today)) && (strtotime($date_NextPeriod) >= strtotime($date_today)))
		{
			$array['next'] = $this->getNextPeriod($period, $array['weekdays']);
		}
		elseif ((strtotime($date_NextDates) >= strtotime($date_today)) && (strtotime($date_NextPeriod) < strtotime($date_today)))
		{
			$array['next'] = $this->getNextDates($dates);
		}
		elseif ((strtotime($date_NextDates) < strtotime($date_today)) && (strtotime($date_NextPeriod) < strtotime($date_today)))
		{
			if (strtotime($date_NextDates) < strtotime($date_NextPeriod))
			{
				$array['next'] = $this->getNextPeriod($period, $array['weekdays']);
			}
			else
			{
				$array['next'] = $this->getNextDates($dates);
			}
		}

		// Control of dates if valid (EDIT SINCE VERSION 3.0 - update 3.1.4)
		if (((strtotime($NextDates) >= '943916400')
			&& (strtotime($NextDates) <= '944002800'))
			&& ($errorperiod == '1'))
		{
			$array['next'] = '-3600';
		}
		if (((strtotime($NextDates)=='943916400') || (strtotime($NextDates)=='943920000'))
			&& ((strtotime($NextPeriod)=='943916400') || (strtotime($NextPeriod)=='943920000')))
		{
			$array['next'] = '-3600';
		}

		if ($array['next'] == '-3600')
		{
			$state = 0;
			$this->_db->setQuery(
			'UPDATE `#__icagenda_events`' .
			' SET `state` = '.(int) $state .
			' WHERE `id` = '. (int) $array['id']
			);
			if(version_compare(JVERSION, '3.0', 'lt'))
			{
				$this->_db->query();
			}
			else
			{
				$this->_db->execute();
			}
		}

		// Set Creator infos
		$user = JFactory::getUser();
		$userId	= $user->get('id');

		if ($array['created_by'] == '0')
		{
			$array['created_by'] = (int)$userId;
		}

		// Save Custom Fields to database
		if ( isset($array['custom_fields'])
			&& is_array($array['custom_fields']) )
		{
			icagendaCustomfields::saveToData($array['custom_fields'], $array['id'], 2);
		}

		// Set Features
		$array['features'] = $this->getFeatures($array['id']);

		// Set Params
		if ( isset($array['params'])
			&& is_array($array['params']) )
		{
			// Convert the params field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($array['params']);
			$array['params'] = (string)$parameter;
		}

		// Set Username
		$username = $user->get('name');
		$array['username'] = $username;

		// Set File upload
		if (!isset($array['file']))
		{
			$file = JRequest::getVar('jform', null, 'files', 'array');
			$fileUrl = $this->upload($file);
			$array['file'] = $fileUrl;
		}

		// Set Meta data
		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string)$registry;
		}

		$return[] = parent::bind($array, $ignore);


		// ====================================
		// START : HACK FOR A FEW PRO USERS !!!
		// ====================================
		$mail_new_event = '0';
		$mail_new_event = JComponentHelper::getParams('com_icagenda')->get('mail_new_event', '0');

		if ($mail_new_event == 1)
		{
			$title = $array['title'];
			$id_event = $array['id'];
			$db = JFactory::getDbo();
			$query	= $db->getQuery(true);
			$query->select('id AS eventID')
					->from('#__icagenda_events')
//					->where(" id = '$id_event'")
					->order('id DESC');
			$db->setQuery($query);
			$eventID = $db->loadResult();

			$new_event = JRequest::getVar('new_event');

			$title = $array['title'];
			$description = $array['desc'];
			$venue = '';
			if ($array['place']) $venue.= $array['place'].' - ';
			if ($array['city']) $venue.= $array['city'];
			if ($array['city'] && $array['country']) $venue.= ', ';
			if ($array['country']) $venue.= $array['country'];

			if (strtotime($array['startdate']))
			{
				$date = 'Du '.$array['startdate'].' au '.$array['startdate'];
			}
			else
			{
				$date = $array['next'];
			}

			$baseURL = JURI::base();
			$baseURL = str_replace('/administrator', '', $baseURL);
			$baseURL = ltrim($baseURL, '/');

			if ($array['image']) $image = '<img src="'.$baseURL.'/'.$array['image'].'" />';


			if ($new_event == '1' && $eventID && $array['state'] == '1' && $array['approval'] == '0')
			{
					$return[] = self::notificationNewEvent(($eventID+1), $title, $description, $venue, $date, $image, $new_event);
			}
//			elseif ($new_event == '0')
//			{
//					$return[] = self::notificationNewEvent($array['id'], 'Edited Event', $new_event);
//			}
		}
		// ====================================
		// END : HACK FOR A FEW PRO USERS !!!
		// ====================================

		return $return;

	}

	/**
	 * Extracts the list of Feature IDs linked to the event and returns an array
	 *
	 * @param	integer  $event_id
	 *
	 * @return	array/integer  Set of Feature IDs
	 * @since	3.4.0
	 */
	protected function getFeatures($event_id)
	{
		// Write any new feature records to the icagenda_feature_xref table
		if (empty($event_id))
		{
			return '';
		}
		else
		{
			$db = JFactory::getDbo();

			// Get a comma separated list of the ids of features present for this event
			// Note: Direct extraction of a comma separated list is avoided because each db type uses proprietary syntax
			$query = $db->getQuery(true)
				->select('fx.feature_id')
				->from('#__icagenda_events AS e')
				->innerJoin('#__icagenda_feature_xref AS fx ON e.id=fx.event_id')
				->innerJoin('#__icagenda_feature AS f ON fx.feature_id=f.id AND f.state=1')
				->where("e.id=$event_id");//$dump=$db->replacePrefix((string)$query);dump($dump,'sql query');
			$db->setQuery($query);
			$features = $db->loadColumn(0);

			// return $features;
			// Return a comma separated list
			return implode(',', $features);
		}
	}

	function setDatesOptions($dates) // DEV.
	{
		$dates	= str_replace('day=', '', $dates);
		$dates	= str_replace('start=', '', $dates);
		$dates	= str_replace('end=', '', $dates);
//		$dates	= str_replace('+', ' ', $dates);
		$dates	= str_replace('%3A', ':', $dates);
		$dates	= str_replace('&', ',', $dates);

		$ex_dates = explode(',stop=stop', $dates);

		$singles_dates = array();

		foreach ($ex_dates AS $sd)
		{
			if ($sd != '')
			{
				array_push($singles_dates, $sd);
			}
		}

		return $singles_dates;
	}

	function getDates($dates)
	{
		$dates		= str_replace('d=', '', $dates);
		$dates		= str_replace('+', ' ', $dates);
		$dates		= str_replace('%3A', ':', $dates);
		$ex_dates	= explode('&', $dates);

		return $ex_dates;
	}

	function getPeriod($period)
	{
		$period		= str_replace('d=', '', $period);
		$period		= str_replace('+', ' ', $period);
		$period		= str_replace('%3A', ':', $period);
		$ex_period	= explode('&', $period);

		return $ex_period;
	}


	function getNextDates($dates)
	{
		// Set Vars
		$eventTimeZone	= null;
		$date_today		= JHtml::date('now', 'Y-m-d'); // Joomla Time Zone

		// Get Next
		$next			= JRequest::getVar('next');

		if (count($dates))
		{
			while (strtotime($next) <= strtotime($date_today))
			{
				$nextDate = $dates[0];

				foreach ($dates as $d)
				{
					if (strtotime($d) >= strtotime($date_today))
					{
						$nextDate = $d;
					}
				}

				return JHtml::date($nextDate, 'Y-m-d H:i', $eventTimeZone);
			}
		}
	}


	function getNextPeriod($period, $i_weekdays)
	{
		// Set Vars
		$eventTimeZone	= null;
		$date_today		= JHtml::date('now', 'Y-m-d'); // Joomla Time Zone

		$WeeksDays = iCDatePeriod::weekdaysToArray($i_weekdays);

		// Set Next Date for Period, if dates exist in Period
		if (count($period))
		{
			$nextPeriod	= $period[0];

			foreach ($period as $e)
			{
				if (in_array(date('w', strtotime($e)), $WeeksDays))
				{
					if (strtotime($e) >= strtotime($date_today)) // if datetime in period >= date today
					{
						$nextPeriod = $e;
					}
				}
			}

			return JHtml::date($nextPeriod, 'Y-m-d H:i', $eventTimeZone);
		}
	}

	/**
	 * upload
	 */

	function upload($file)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$filename = JFile::makeSafe($file['name']['file']);

		// Get media path
		$params_media	= JComponentHelper::getParams('com_media');
		$image_path		= $params_media->get('image_path', 'images');

		// Paths to thumbs folder
		$thumbsPath		= $image_path . '/icagenda/thumbs';

		if ($filename!='')
		{
			$src = $file['tmp_name']['file'];
			$dest =  JPATH_SITE . '/' . $image_path . '/icagenda/files/' . $filename;

			if(!is_dir($dest))
			{
				mkdir($intDir, 0755);
			}

			if ( JFile::upload($src, $dest, false) )
			{
				echo 'upload';
				return $image_path . '/icagenda/files/' . $filename;
			}

			return $image_path . '/icagenda/files/' . $filename;
		}
	}

	/**
	* Overloaded check function
	*/
	public function check()
	{
		// Import Joomla 2.5
//		jimport( 'joomla.filter.output' );

		//If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0)
		{
			$this->ordering = self::getNextOrder();
		}

		return parent::check();
	}


	/**
	* Method to set the publishing state for a row or list of rows in the database
	* table.  The method respects checked out rows by other users and will attempt
	* to checkin rows that it can after adjustments are made.
	*
	* @param	mixed	An optional array of primary key values to update.  If not
	*					set the instance property value is used.
	* @param    integer The publishing state. eg. [0 = unpublished, 1 = published]
	* @param    integer The user id of the user performing the operation.
	* @return    boolean    True on success.
	* @since    1.0.4
	*/
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k.'='.implode(' OR '.$k.'=', $pks);

		// Determine if there is checkin support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = '.(int) $userId.')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `state` = '.(int) $state .
			' WHERE ('.$where.')' .
			$checkin
		);
// J2.5 :
		$this->_db->query();

// J3
//		$this->_db->setQuery($query);
//		$this->_db->execute();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		$this->setError('');

		return true;
	}


		/**
		* Overloaded load function
		*
		* @param       int $pk primary key
		* @param       boolean $reset reset data
		* @return      boolean
		* @see JTable:load
		*/
		public function load($pk = null, $reset = true)
		{
			if (parent::load($pk, $reset))
			{
			// Convert the params field to a registry.
				$params = new JRegistry;
				// loadJSON is @deprecated    12.1  Use loadString passing JSON as the format instead.
				// $params->loadString($this->item->params, 'JSON');
				// "item" should not be present.
				if(version_compare(JVERSION, '3.0', 'lt'))
				{
					$params->loadJSON($this->params);
				}
				else
				{
					$params->loadString($this->params);
				}
				$this->params = $params;
				return true;
			}
			else
			{
				return false;
			}
		}

	/**
	 * HACK FOR A FEW PRO USERS !!!
	 *
	 * Will be removed when creation of a notification plugin
	 *
	 */
	function notificationNewEvent ($eventid, $title, $description, $venue, $date, $image, $new_event)
	{
		// Load iCagenda Global Options
		$iCparams = JComponentHelper::getParams('com_icagenda');

		// Load Joomla Config
		$config = JFactory::getConfig();

		// Switch Joomla 3.x / 2.5
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
				// Get the site name
				$sitename = $config->get('sitename');

				// Get Global Joomla Contact Infos
				$mailfrom = $config->get('mailfrom');
				$fromname = $config->get('fromname');

				// Get default language
				$langdefault = $config->get('language');
		}
		else
		{
				// Get the site name
				$sitename = $config->getValue('config.sitename');

				// Get Global Joomla Contact Infos
				$mailfrom = $config->getValue('config.mailfrom');
				$fromname = $config->getValue('config.fromname');

				// Get default language
				$langdefault = $config->getValue('config.language');
		}

		$siteURL = JURI::base();
		$siteURL = rtrim($siteURL,'/');

		$iCmenuitem = false;

		// Itemid Request (automatic detection of the first iCagenda menu-link, by menuID, and depending of current language)

		$langFrontend = $langdefault;
		$db = JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('id AS idm')
				->from('#__menu')
				->where( "(link = 'index.php?option=com_icagenda&view=list') AND (published > 0) AND (language = '$langFrontend')" );
		$db->setQuery($query);
		$idm = $db->loadResult();
		$mItemid = $idm;

		if ($mItemid == NULL)
		{
				$db = JFactory::getDbo();
				$query	= $db->getQuery(true);
				$query->select('id AS noidm')
						->from('#__menu')
						->where( "(link = 'index.php?option=com_icagenda&view=list') AND (published > 0) AND (language = '*')" );
				$db->setQuery($query);
				$noidm = $db->loadResult();
		}

		$nolink = '';

		if ($noidm == NULL && $mItemid == NULL)
		{
				$nolink = 1;
		}

		if (is_numeric($iCmenuitem))
		{
				$lien = $iCmenuitem;
		}
		else
		{
			if ($mItemid == NULL)
			{
					$lien = $noidm;
			}
			else
			{
					$lien = $mItemid;
			}
		}

		// Set Notification Email to each User groups allowed to receive a notification email when a new event created
		$groupid = $iCparams->get('newevent_Groups', array("8"));

		// Load Global Option for Autologin
//		$autologin = $iCparams->get('auto_login', 1);

		jimport( 'joomla.access.access' );
		$newevent_Groups_Array = array();
		foreach ($groupid AS $gp) {
			$GroupUsers = JAccess::getUsersByGroup($gp, False);
			$newevent_Groups_Array = array_merge($newevent_Groups_Array, $GroupUsers);
		}

//		if ($u_id == NULL) {
//				$u_id = 0;
//		}

		$db = JFactory::getDbo();
		$query	= $db->getQuery(true);

//		if (!in_array($u_id, $newevent_Groups_Array)) {

//			$matches = implode(',', $adminUsersArray);
//			$query->select('ui.username AS username, ui.email AS email, ui.password AS passw, ui.block AS block, ui.activation AS activation')
//					->from('#__users AS ui')
//					->where( "ui.id IN ($matches) ");

//		} else {

//			$matches = $u_id;
			$matches = implode(',', $newevent_Groups_Array);
			$query->select('ui.username AS username, ui.email AS email, ui.password AS passw, ui.block AS block, ui.activation AS activation')
					->from('#__users AS ui')
					->where( "ui.id IN ($matches) ");
//					->where( "ui.id = $matches ");

//		}

		$db->setQuery($query);
		$users = $db->loadObjectList();

		// Get all users email and group except for senders
//		$db = JFactory::getDbo();
//		$query	= $db->getQuery(true)
//			->select('email')
//			->from('#__users');
//			->where('id != '.(int) $user->get('id'));
//		if ($grp !== 0)
//		{
//			if (empty($to))
//			{
//				$query->where('0');
//			} else {
//				$query->where('id IN (' . implode(',', $to) . ')');
//			}
//		}

//		if ($disabled == 0){
//			$query->where("block = 0");
//		}

//		$db->setQuery($query);
//		$rows = $db->loadColumn();

		foreach ($users AS $user)
		{
			// Create Notification Mailer
			$new_mailer = JFactory::getMailer();

			// Set Sender of Notification Email
			$new_mailer->setSender(array( $mailfrom, $fromname ));

        	$username = $user->username;
        	$passw = $user->passw;
        	$email = $user->email;

			// Set Recipient of Notification Email
			$new_recipient = $email;
			$new_mailer->addRecipient($email);

			// Set Subject of New Event Notification Email
//			$new_subject = JText::sprintf('COM_ICAGENDA_MAIL_NEW_EVENT_SUBJECT', $sitename);
			$new_subject = 'Nouvel évènement, '.$sitename;
			$new_mailer->setSubject($new_subject);



			// Set Url to preview new event
			$baseURL = JURI::base();
//			$subpathURL = JURI::base(true);

			$baseURL = str_replace('/administrator', '', $baseURL);
//			$subpathURL = str_replace('/administrator', '', $subpathURL);

			$urlpreview = str_replace('&amp;','&', JRoute::_($baseURL.'index.php?option=com_icagenda&view=list&layout=event&id='.(int)$eventid.'&Itemid='.(int)$lien));

			// Sub Path filtering
//			$subpathURL = ltrim($subpathURL, '/');

			// URL Event Preview filtering
//			$urlpreview = ltrim($urlpreview, '/');
//			if(substr($urlpreview,0,strlen($subpathURL)+1) == "$subpathURL/") $urlpreview = substr($urlpreview,strlen($subpathURL)+1);
//			$urlpreview = rtrim($baseURL,'/').'/'.ltrim($urlpreview,'/');


			/**
			 * Set Body of User Notification Email
			 */

			// Hello
//			$new_body_hello = JText::sprintf( 'COM_ICAGENDA_MAIL_NEW_EVENT_BODY_HELLO', $username);
			$new_body_hello = 'Bonjour,';
			$new_bodycontent = $new_body_hello.'<br /><br />';

			// Text
//			$new_body_text = JText::sprintf( 'COM_ICAGENDA_MAIL_NEW_EVENT_BODY_TEXT', $sitename);
			$new_body_text = $sitename.' vous propose un nouvel évènement :';
			$new_bodycontent.= $new_body_text.'<br /><br />';

			// Event Details
			$new_bodycontent.= $title ? 'Titre: '.$title.'<br />' : '';
			$new_bodycontent.= $description ? 'Description: '.$description.'<br />' : '';
			$new_bodycontent.= $venue ? 'Lieu: '.$venue.'<br />' : '';
			$new_bodycontent.= $date ? 'Date: '.$date.'<br /><br />' : '';
			$new_bodycontent.= $image.'<br /><br />';

			// Link to event details view
			$new_bodycontent.= '<a href="'.$urlpreview.'">'.$urlpreview.'</a><br /><br />';

			// Footer
			$new_body_footer = 'Do not answer to this e-mail notification as it is a generated e-mail. You are receiving this email message because you are registered at '.$sitename.'.';
			$new_bodycontent.= '<hr><small>'.$new_body_footer.'<small>';

			// Removes spaces (leading, ending) from Body
			$new_body = rtrim($new_bodycontent);

			// Authorizes HTML
			$new_mailer->isHTML(true);
			$new_mailer->Encoding = 'base64';

			// Set Body
			$new_mailer->setBody($new_body);

			// Send User Notification Email
			if (isset($email)) {
				if($user->block == '0' && empty($user->activation)){
					$send = $new_mailer->Send();
				}
			}
		}
	}
}
