<?php
/**
 * @version SVN: $Id$
 * @package    com_userprofile
 * @author     Mathias Hortig {@link http://tuts4you.de/}
 * @license    GNU/GPL
 */


defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
 

class UserProfileController extends JControllerLegacy
{
    /**
     * Method to display the view
     *
     * @access    public
     */
    function display()
    {
        parent::display();
    }

    function saveCSS()
    {
      JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );
      $file = JPATH_COMPONENT_SITE . '/css/userprofile.css';
      $filecontent  = JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);

      jimport('joomla.filesystem.file');
      if( !JFile::write($file, $filecontent)) {
        $message = "CSS File could not be saved!";
      } else {
        $message = "CSS File successfully saved!";
      }
      $link = JRoute::_('index.php?option='. JRequest::getCmd( 'option' ) , false);
      $this->setRedirect($link, $message);

    }

    function cancelCSS()
    {
      $link = JRoute::_('index.php?option=' . JRequest::getCmd( 'option' ) , false);
      $this->setRedirect($link);
    }
 
}
