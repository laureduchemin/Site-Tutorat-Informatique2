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
 * @version     3.5.3 2015-03-23
 * @since		2.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of iCagenda records.
 */
class iCagendaModelregistrations extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param	array			An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'userid', 'userid',
				'name', 'name',
				'username', 'username',
				'email', 'email',
				'phone', 'phone',
				'event', 'event',
				'date', 'a.date',
				'people', 'a.people',
				'notes', 'a.notes',
				'created_by', 'created_by'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter search.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Filter (dropdown) state.
		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Filter (dropdown) categories
		$categories = $this->getUserStateFromRequest($this->context.'.filter.categories', 'filter_categories', '', 'string');
		$this->setState('filter.categories', $categories);

		// Filter (dropdown) events
		$events = $this->getUserStateFromRequest($this->context.'.filter.events', 'filter_events', '', 'string');
		$this->setState('filter.events', $events);

		// Filter (dropdown) dates
		$dates = $this->getUserStateFromRequest($this->context.'.filter.dates', 'filter_dates', '', 'string');
		$this->setState('filter.dates', $dates);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_icagenda');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'desc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__icagenda_registration` AS a');

		// Join over the events.
		$query->select('e.title AS event, e.created_by AS created_by, e.state AS evt_state');
		$query->join('LEFT', '#__icagenda_events AS e ON e.id=a.eventid');

		// Join over the categories.
		$query->select('c.id AS cat_id, c.title AS cat_title');
		$query->join('LEFT', '#__icagenda_category AS c ON c.id=e.catid');

		// Join over the users for the checked out user.
		$query->select('u.username AS username, u.name AS fullname');
		$query->join('LEFT', '#__users AS u ON u.id=a.userid');

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = '.(int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in content
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else
			{
				if(version_compare(JVERSION, '3.0', 'lt'))
				{
					$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				}
				else
				{
					$search = $db->Quote('%'.$db->escape($search, true).'%');
				}
				$query->where('(u.username LIKE '.$search.'  OR  a.name LIKE '.$search.'  OR  a.userid LIKE '.$search.'  OR  a.email LIKE '.$search.'  OR  a.phone LIKE '.$search.'  OR  a.date LIKE '.$search.'  OR  a.period LIKE '.$search.'  OR  a.people LIKE '.$search.'  OR  a.notes LIKE '.$search.'  OR  e.title LIKE '.$search.' )');
			}
		}

		// Filter categories
		$category = $db->escape($this->getState('filter.categories'));

		if (!empty($category))
		{
			$query->where('(c.id=' . $db->q($category) . ')');
		}

		// Filter events
		$event = $db->escape($this->getState('filter.events'));

		if (!empty($event))
		{
			$query->where('(a.eventid=' . $db->q($event) . ')');
		}

		// Filter dates
		$date = $db->escape($this->getState('filter.dates'));

		if (!empty($date) && ! in_array($date, array('1', '2')))
		{
			$query->where($db->qn('a.date') . ' = ' . $db->q($date));
		}
		elseif ($date == 1)
		{
			$query->where($db->qn('a.date') . ' = ""');
			$query->where($db->qn('a.period') . ' = "0"');
		}
		elseif ($date == 2)
		{
			$query->where($db->qn('a.date') . ' = ""');
			$query->where($db->qn('a.period') . ' = "1"');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			if(version_compare(JVERSION, '3.0', 'lt'))
			{
				$query->order($db->getEscaped($orderCol.' '.$orderDirn));
			}
			else
			{
				$query->order($db->escape($orderCol.' '.$orderDirn));
			}
		}

		return $query;
	}

	/**
	 * Gets a list of categories.
	 */
	function getCategories()
	{
		// Create a new query object.
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('c.id AS cat_id, c.title AS cat_title');
		$query->from('`#__icagenda_category` AS c');

		// Join over the events.
		$query->select('e.id AS id');
		$query->join('LEFT', '#__icagenda_events AS e ON e.catid=c.id');

		// Join over the registrations.
		$query->select('r.eventid AS event_id');
		$query->join('LEFT', '#__icagenda_registration AS r ON r.eventid=e.id');
		$query->where('(e.id = r.eventid)');
		$query->order('c.ordering ASC');

		$db->setQuery($query);
		$categories = $db->loadObjectList();

		$list = array();

		foreach ($categories as $c)
		{
			$list[$c->cat_id] = $c->cat_title . ' [' . $c->cat_id . ']';
		}

		return $list;
	}

	/**
	 * Gets a list of all events.
	 */
	function getEvents()
	{
		// Create a new query object.
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('e.id AS event, e.title AS title');
		$query->from('`#__icagenda_events` AS e');

		// Join over the categories.
		$query->select('c.id AS cat_id, c.title AS cat_title');
		$query->join('LEFT', '#__icagenda_category AS c ON c.id=e.catid');

		// Join over the registrations.
		$query->select('r.eventid AS eventid');
		$query->join('LEFT', '#__icagenda_registration AS r ON r.eventid=e.id');
		$query->where('(e.id = r.eventid)');
		$query->order('e.title ASC');

		// Filter by published state
//		$query->where('(e.state IN (0, 1))');

		$db->setQuery($query);
		$events = $db->loadObjectList();

		$list = array();

		$catId = $db->escape($this->getState('filter.categories'));

		foreach ($events as $e)
		{
			if ( ! empty($catId) && $catId == $e->cat_id)
			{
				$list[$e->event] = $e->title . ' [' . $e->event . ']';
			}
			elseif (empty($catId))
			{
				$list[$e->event] = $e->title . ' [' . $e->event . ']';
			}
//			$list[$e->event] = $e->title . ' [' . $e->event . ']';
		}

		return $list;
	}

	/**
	 * Gets a list of dates.
	 */
	function getDates()
	{
		// Create a new query object.
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('r.date AS date, r.period AS period, r.eventid AS eventid');
		$query->from('`#__icagenda_registration` AS r');

		$db->setQuery($query);
		$dates = $db->loadObjectList();

		$list = array();

		$eventId = $db->escape($this->getState('filter.events'));

		$p = $e = 0;

		// Add to select dropdown the filters 'For all dates of the event' and/or 'For all the period',
		// depending of registrations in data, and selected event
		foreach ($dates as $d)
		{
			$date	= (empty($d->date) && $d->period == 0)
					? '[ ' . ucfirst(JText::_('COM_ICAGENDA_ADMIN_REGISTRATION_FOR_ALL_PERIOD')) . ' ]'
					: '';
			$period	= (empty($d->date) && $d->period == 1)
					? '[ ' . ucfirst(JText::_('COM_ICAGENDA_ADMIN_REGISTRATION_FOR_ALL_DATES')) . ' ]'
					: '';

			if (empty($d->date)
				&& $d->period == 0
				&& $p == 0
				)
			{
				if ( ! empty($eventId) && $eventId == $d->eventid)
				{
					$p = $p+1;
					$list[1] = $date;
				}
				elseif (empty($eventId))
				{
					$p = $p+1;
					$list[1] = $date;
				}
			}

			if (empty($d->date)
				&& $d->period == 1
				&& $e == 0
				)
			{
				if ( ! empty($eventId) && $eventId == $d->eventid)
				{
					$e = $e+1;
					$list[2] = $period;
				}
				elseif (empty($eventId))
				{
					$e = $e+1;
					$list[2] = $period;
				}
			}
		}

		// Add to select dropdown the list of dates,
		// depending of registrations in data, and selected event
		foreach ($dates as $d)
		{
			$date	= iCDate::isDate($d->date)
					? JHtml::date($d->date, JText::_('DATE_FORMAT_LC3'), null) . ' - ' . date('H:i', strtotime($d->date))
					: $d->date;

			$display_date = ($date != '0000-00-00 00:00:00' && $d->date) ? true : false;

			if ($display_date
				&& ! empty($eventId)
				&& $eventId == $d->eventid
				)
			{
				$list[$d->date] = $date;
			}
			elseif ($display_date
				&& empty($eventId)
				)
			{
				$list[$d->date] = $date;
			}
		}

		return $list;
	}
	/**
	 * Get file name
	 *
	 * @return  string    The file name
	 *
	 * @since   1.6
	 */
	public function getBaseName()
	{
		if (!isset($this->basename))
		{
			$app = JFactory::getApplication();
			$basename = $this->getState('basename');
			$basename = str_replace('__SITE__', $app->getCfg('sitename'), $basename);

			$eventId = $this->getState('filter.events');

			if (is_numeric($eventId))
			{
				$basename = str_replace('__EVENTID__', $eventId, $basename);
				$basename = str_replace('__EVENT__', $this->getEventTitle($eventId), $basename);
			}
			else
			{
				$basename = str_replace('__EVENTID__', '', $basename);
				$basename = str_replace('__EVENT__', '', $basename);
			}

			$date = $this->getState('filter.dates');

			if (!empty($date))
			{
				if (iCDate::isDate($date))
				{
					$basename = str_replace('__DATE__', JHtml::date($date, JText::_('DATE_FORMAT_LC3'), null)
											. ' - ' . date('H:i', strtotime($date)),
											$basename);
				}
				else
				{
					$basename = str_replace('__DATE__', $date, $basename);
				}
			}
			else
			{
				$basename = str_replace('__DATE__', '', $basename);
			}

			$this->basename = $basename;
		}

		return $this->basename;
	}

	/**
	 * Get the event title.
	 *
	 * @return  string    The event title
	 *
	 * @since   3.5.0
	 */
	protected function getEventTitle()
	{
		$eventId = $this->getState('filter.events');

		if ($eventId)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->select('title')
				->from($db->quoteName('#__icagenda_events'))
				->where($db->quoteName('id') . '=' . $db->quote($eventId));
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}
		else
		{
			$title = JText::_('COM_ICAGENDA_NO_EVENT_TITLE');
		}

		return $title;
	}

	/**
	 * Get the status name.
	 *
	 * @return  string    The status name
	 *
	 * @since   3.5.0
	 */
	protected function getStatusName($status)
	{
		$status_array = JHtml::_('jgrid.publishedOptions');

		foreach ($status_array AS $key => $name)
		{
			if ($status == $name->value)
			{
				$status_name = $name->text;
			}
		}

		return JText::_($status_name);
	}

	/**
	 * Get the file type.
	 *
	 * @return  string    The file type
	 *
	 * @since   3.5.0
	 */
	public function getFileType()
	{
		return $this->getState('compressed') ? 'zip' : 'csv';
	}

	/**
	 * Get the mime type.
	 *
	 * @return  string    The mime type.
	 *
	 * @since   3.5.0
	 */
	public function getMimeType()
	{
		return $this->getState('compressed') ? 'application/zip' : 'text/csv';
	}

	/**
	 * Get the content
	 *
	 * @return  string    The content.
	 *
	 * @since   3.5.0
	 */
	public function getContent()
	{
		if (!isset($this->content))
		{
			foreach ($this->getItems() as $item)
			{
				// Adds filled custom fields
				$customfields = icagendaCustomfields::getList($item->id, 1);

 				$header_cfs	= array();

				if ($customfields)
				{
					foreach ($customfields AS $customfield)
					{
						$header_cfs[]= $customfield->cf_title;
					}
				}
			}

			// Add BOM UTF-8 to csv content
			$this->content	= chr(239) . chr(187) . chr(191);

			$this->content .= '';

			$this->content .=
				'"' .
				str_replace('"', '""', JText::_('COM_ICAGENDA_REGISTRATION_EVENTID')) . '","' .
				str_replace('"', '""', JText::_('COM_ICAGENDA_REGISTRATION_DATE')) . '","' .
				str_replace('"', '""', JText::_('COM_ICAGENDA_REGISTRATION_TICKETS')) . '","' .
				str_replace('"', '""', JText::_('IC_NAME')) . '","' .
				str_replace('"', '""', JText::_('COM_ICAGENDA_REGISTRATION_EMAIL')) . '","' .
				str_replace('"', '""', JText::_('COM_ICAGENDA_REGISTRATION_PHONE')) . '","';

			foreach ($header_cfs AS $header)
			{
				$this->content .= str_replace('"', '""', $header) . '","';
			}

			$this->content .=
				str_replace('"', '""', JText::_('COM_ICAGENDA_REGISTRATION_NOTES_DISPLAY_LABEL')) . '","' .
				str_replace('"', '""', JText::_('JSTATUS')) . '"' .
				"\n";

			foreach ($this->getItems() as $item)
			{
				// Adds filled custom fields
				$customfields = icagendaCustomfields::getList($item->id, 1);

 				$values_cfs	= array();

				if ($customfields)
				{
					foreach ($customfields AS $customfield)
					{
						$cf_value = isset($customfield->cf_value) ? $customfield->cf_value : JText::_('IC_NOT_SPECIFIED');
						$values_cfs[]= $cf_value;
					}
				}

				$this->content .=
					'"' .
					str_replace('"', '""', $item->event) . '","' .
					str_replace('"', '""', ($item->period == 1 ? JText::_('COM_ICAGENDA_REGISTRATION_ALL_DATES') : $item->date)) . '","' .
					str_replace('"', '""', $item->people) . '","' .
					str_replace('"', '""', $item->name) . '","' .
					str_replace('"', '""', $item->email) . '","' .
					str_replace('"', '""', $item->phone) . '","';

				foreach ($values_cfs AS $value)
				{
					$this->content .= str_replace('"', '""', $value) . '","';
				}

				$this->content .=
					str_replace('"', '""', $item->notes) . '","' .
					str_replace('"', '""', $this->getStatusName($item->state)) . '"' .
					"\n";
			}

			if ($this->getState('compressed'))
			{
				$app = JFactory::getApplication('administrator');

				$files = array();
				$files['registrations'] = array();
				$files['registrations']['name'] = $this->getBasename() . '.csv';
				$files['registrations']['data'] = $this->content;
				$files['registrations']['time'] = time();
				$ziproot = $app->get('tmp_path') . '/' . uniqid('icagenda_registrations_') . '.zip';

				// Run the packager
				jimport('joomla.filesystem.folder');
				jimport('joomla.filesystem.file');
				$delete = JFolder::files($app->get('tmp_path') . '/', uniqid('icagenda_registrations_'), false, true);

				if (!empty($delete))
				{
					if (!JFile::delete($delete))
					{
						// JFile::delete throws an error
						$this->setError(JText::_('COM_ICAGENDA_EXPORT_ERR_ZIP_DELETE_FAILURE'));

						return false;
					}
				}

				if (!$packager = JArchive::getAdapter('zip'))
				{
					$this->setError(JText::_('COM_ICAGENDA_EXPORT_ERR_ZIP_ADAPTER_FAILURE'));

					return false;
				}
				elseif (!$packager->create($ziproot, $files))
				{
					$this->setError(JText::_('COM_ICAGENDA_EXPORT_ERR_ZIP_CREATE_FAILURE'));

					return false;
				}

				$this->content = file_get_contents($ziproot);
			}
		}

		return $this->content;
	}
}
