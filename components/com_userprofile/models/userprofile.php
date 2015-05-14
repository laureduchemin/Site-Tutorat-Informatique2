<?php
/**
 * @version SVN: $Id$
 * @package    com_userprofile
 * @author     Mathias Hortig {@link http://tuts4you.de/}
 * @license    GNU/GPL
 */

// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
jimport( 'joomla.html.parameter' );

class UserProfileModelUserProfile extends JModelLegacy
{

    function GetUserList($page)
    {

     $menu = JSite::getMenu()->getActive();
     $params = $menu->params;
     $mainframe = &JFactory::getApplication();
     $pathway   =& $mainframe->getPathway();	
     
     $userGroupWhereStatement = "u.id in (select ugm.user_id from #__user_usergroup_map ugm where ";
     $hasGroups = false;
     if($params->get('usergroupids') != null)
     {
     	foreach($params->get('usergroupids') as $key => $value)
     	{
        	 if($value != "")
         	{
			if($hasGroups == false)
			{
				$userGroupWhereStatement .= "ugm.group_id=" . $value;
				$hasGroups = true;
			}
			else
			{
				$userGroupWhereStatement .= " or ugm.group_id=" . $value;
			}
	 	}
	}
     }
     $userGroupWhereStatement .= ")";


     $db =& JFactory::getDBO();
 
     $query = "SELECT u.id, u.username, u.name,u.email, DATE_FORMAT(u.registerDate,'%d.%m.%Y') as registerDate, DATE_FORMAT(u.lastVisitDate,'%d.%m.%Y') as lastVisitDate,";
     $query .= " (select w.profile_value from #__user_profiles w where w.user_id=u.id and w.profile_key='profile.website') as website,";
     $query .= " (SELECT count( c.id ) FROM #__content c WHERE c.created_by = u.id) AS messagesCount FROM #__users u";
     if($hasGroups)
     {     
         $query .= " WHERE " . $userGroupWhereStatement;
     }
     $query .= " order by messagesCount desc";
     if($params->get('userCount') != 0)
     {    
	$query .= " LIMIT " . ($page * $params->get('userCount'))  . ", " . $params->get('userCount');
        $pathway->addItem(JText::_('COM_USERPROFILE_PAGE') . ' ' . ($page+1), '');
     }
     $db->setQuery( $query );
     $rows = $db->loadObjectList();


     $html = '<table id="userprofiletable">';
     $html .= '<tr><th>' . JText::_('COM_USERPROFILE_AUTHOR') . '</th>';

     if($params->get('showHomepage',0) == 0)
     {
       $html .= '<th>' . JText::_('COM_USERPROFILE_HOMEPAGE') . '</th>';
     }

     if($params->get('showEmail',1) == 0)
     {
       $html .= '<th>' . JText::_('COM_USERPROFILE_EMAIL') . '</th>';
     }
     
     if($params->get('showWrittenArticle',0) == 0)
     {
       $html .= '<th>' . JText::_('COM_USERPROFILE_ARTICLECOUNT') . '</th>';
     }

     if($params->get('showRegisterDate',0) == 0)
     {
       $html .= '<th>' . JText::_('COM_USERPROFILE_REGISTEREDAT') . '</th>';
     }

     if($params->get('showLastVisitDate',0) == 0)
     {
       $html .= '<th>' . JText::_('COM_USERPROFILE_LASTLOGINAT') . '</th>';
     }

     $html .= '</tr>';
     foreach($rows as $row)
     {
       $html .= '<tr>';
       $userNameToShow = str_replace("[NAME]",$row->name, str_replace("[USERNAME]", $row->username,$params->get('userMask','[USERNAME]')));
       
       if($params->get('showDetail') == 0)
       {
         $html .= '<td><a href="' . JRoute::_("index.php?option=com_userprofile&view=userprofile&id=".$row->id . "%3A" . $row->username). '">'.$userNameToShow.'</a></td>';
       }
       else
       {
         $html .= '<td>'.$userNameToShow.'</td>';
       }

       if($params->get('showHomepage') == 0)
       {
         if($params->get('hideHomepage') == 0 && $row->messagesCount == 0)
         {
           $html .= '<td><small>' . JText::_('COM_USERPROFILE_NOHOMEPAGE') . '</small></td>';
         }
         else
         {
           $html .= '<td><a target="_blank" href="' . str_replace("\"","",str_replace("\\", "", $row->website)) .'">'.str_replace("\"","",str_replace("\\", "", $row->website)).'</a></td>';
         }
       }

       if($params->get('showEmail',1) == 0)
       {
         $html .= '<td><a href="mailto:'.$row->email.'">'.$row->email.'</a></td>';
       }
     
       if($params->get('showWrittenArticle',0) == 0)
       {
         $html .= '<td>'.$row->messagesCount.'</td>';
       }

       if($params->get('showRegisterDate',0) == 0)
       {
         $html .= '<td>'.$row->registerDate.'</td>';
       }

       if($params->get('showLastVisitDate',0) == 0)
       {
         $html .= '<td>'.$row->lastVisitDate.'</td>';
       }

       $html .= '</tr>';

     }
     $html .= '</table>';

     // region Pages
     if($params->get('userCount') != 0)
     {
       $db->setQuery("Select count(*) FROM #__users");
       $count = $db->loadResult();
       for($i=0; $i<=($count/$params->get('userCount'));$i++)
       {
	$html .= '<a href="'.JRoute::_("index.php?option=com_userprofile&view=userprofile&page=".$i).'">'.($i+1).'</a>';
       }
     }


     $html .= '<p  style="text-align:right;"><small>Powered By <a target="_blank" href="http://tuts4you.de/">http://tuts4you.de/</a></small></p>';
     return $html;
  }

  function GetUserDetails($id)
  {
      $menu = JSite::getMenu()->getActive();
      $params = $menu->params;
      $mainframe = &JFactory::getApplication();
      $pathway   =& $mainframe->getPathway();	

      $doc = JFactory::getDocument();

      $db =& JFactory::getDBO();
 
      $query = "SELECT u.id, u.username, u.name,u.email, DATE_FORMAT(u.registerDate,'%d.%m.%Y') as registerDate, DATE_FORMAT(u.lastVisitDate,'%d.%m.%Y') as lastVisitDate, (select w.profile_value from #__user_profiles w where w.user_id=u.id and w.profile_key='profile.website') as website, (select w.profile_value from #__user_profiles w where w.user_id=u.id and w.profile_key='profile.aboutme') as about,(SELECT count( c.id ) FROM #__content c WHERE c.created_by = u.id) AS messagesCount FROM #__users u where u.id=" . $id;
      $db->setQuery( $query );
      $user = $db->loadObjectList();
      $user = $user[0];
      $userNameToShow = str_replace("[NAME]",$user->name, str_replace("[USERNAME]", $user->username,$params->get('userMask','[USERNAME]')));
      $doc->setTitle(JText::_('COM_USERPROFILE_DETAILHEADER') . " " . $userNameToShow);
      $pathway->addItem(JText::_('COM_USERPROFILE_DETAILHEADER') . " " . $userNameToShow, '');
      
      $userPosts = $this->GetUserPosts($id);

      $html = '';
      $html .= '<table id="userprofiledetailtable">';
      $html .= '<tr><th class="userprofilekey">' . JText::_('COM_USERPROFILE_AUTHOR') . '</th><td>'.$userNameToShow.'</td></tr>';
      if($params->get('showHomepage') == 0)
      {
     
        if($params->get('hideHomepage') == 0 && $userPosts == "")
        {
          $html .= '<tr><th class="userprofilekey">' . JText::_('COM_USERPROFILE_HOMEPAGE') . '</th><td><small>' . JText::_('COM_USERPROFILE_NOHOMEPAGE') . '</small></td></tr>';
        }
        else
        {
         $html .= '<tr><th class="userprofilekey">' . JText::_('COM_USERPROFILE_HOMEPAGE') . '</th><td><a target="_blank" href="'.str_replace("\"","",str_replace("\\", "", $user->website)).'">' . str_replace("\"","",str_replace("\\", "", $user->website)) . '</td></tr>';
        }
      }

      if($params->get('showEmail',0) == 0)
      {
        $html .= '<tr><th class="userprofilekey">' . JText::_('COM_USERPROFILE_EMAIL') . '</th><td><a href="mailto:'.$user->email.'">'.$user->email.'</a></td></tr>';
      }
     
      if($params->get('showWrittenArticle',0) == 0)
      {
        $html .= '<tr><th class="userprofilekey">' . JText::_('COM_USERPROFILE_ARTICLECOUNT') . '</th><td>'.$user->messagesCount.'</td></tr>';
      }

      if($params->get('showRegisterDate',0) == 0)
      {
        $html .= '<tr><th class="userprofilekey">' . JText::_('COM_USERPROFILE_REGISTEREDAT') . '</th><td>'.$user->registerDate.'</td></tr>';
      }

      if($params->get('showLastVisitDate',0) == 0)
      {
        $html .= '<tr><th class="userprofilekey">' . JText::_('COM_USERPROFILE_LASTLOGINAT') . '</th><td>'.$user->lastVisitDate.'</td></tr>';
      }

    if($params->get('AboutMe') == 0)
    {
      $html .= '<tr><th class="userprofilekey">' . JText::_('COM_USERPROFILE_ABOUTAUTHOR') . '</th><td>'.$user->about.'</td></tr>';
    }

    $html .= '</table>';

    $html .= '<br />';
    $html .= $userPosts;
    return $html;
  }

  private function GetUserPosts($id)
  {
      $menu = JSite::getMenu()->getActive();
      $params = $menu->params;

      require_once JPATH_SITE.'/components/com_content/helpers/route.php';
      $db =& JFactory::getDBO();
      $query = "SELECT cc.title AS category, a.id, a.title, DATE_FORMAT(a.created,'%d.%m.%Y') as created, a.created_by, a.hits, cc.id as catid
      FROM #__content AS a
      LEFT JOIN #__categories AS cc ON a.catid = cc.id
            LEFT JOIN #__users AS u ON u.id = a.created_by
      where a.created_by=" . $id;
      if($params->get('showOnlyPublicPosts') == 0)
      {
       $query .= " and a.access = 1 ";
      }	
      $query .= " ORDER BY a.created DESC"; 
      $db->setQuery( $query );
        $table = $db->loadObjectList();
        $html = '<br /><table id="userprofiletable">';
        $html .= '<tr><th>' . JText::_('COM_USERPROFILE_CATEGORY') . '</th><th>' . JText::_('COM_USERPROFILE_TITLE') . '</th><th>' . JText::_('COM_USERPROFILE_CREATEDAT') . '</th><th>' . JText::_('COM_USERPROFILE_VIEWCOUNT') . '</th></tr>';
        $hasdata = false;
	foreach($table as $row)
        {
	    $hasdata = true;
            $html .= '<tr>';

            $html .= '<td><a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($row->catid)) . '">'.$row->category.'</a></td>';
            $html .= '<td><a href="' . JRoute::_(ContentHelperRoute::getArticleRoute($row->id, $row->catid)) . '">'.$row->title.'</a></td>';
            $html .= '<td>'.$row->created.'</td>';
            $html .= '<td>'.$row->hits.'</td>';

            $html .= '</tr>';
      }
           $html .= '</table>';
        if($hasdata)
	{
		return $html;
	}
	else
	{
		return "";
	}

  }
}
