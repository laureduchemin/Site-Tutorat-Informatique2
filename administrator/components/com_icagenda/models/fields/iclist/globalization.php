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
 * @version     3.1.9 2013-09-06
 * @since       2.1.7
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

jimport( 'joomla.filesystem.path' );
jimport('joomla.form.formfield');

class JFormFieldiClist_globalization extends JFormField
{
	protected $type='iclist_globalization';

	protected function getInput()
	{

		$lang = JFactory::getLanguage();
		$langTag = $lang->getTag();
		$langName = $lang->getName();
		if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_icagenda/globalization/'.$langTag.'.php')){

			$langTag='en-GB';
			$currentText=JTEXT::_('COM_ICAGENDA_DATE_FORMAT_DEFAULT').' ['.$langTag.'] :';

		} else {
			$currentText=JTEXT::_('COM_ICAGENDA_DATE_FORMAT_CURRENT').' ['.$langTag.'] :';
		}

		$globalize = JPATH_ADMINISTRATOR.'/components/com_icagenda/globalization/'.$langTag.'.php';
		require_once $globalize;
		$iso = JPATH_ADMINISTRATOR.'/components/com_icagenda/globalization/iso.php';
		require_once $iso;


		$class = JRequest::getVar('class');

		$html= '
			<select id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" style="width:250px;" >';
		if ($this->name!='jform[format]' && $this->name!='format') $html.='<option value="0" style="text-align:center;">- '.JTEXT::_('COM_ICAGENDA_SELECT_FORMAT').' -</option>';



		// Current Language in Joomla (admin)

if(version_compare(JVERSION, '3.0', 'lt')) {
			$html.='<optgroup label="&nbsp;"></optgroup>';
			$html.='<optgroup label="'.$currentText.'" style="font-style:normal; color:#333333;"></optgroup>';
} else {
			$html.='<optgroup label="'.$currentText.'">';
}

			if(isset($dateglobalize_1)) {
				$html.='<option value="1"';
				if ($this->value == '1'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_1.'</option>';
			}

			if(isset($dateglobalize_2)) {
				$html.='<option value="2"';
				if ($this->value == '2'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_2.'</option>';
			}

			if(isset($dateglobalize_3)) {
				$html.='<option value="3"';
				if ($this->value == '3'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_3.'</option>';
			}

			if(isset($dateglobalize_4)) {
				$html.='<option value="4"';
				if ($this->value == '4'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_4.'</option>';
			}

			if(isset($dateglobalize_5)) {
				$html.='<option value="5"';
				if ($this->value == '5'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_5.'</option>';
			}

			if(isset($dateglobalize_6)) {
				$html.='<option value="6"';
				if ($this->value == '6'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_6.'</option>';
			}

			if(isset($dateglobalize_7)) {
				$html.='<option value="7"';
				if ($this->value == '7'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_7.'</option>';
			}

			if(isset($dateglobalize_8)) {
				$html.='<option value="8"';
				if ($this->value == '8'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_8.'</option>';
			}

			if(isset($dateglobalize_9)) {
				$html.='<option value="9"';
				if ($this->value == '9'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_9.'</option>';
			}

			if(isset($dateglobalize_10)) {
				$html.='<option value="10"';
				if ($this->value == '10'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_10.'</option>';
			}

			if(isset($dateglobalize_11)) {
				$html.='<option value="11"';
				if ($this->value == '11'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_11.'</option>';
			}

			if(isset($dateglobalize_12)) {
				$html.='<option value="12"';
				if ($this->value == '12'){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$dateglobalize_12.'</option>';
			}

if(version_compare(JVERSION, '3.0', 'ge')) {
			$html.='</optgroup>';
}


		// Other date format in English (if 'en-GB' is current language)
			if ($langTag == 'en-GB') {
				$html.='<optgroup label="&nbsp;"></optgroup>';
				$html.='<optgroup label="Other date format in English" style="font-style:normal; color:#333333;"></optgroup>';

			// Extra en-US
if(version_compare(JVERSION, '3.0', 'lt')) {
				$html.='<optgroup label="en-US (more formats if current language) :" style="font-weight:normal; color:#777777;"></optgroup>';
} else {
				$html.='<optgroup label="en-US (more formats if current language) :">';
}

				$html.='<option value="'.$extravalue_1.'"';
				if ($this->value == $extravalue_1){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$extra_1.'</option>';

				$html.='<option value="'.$extravalue_2.'"';
				if ($this->value == $extravalue_2){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$extra_2.'</option>';

				$html.='<option value="'.$extravalue_3.'"';
				if ($this->value == $extravalue_3){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$extra_3.'</option>';

				$html.='<option value="'.$extravalue_4.'"';
				if ($this->value == $extravalue_4){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$extra_4.'</option>';

				$html.='<option value="'.$extravalue_5.'"';
				if ($this->value == $extravalue_5){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$extra_5.'</option>';

if(version_compare(JVERSION, '3.0', 'ge')) {
				$html.='</optgroup>';
}

			// Extra en-CA
if(version_compare(JVERSION, '3.0', 'lt')) {
				$html.='<optgroup label="en-CA :" style="font-weight:normal; color:#777777;"></optgroup>';
} else {
				$html.='<optgroup label="en-CA :">';
}

				$html.='<option value="'.$extravalue_6.'"';
				if ($this->value == $extravalue_6){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$extra_6.'</option>';

if(version_compare(JVERSION, '3.0', 'ge')) {
				$html.='</optgroup>';
}

			// Extra en-SG
if(version_compare(JVERSION, '3.0', 'lt')) {
				$html.='<optgroup label="en-SG :" style="font-weight:normal; color:#777777;"></optgroup>';
} else {
				$html.='<optgroup label="en-SG :">';
}

				$html.='<option value="'.$extravalue_7.'"';
				if ($this->value == $extravalue_7){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$extra_7.'</option>';

if(version_compare(JVERSION, '3.0', 'ge')) {
				$html.='</optgroup>';
}

			}



		// International Date Format (ISO)
			$html.='<optgroup label="&nbsp;"></optgroup>';
if(version_compare(JVERSION, '3.0', 'lt')) {
			$html.='<optgroup label="'.JTEXT::_('COM_ICAGENDA_DATE_FORMAT_ISO').'" style="font-style:normal; color:#333333;"></optgroup>';
} else {
			$html.='<optgroup label="'.JTEXT::_('COM_ICAGENDA_DATE_FORMAT_ISO').'">';
}

			$html.='<option value="'.$iso.'"';
			if ($this->value == $iso){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>1996-04-22</option>';

if(version_compare(JVERSION, '3.0', 'ge')) {
			$html.='</optgroup>';
}



		// Global date formats with separator
if(version_compare(JVERSION, '3.0', 'lt')) {
			$html.='<optgroup label="&nbsp;"></optgroup>';
			$html.='<optgroup label="'.JTEXT::_('COM_ICAGENDA_DATE_FORMAT_SEPARATOR').'" style="font-style:normal; color:#333333;"></optgroup>';
} else {
			$html.='<optgroup label="'.JTEXT::_('COM_ICAGENDA_DATE_FORMAT_SEPARATOR').'">';
}


		// DMY Little-endian (day, month, year), e.g. 22.04.96 or 22/04/96
if(version_compare(JVERSION, '3.0', 'lt')) {
			$html.='<optgroup label="'.JTEXT::_('COM_ICAGENDA_DATE_FORMAT_DMY').' :" style="font-weight:normal; color:#777777;"></optgroup>';
}

			$html.='<option value="'.$dmy_1.'"';
			if ($this->value == $dmy_1){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>22␣04␣1996</option>';

			$html.='<option value="'.$dmy_2.'"';
			if ($this->value == $dmy_2){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>22␣04␣96</option>';

			$html.='<option value="'.$dmy_3.'"';
			if ($this->value == $dmy_3){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>22␣04</option>';

			$html.='<option value="'.$dmy_4.'"';
			if ($this->value == $dmy_4){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>04␣96</option>';

			$html.='<option value="'.$dmy_5.'"';
			if ($this->value == $dmy_5){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>'.$dmy_text_5.'</option>';

			$html.='<option value="'.$dmy_6.'"';
			if ($this->value == $dmy_6){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>'.$dmy_text_6.'</option>';


		// MDY Middle-endian (month, day, year), e.g. 04/22/96
if(version_compare(JVERSION, '3.0', 'lt')) {
			$html.='<optgroup label="'.JTEXT::_('COM_ICAGENDA_DATE_FORMAT_MDY').' :" style="font-weight:normal; color:#777777;"></optgroup>';
}

			$html.='<option value="'.$mdy_1.'"';
			if ($this->value == $mdy_1){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>04␣22␣1996</option>';

			$html.='<option value="'.$mdy_2.'"';
			if ($this->value == $mdy_2){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>04␣22␣96</option>';

			$html.='<option value="'.$mdy_3.'"';
			if ($this->value == $mdy_3){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>04␣22</option>';

			$html.='<option value="'.$mdy_4.'"';
			if ($this->value == $mdy_4){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>04␣96</option>';

			$html.='<option value="'.$mdy_5.'"';
			if ($this->value == $mdy_5){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>'.$mdy_text_5.'</option>';

			$html.='<option value="'.$mdy_6.'"';
			if ($this->value == $mdy_6){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>'.$mdy_text_6.'</option>';


		// YMD Big-endian (year, month, day), e.g. 1996-04-22
if(version_compare(JVERSION, '3.0', 'lt')) {
			$html.='<optgroup label="'.JTEXT::_('COM_ICAGENDA_DATE_FORMAT_YMD').' :" style="font-weight:normal; color:#777777;"></optgroup>';
}

			$html.='<option value="'.$ymd_1.'"';
			if ($this->value == $ymd_1){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>1996␣04␣22</option>';

			$html.='<option value="'.$ymd_2.'"';
			if ($this->value == $ymd_2){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>96␣04␣22</option>';

			$html.='<option value="'.$ymd_3.'"';
			if ($this->value == $ymd_3){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>04␣22</option>';

			$html.='<option value="'.$ymd_4.'"';
			if ($this->value == $ymd_4){
				$html.='selected="selected" style="background:#D4D4D4;"';
			}
			$html.='>96␣04</option>';

			if(isset($ymd_text_5)) {
				$html.='<option value="'.$ymd_5.'"';
				if ($this->value == $ymd_5){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$ymd_text_5.'</option>';
			}

			if(isset($ymd_text_6)) {
				$html.='<option value="'.$ymd_6.'"';
				if ($this->value == $ymd_6){
					$html.='selected="selected" style="background:#D4D4D4;"';
				}
				$html.='>'.$ymd_text_6.'</option>';
			}

if(version_compare(JVERSION, '3.0', 'ge')) {
			$html.='</optgroup>';
}

		$html.='</select>';
		return $html;

	}
}
