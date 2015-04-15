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
 * @version     3.3.0 2014-02-27
 * @since       2.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.application.component.modeladmin');
jimport( 'joomla.mail.mail' );


/**
 * iCagenda model.
 */
class iCagendaModelmail extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_ICAGENDA';


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
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
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$datamail = array('control' => 'jform', 'load_data' => $loadData);
		$form = $this->loadForm('com_icagenda.mail', 'mail', $datamail);
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		if(version_compare(JVERSION, '3.0', 'lt')) {
			$data = JFactory::getApplication()->getUserState('com_icagenda.edit.event.data', array());
			if (empty($data)) {
				$data = $this->getItem();
			}

		} else {
			$data = JFactory::getApplication()->getUserState('com_icagenda.edit.mail.data', array());
			$this->preprocessData('com_icagenda.mail', $data);
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {

			//Do any procesing on fields here if needed

		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable( $table )
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__icagenda_events');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
	}

	/**
	 * Override preprocessForm to load the user plugin group instead of content.
	 *
	 * @param   object	A form object.
	 * @param   mixed	The data expected for the form.
	 * @throws	Exception if there is an error in the form event.
	 * @since   1.6
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'user')
	{
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Send mail
	 *
	 */


	function getMail(){

		$app    = JFactory::getApplication();
		$data   = $app->input->post->get('jform', array(), 'array');
		$user   = JFactory::getUser();
		$access = new JAccess;
		$db     = $this->getDbo();

		$mailer = JFactory::getMailer();
		$config = JFactory::getConfig();

		$send='';
		if(version_compare(JVERSION, '3.0', 'lt'))
		{
			$sender = array(
		    	$config->getValue( 'config.mailfrom' ),
		    	$config->getValue( 'config.fromname' ));
		}
		else
		{
			$sender = array(
		    	$app->getCfg( 'mailfrom' ),
		    	$app->getCfg( 'fromname' ));
		}

		$mailer->setSender($sender);

		$list = array_key_exists('list', $data) ? $data['list'] : '';
		$subject = array_key_exists('subject', $data) ? $data['subject'] : '';
		$messageget = array_key_exists('message', $data) ? $data['message'] : '';

		$recipient = explode(', ', $list);
		$obj   = $subject;
		$message   = $messageget;


		$recipient = array_filter($recipient);
//		$mailer->addRecipient($recipient);
//		$mailer->addRecipient($sender);
		$mailer->addBCC($recipient);

		$content = stripcslashes($message);
		$body=str_replace('src="images/', 'src="' . JURI::root() . '/images/', $content);
//		$mailer->setSender(array( $mailfrom, $fromname ));
		$mailer->setSubject($obj);
		$mailer->isHTML(true);
		$mailer->Encoding = 'base64';
		$mailer->setBody($body);

		if ( $obj == "" ) {
			echo '<div><b>'.JText::_('COM_ICAGENDA_NEWSLETTER_NO_OBJ_ALERT').'</b></div>';
			echo '<script type="text/javascript">';
			echo '	alert("'.JText::_('COM_ICAGENDA_NEWSLETTER_NO_OBJ_ALERT', true).'");';
			echo '</script>';
		}
		if ( $body == "" ) {
			echo '<div><b>'.JText::_('COM_ICAGENDA_NEWSLETTER_NO_BODY_ALERT').'</b></div>';
			echo '<script type="text/javascript">';
			echo '	alert("'.JText::_('COM_ICAGENDA_NEWSLETTER_NO_BODY_ALERT', true).'");';
			echo '</script>';
		}
		if (( $obj != "" ) && ( $body != "" )){
			$send = $mailer->Send();
		}
		if ( $send !== true ) {
//		    echo 'Error in sending the e-mail: ' . $send->message;
		    echo '<div>'.JText::_('COM_ICAGENDA_NEWSLETTER_ERROR_ALERT').'</div>';
		} else {
		    echo '<div><h2>'.JText::_('COM_ICAGENDA_NEWSLETTER_SUCCESS').'<h2></div>';
			function listArray($recipient, $level = 0) {
				foreach($recipient AS $key => $value) {
					if (is_array($value) | is_object($value)) listArray($value, $level+=1);
					else {
						$number = ($key + 1);
						echo str_repeat("&nbsp;", $level*3);
						echo $number." : ".$value."<br>";
					}
				}
		    	echo '<div>&nbsp;</div>';
				echo '<i>'.JText::_('COM_ICAGENDA_NEWSLETTER_NB_EMAIL_SEND').' = '.$number.'</i>';
			}
			  listArray($recipient);
//			echo '<pre>'.print_r($recipient, true).'</pre>';
		}
	}

}
