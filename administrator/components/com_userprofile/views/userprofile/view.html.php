<?php
/**
 * @version SVN: $Id$
 * @package    com_userprofile
 * @author     Mathias Hortig {@link http://tuts4you.de/}
 * @license    GNU/GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
jimport( 'joomla.filesystem.file' );
 
class UserProfileViewUserProfile extends JViewLegacy
{
    function display($tpl = null)
    {
        $uri =& JFactory::getURI();
        
        JToolBarHelper::title('UserProfile : <small><small>[ Css Edit ]</small></small>' );
        JToolBarHelper::save('saveCss');
        JToolBarHelper::cancel('cancelCss');

        $file = JPATH_COMPONENT_SITE . '/css/userprofile.css';
        $content = JFile::read($file);
        $content = htmlspecialchars($content, ENT_COMPAT, 'UTF-8');

        $this->assignRef('content', $content);
        $this->assignRef('title', $title);
        $this->assignRef('filename', $file);
        $this->assignRef('action',$uri->toString());
 
        parent::display($tpl);
    }
}
