<?php
/**
* @version 			SEBLOD 3.x Core ~ $Id: xml.php sebastienheraud $
* @package			SEBLOD (App Builder & CCK) // SEBLOD nano (Form Builder)
* @url				http://www.seblod.com
* @editor			Octopoos - www.octopoos.com
* @copyright		Copyright (C) 2013 SEBLOD. All Rights Reserved.
* @license 			GNU General Public License version 2 or later; see _LICENSE.php
**/

defined( '_JEXEC' ) or die;

echo trim( str_replace( array( '<!-- Begin: SEBLOD 3.x Document { www.seblod.com } -->', '<!-- End: SEBLOD 3.x (App Builder & CCK for Joomla!) { www.seblod.com } -->' ), '', $this->data ) );
?>