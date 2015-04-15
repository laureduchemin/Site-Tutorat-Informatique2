<?php
/**
 *------------------------------------------------------------------------------
 *  iCagenda v3 by Jooml!C - Events Management Extension for Joomla! 2.5 / 3.x
 *------------------------------------------------------------------------------
 * @package     com_icagenda
 * @copyright   Copyright (c)2012-2015 Cyril Rezé, Jooml!C - All rights reserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Tom-Henning (MaW) / Cyril Rezé (Lyr!C)
 * @link        http://www.joomlic.com
 *
 * @version     3.4.1 2015-01-30
 * @since       3.2.9
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

require_once JPATH_COMPONENT . '/helpers/iCalcreator.class.php';
//$v = new vCalendar($config);
$v = new vCalendar();
$v->setConfig( 'filename', 'icagenda.ics' );
$v->prodid = 'iCagenda';

$config = JFactory::getConfig();

// Joomla 3.x / 2.5 SWITCH
if(version_compare(JVERSION, '3.0', 'ge'))
{
	$offset = $config->get('offset');
}
else
{
	$offset = $config->getValue('config.offset');
}

$dateTimeZone = new DateTimeZone($offset);
$dateTime = new DateTime("now", $dateTimeZone);
$timeOffset = $dateTimeZone->getOffset($dateTime);
$timezone = ($timeOffset / 3600);

$tz = 'UTC';
$v->setProperty( 'method', 'PUBLISH' );
$v->setProperty( 'X-WR-CALDESC', '' );
$v->setProperty( 'X-WR-TIMEZONE', $tz);
$xprops = array( 'X-LIC-LOCATION' => $tz);

if(version_compare(PHP_VERSION, '5.3.0') >= 0)
{
	iCalUtilityFunctions::createTimezone($v, $tz, $xprops);
}
$stamp = $this->data;

$get_date = '';
$href='#';
$start_Datetime = '';
$start_Date = '';
$end_Datetime = '';
$end_Date = '';

foreach ($stamp->items as $item)
{
	$s_dates = $item->dates;
	$single_dates = unserialize($s_dates);

	if (JRequest::getVar('date', ''))
	{
		$var_one_date = JRequest::getVar('date');
		$one_ex = explode('-', $var_one_date);
		$get_one_date = $one_ex['0'].'-'.$one_ex['1'].'-'.$one_ex['2'].' '.$one_ex['3'].':'.$one_ex['4'].':00';
		$get_date = date('Y-m-d-H-i', strtotime($get_one_date)-$timeOffset);
	}
	else
	{
		$get_date = date('Y-m-d-H-i', strtotime($item->next)-$timeOffset);
	}

	$ex = explode('-', $get_date);
	$this_date = $ex['0'].'-'.$ex['1'].'-'.$ex['2'].' '.$ex['3'].':'.$ex['4'];
	$startdate = date('Y-m-d-H-i', strtotime($item->start_datetime)-$timeOffset);
	$enddate = date('Y-m-d-H-i', strtotime($item->end_datetime)-$timeOffset);

	if ( ($get_date >= $startdate) AND ($get_date <= $enddate) AND (!in_array($this_date, $single_dates)) )
	{
		$ex_S = explode('-', $startdate);
		$ex_E = explode('-', $enddate);
		$start_Datetime = $ex_S['0'].$ex_S['1'].$ex_S['2'].'T'.$ex_S['3'].$ex_S['4'].'00Z';
		$end_Datetime = $ex_E['0'].$ex_E['1'].$ex_E['2'].'T'.$ex_E['3'].$ex_E['4'].'00Z';
		$start_Date = $ex_S['0'].$ex_S['1'].$ex_S['2'];
		$end_Date = $ex_E['0'].$ex_E['1'].$ex_E['2'];
	}
	else
	{
		$start_Datetime = $end_Datetime = $ex['0'].$ex['1'].$ex['2'].'T'.$ex['3'].$ex['4'].'00Z';
		$start_Date = $end_Date = $ex['0'].$ex['1'].$ex['2'];
	}

	$urllink = JUri::getInstance()->toString();
	$cleanurl = preg_replace('/&tmpl=[^&]*/', '', $urllink);
	$cleanurl = preg_replace('/&vcal=[^&]*/', '', $cleanurl);

	$vevent = &$v->newComponent('vevent');
	$vevent->setProperty('categories', $item->cat_title);
	$vevent->setProperty('summary', $item->title);
	$vevent->setProperty('description', strip_tags($item->desc));
	$vevent->setProperty('url', $item->Event_Link);
	$vevent->setUID($item->id);

	if ( $item->contact_name != '' )
	{
		$vevent->setOrganizer($item->contact_name, $item->contact_email);
	}

	if ($item->displaytime == 1)
	{
		$vevent->setProperty('dtstart', $start_Datetime);
		$vevent->setProperty('dtend', $end_Datetime);
	}
	else
	{
		// All day event (if time not displayed)
		$vevent->setProperty('dtstart', $start_Date, array("VALUE" => "DATE"));
		$vevent->setProperty('dtend', $end_Date, array("VALUE" => "DATE"));
//		$vevent->setProperty ("duration" , "PT24H");
	}
	$vevent->setProperty('location',$item->place_name);
}
$v->returnCalendar();
