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
 * @version     3.5.1 2015-02-28
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.application.component.modeladmin');


/**
 * iCagenda model.
 */
class iCagendaModelEvent extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.0
	 */
	protected $text_prefix = 'COM_ICAGENDA';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.0
	 */
	public function getTable($type = 'Event', $prefix = 'iCagendaTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_icagenda.event', 'event',
								array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data_array = JFactory::getApplication()->getUserState('com_icagenda.edit.event.data', array());

		if (empty($data_array))
		{
			$data = $this->getItem();
		}
		else
		{
			$data = new JObject;
			$data->setProperties($data_array);
		}

		// If not array, creates array with week days data
		if (!is_array($data->weekdays))
		{
			$data->weekdays = explode(',', $data->weekdays);
		}

		// Retrieves data, to display selected week days
		$arrayWeekDays = $data->weekdays;
		foreach ($arrayWeekDays as $allTest)
		{
			if ($allTest == '')
			{
				$data->weekdays = '0,1,2,3,4,5,6';
			}
		}

		// Convert features into an array so that the form control can be set
		if (!isset($data->features))
		{
			$data->features = array();
		}

		if (!is_array($data->features))
		{
			$data->features = explode(',', $data->features);
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			//Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.0.0
	 */
	protected function prepareTable( $table )
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);

		if (empty($table->id))
		{
			// Set the values
			$table->created = $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__icagenda_events'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		else
		{
			// Set the values
			$table->modified = $date->toSql();
			$table->modified_by = $user->get('id');
		}
	}

	/**
	 * Approve Function.
	 *
	 * *** Not used ***
	 */
	function approve($cid, $publish)
	{
		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'UPDATE #__icagenda_events'
					. ' SET approval = '.(int) $publish
					. ' WHERE id IN ( '.$cids.' )';
					$this->_db->setQuery( $query );
			if (!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}

	/**
	 * Save form
	 * @Author	doorknob
	 *
	 * @since	3.4.0
	 */
	public function save($data)
	{
		$input = JFactory::getApplication()->input;
		$date = JFactory::getDate();

		// Fix version before 3.4.0 to set a created date (will use last modified date if exists, or current date)
		if (empty($data['created']))
		{
			$data['created'] = !empty($data['modified']) ? $data['modified'] : $date->toSql();
		}

		// Alter the title for save as copy
		if ($input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['title'] == $origTable->title)
			{
				list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
				$data['title'] = $title;
				$data['alias'] = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->alias)
				{
					$data['alias'] = '';
				}
			}
			$data['state'] = 0;
		}

		// Automatic handling of alias for empty fields
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (int) $input->get('id') == 0)
		{
			if ($data['alias'] == null)
			{
				if (JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$data['alias'] = JFilterOutput::stringURLUnicodeSlug($data['title']);
				}
				else
				{
					$data['alias'] = JFilterOutput::stringURLSafe($data['title']);
				}

				$table = JTable::getInstance('Event', 'iCagendaTable');

				if ($table->load(array('alias' => $data['alias'], 'catid' => $data['catid'])))
				{
					$msg = JText::_('COM_ICAGENDA_ALERT_EVENT_SAVE_WARNING');
				}

				list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
				$data['alias'] = $alias;

				if (isset($msg))
				{
					JFactory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		}

		// Generates Alias if empty
		if ($data['alias'] == null || empty($data['alias']))
		{
			$data['alias'] = JFilterOutput::stringURLSafe($data['title']);

			if ($data['alias'] == null || empty($data['alias']))
			{
				if (JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$data['alias'] = JFilterOutput::stringURLUnicodeSlug($data['title']);
				}
				else
				{
					$data['alias'] = JFilterOutput::stringURLSafe($data['created']);
				}
			}
		}

		$return = parent::save($data);

		if ($return === true)
		{
			$return = $this->maintainFeatures($data);
		}

		return $return;
	}

	/**
	 * Maintain features to data
	 * @Author	doorknob
	 *
	 * @since	3.4.0
	 */
	protected function maintainFeatures($data)
	{
		// Get the list of feature ids to be linked to the event
		$features = isset($data['features']) && is_array($data['features']) ? implode(',', $data['features']) : '';

		$db = JFactory::getDbo();

		// Write any new feature records to the icagenda_feature_xref table
		if (!empty($features))
		{
			// Get a list of the valid features already present for this event
			$query = $db->getQuery(true)
				->select('feature_id')
				->from($db->quoteName('#__icagenda_feature_xref'))
				->where("event_id={$data['id']}")
				->where("feature_id IN($features)");
			$db->setQuery($query);
			$existing_features = $db->loadColumn(0);

			// Identify the insert list
			if (empty($existing_features))
			{
				$new_features = $data['features'];
			}
			else
			{
				$new_features = array();

				foreach ($data['features'] as $feature)
				{
					if (!in_array($feature, $existing_features))
					{
						$new_features[] = $feature;
					}
				}
			}
			// Write the needed xref records
			if (!empty($new_features))
			{
				$xref = new JObject;
				$xref->set('event_id', $data['id']);

				foreach ($new_features as $feature)
				{
					$xref->set('feature_id', $feature);
					$db->insertObject('#__icagenda_feature_xref', $xref);
					$db->setQuery($query);

					if (!$db->execute())
					{
						return false;
					}
				}
			}
		}

		// Delete any unwanted feature records from the icagenda_feature_xref table
		$query = $db->getQuery(true)
			->delete('#__icagenda_feature_xref')
			->where("event_id={$data['id']}");

		if (!empty($features))
		{
			// Delete only unwanted features
			$query->where("feature_id NOT IN($features)");//$dump=$db->replacePrefix((string)$query);dump($dump,'sql query');
		}

		$db->setQuery($query);
		$db->execute($query);

		if (!$db->execute())
		{
			return false;
		}

		return true;
	}
}
