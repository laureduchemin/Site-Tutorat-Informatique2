<?php
/**
* @version 			SEBLOD 3.x More
* @package			SEBLOD (App Builder & CCK) // SEBLOD nano (Form Builder)
* @url				http://www.seblod.com
* @editor			Octopoos - www.octopoos.com
* @copyright		Copyright (C) 2013 SEBLOD. All Rights Reserved.
* @license 			GNU General Public License version 2 or later; see _LICENSE.php
**/

defined( '_JEXEC' ) or die;

// Plugin
class plgCCK_FieldTabs extends JCckPluginField
{
	protected static $type		=	'tabs';
	protected static $path;
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Construct
	
	// onCCK_FieldConstruct
	public function onCCK_FieldConstruct( $type, &$data = array() )
	{
		if ( self::$type != $type ) {
			return;
		}
		parent::g_onCCK_FieldConstruct( $data );
	}

	// onCCK_FieldConstruct_SearchContent
	public static function onCCK_FieldConstruct_SearchContent( &$field, $style, $data = array() )
	{
		$data['markup']		=	NULL;

		parent::onCCK_FieldConstruct_SearchContent( $field, $style, $data );
	}

	// onCCK_FieldConstruct_SearchSearch
	public static function onCCK_FieldConstruct_SearchSearch( &$field, $style, $data = array() )
	{
		$data['match_mode']	=	NULL;
		$data['markup']		=	NULL;
		$data['validation']	=	NULL;

		parent::onCCK_FieldConstruct_SearchSearch( $field, $style, $data );
	}

	// onCCK_FieldConstruct_TypeContent
	public static function onCCK_FieldConstruct_TypeContent( &$field, $style, $data = array() )
	{
		$data['markup']		=	NULL;

		parent::onCCK_FieldConstruct_TypeContent( $field, $style, $data );
	}
	
	// onCCK_FieldConstruct_TypeForm
	public static function onCCK_FieldConstruct_TypeForm( &$field, $style, $data = array() )
	{
		$data['markup']		=	NULL;
		$data['validation']	=	NULL;

		parent::onCCK_FieldConstruct_TypeForm( $field, $style, $data );
	}

	// -------- -------- -------- -------- -------- -------- -------- -------- // Prepare
	
	// onCCK_FieldPrepareContent
	public function onCCK_FieldPrepareContent( &$field, $value = '', &$config = array() )
	{
		if ( self::$type != $field->type ) {
			return;
		}
		parent::g_onCCK_FieldPrepareContent( $field, $config );
		
		// Init
		$id			=	$field->name;
		$value		=	(int)$field->defaultvalue;
		$value		=	( $value ) ? $value - 1 : 0;
		$group_id	=	( $field->location != '' ) ? $field->location : 'cck_tabs1';
		static $groups	=	array();
		if ( !isset( $groups[$group_id] ) ) {
			$groups[$group_id]	=	array( 'active'=>$value, 'current'=>0 );
		}

		// Prepare
		$html		=	'';
		if ( $field->state ) {
			$group_id	=	( $field->location != '' ) ? $field->location : 'cck_tabs1';
			if ( $field->bool == 2 ) {
				$html	=	JCckDevTabs::end();
			} elseif ( $field->bool == 1 ) {
				$html	=	JCckDevTabs::open( $group_id, $id, $field->label );
				if ( $groups[$group_id]['current'] == $groups[$group_id]['active'] ) {
					$js	=	'(function($){ $(document).ready(function() { $("#'.$group_id.'Tabs > li,#'.$group_id.'Content > div").removeClass("active"); $("#'.$group_id.'Tabs > li:eq('.(int)$groups[$group_id]['active'].'),#'.$id.'").addClass("active"); }); })(jQuery);';
					JFactory::getDocument()->addScriptDeclaration( $js );
				}
			} else {
				$html	=	JCckDevTabs::start( $group_id, $id, $field->label, array( 'active'=>$id ) );
			}
			$groups[$group_id]['current']++;
		}

		// Set
		$field->html	=	$html;
		$field->value	=	$field->label;
		$field->label	=	'';
	}
	
	// onCCK_FieldPrepareForm
	public function onCCK_FieldPrepareForm( &$field, $value = '', &$config = array(), $inherit = array(), $return = false )
	{
		if ( self::$type != $field->type ) {
			return;
		}
		self::$path	=	parent::g_getPath( self::$type.'/' );
		parent::g_onCCK_FieldPrepareForm( $field, $config );
		
		// Init
		if ( count( $inherit ) ) {
			$id		=	( isset( $inherit['id'] ) && $inherit['id'] != '' ) ? $inherit['id'] : $field->name;
		} else {
			$id		=	$field->name;
		}
		$value		=	( $value != '' ) ? (int)$value : (int)$field->defaultvalue;
		$value		=	( $value ) ? $value - 1 : 0;
		$group_id	=	( $field->location != '' ) ? $field->location : 'cck_tabs1';
		static $groups	=	array();
		if ( !isset( $groups[$group_id] ) ) {
			$groups[$group_id]	=	array( 'active'=>$value, 'current'=>0 );
		}

		// Prepare
		$form		=	'';
		if ( $field->state ) {
			if ( $field->bool == 2 ) {
				$form	=	JCckDevTabs::end();
			} elseif ( $field->bool == 1 ) {
				$form	=	JCckDevTabs::open( $group_id, $id, $field->label );
				$form	=	str_replace( 'class="tab-pane', 'class="tab-pane cck-tab-pane', $form );
				if ( $groups[$group_id]['current'] == $groups[$group_id]['active'] ) {
					$js	=	'(function($){ $(document).ready(function() { $("#'.$group_id.'Tabs > li,#'.$group_id.'Content > div").removeClass("active"); $("#'.$group_id.'Tabs > li:eq('.(int)$groups[$group_id]['active'].'),#'.$id.'").addClass("active"); }); })(jQuery);';
					JFactory::getDocument()->addScriptDeclaration( $js );
				}
			} else {
				$form	=	JCckDevTabs::start( $group_id, $id, $field->label, array( 'active'=>$id ) );
				$form	=	str_replace( 'class="nav nav-tabs"', 'class="nav nav-tabs cck-tabs"', $form );
				$form	=	str_replace( 'class="tab-pane', 'class="tab-pane cck-tab-pane', $form );
			}
			$groups[$group_id]['current']++;
		}

		// Set
		$field->form	=	$form;	// todo: '<div class="tabbable tabs-left">'
		$field->value	=	$field->label;
		$field->label	=	'';
		
		// Return
		if ( $return === true ) {
			return $field;
		}
	}
	
	// onCCK_FieldPrepareSearch
	public function onCCK_FieldPrepareSearch( &$field, $value = '', &$config = array(), $inherit = array(), $return = false )
	{
		if ( self::$type != $field->type ) {
			return;
		}
		
		// Prepare
		self::onCCK_FieldPrepareForm( $field, $value, $config, $inherit, $return );
		
		// Return
		if ( $return === true ) {
			return $field;
		}
	}
	
	// onCCK_FieldPrepareStore
	public function onCCK_FieldPrepareStore( &$field, $value = '', &$config = array(), $inherit = array(), $return = false )
	{
		if ( self::$type != $field->type ) {
			return;
		}
	}
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Render
	
	// onCCK_FieldRenderContent
	public static function onCCK_FieldRenderContent( &$field, &$config = array() )
	{
		$field->markup	=	'none';

		return parent::g_onCCK_FieldRenderContent( $field, 'html' );
	}
	
	// onCCK_FieldRenderForm
	public static function onCCK_FieldRenderForm( &$field, &$config = array() )
	{
		$field->markup	=	'none';

		return parent::g_onCCK_FieldRenderForm( $field );
	}
}
?>