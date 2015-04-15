<?php
/**
 *------------------------------------------------------------------------------
 *  iCagenda v3 by Jooml!C - Events Management Extension for Joomla! 2.5 / 3.x
 *------------------------------------------------------------------------------
 * @package     com_icagenda
 * @copyright   Copyright (c)2012-2015 Cyril Rez, Jooml!C - All rights reserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Cyril Rez (Lyr!C)
 * @link        http://www.joomlic.com
 *
 * @version     3.2.13 2014-01-26
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.application.component.controllerform');

/**
 * Event controller class.
 */
class iCagendaControllerMail extends JControllerForm
{

    function __construct() {
        $this->view_list = 'icagenda';
        parent::__construct();
    }

}
