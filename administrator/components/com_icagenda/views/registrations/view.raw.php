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
 * @version     3.5.0 2015-02-05
 * @since       3.5.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

/**
 * View class for a list of registrations.
 *
 * @since	3.5.0
 */
class icagendaViewRegistrations extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$basename		= $this->get('BaseName');
		$filetype		= $this->get('FileType');
		$mimetype		= $this->get('MimeType');
		$content		= $this->get('Content');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$document = JFactory::getDocument();
		$document->setMimeEncoding($mimetype);

		// Joomla 3
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			JFactory::getApplication()
				->setHeader(
					'Content-disposition',
					'attachment; filename="' . $basename . '.' . $filetype . '"; creation-date="' . JFactory::getDate()->toRFC822() . '"',
					true
				);
		}
		// Joomla 2.5
		else
		{
			JResponse::setHeader('Content-disposition', 'attachment; filename="'.$basename.'.'.$filetype.'"; creation-date="'.JFactory::getDate()->toRFC822().'"', true);
		}

		echo $content;
	}
}
