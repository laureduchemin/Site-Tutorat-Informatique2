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
 * @version     3.5.2 2015-03-13
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

JHtml::_('behavior.modal');
JHtml::_('behavior.multiselect');

$app = JFactory::getApplication();

// Access Administration Events check.
if (JFactory::getUser()->authorise('icagenda.access.events', 'com_icagenda') && defined('IC_LIBRARY'))
{
	$user		= JFactory::getUser();
	$userId		= $user->get('id');
	$listOrder	= $this->state->get('list.ordering');
	$listDirn	= $this->state->get('list.direction');
	$canOrder	= $user->authorise('core.edit.state', 'com_icagenda');
	$saveOrder	= $listOrder == 'a.ordering';

	if(version_compare(JVERSION, '3.0', 'lt'))
	{
		JHtml::_('behavior.tooltip');
	}
	else
	{
		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		JHtml::_('bootstrap.tooltip');
		JHtml::_('formbehavior.chosen', 'select');
		JHtml::_('dropdown.init');

		$archived	= $this->state->get('filter.published') == 2 ? true : false;
		$trashed	= $this->state->get('filter.published') == -2 ? true : false;

		if ($saveOrder)
		{
	   	 $saveOrderingUrl = 'index.php?option=com_icagenda&task=events.saveOrderAjax&tmpl=component';
	    	JHtml::_('sortablelist.sortable', 'eventsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
		}
	}

	// iCthumb generator pre-settings
//	include_once JPATH_ROOT.'/media/com_icagenda/scripts/icthumb.php';

	// Check if GD is enabled
	if (extension_loaded('gd') && function_exists('gd_info'))
	{
		$ic_params = JComponentHelper::getParams('com_icagenda');
		$thumb_generator = $ic_params->get('thumb_generator', 1);
//		echo "It looks like GD is installed";
	}
	else
	{
		$thumb_generator = 0;
		JError::raiseWarning('101', JText::_('COM_ICAGENDA_PHP_ERROR_GD'));
	}

	// Check if fopen is allowed
	$fopen = true;
	$result = ini_get('allow_url_fopen');

	if (empty($result))
	{
		JError::raiseWarning('101', JText::_('COM_ICAGENDA_PHP_ERROR_FOPEN'));
		$fopen = false;
	}

	// 3.3.3
	$sortFields = array();
	?>

<form action="<?php echo JRoute::_('index.php?option=com_icagenda&view=events'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

	<?php if (version_compare(JVERSION, '3.0', 'lt')) : ?>
		<fieldset id="filter-bar">
			<div class="filter-search fltlft">
				<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
				<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('Search'); ?>" />
				<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
			<div class="filter-select fltrt">
				<select name="filter_published" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('COM_ICAGENDA_SELECT_STATE');?></option>
					<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true);?>
				</select>

				<select name="filter_category" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('COM_ICAGENDA_SELECT_CATEGORY');?></option>
					<?php echo JHtml::_('select.options', $this->categories, 'value', 'text', $this->state->get('filter.category'));?>
				</select>

				<select name="filter_upcoming" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('COM_ICAGENDA_SELECT_DATES');?></option>
					<?php echo JHtml::_('select.options', $this->upcoming, 'value', 'text', $this->state->get('filter.upcoming'));?>
				</select>

				<select name="filter_site_itemid" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('COM_ICAGENDA_SELECT_SITE_ITEMID');?></option>
					<?php echo JHtml::_('select.options', $this->itemids, 'value', 'text', $this->state->get('filter.site_itemid'));?>
				</select>
			</div>
		</fieldset>
		<div class="clr"> </div>

	<?php else : ?>

		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_ICAGENDA_FILTER_SEARCH_EVENTS_DESC'); ?></label>
				<input type="text" name="filter_search" placeholder="<?php echo JText::_('COM_ICAGENDA_FILTER_SEARCH_EVENTS_DESC'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_ICAGENDA_FILTER_SEARCH_EVENTS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<!--div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');  ?></option>
				</select>
			</div-->
			<!--div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div-->
		</div>
		<div class="clearfix"> </div>

	<?php endif;?>



	<?php if(version_compare(JVERSION, '3.0', 'lt')) : ?>
		<table class="adminlist">
	<?php else : ?>
		<table class="table table-striped" id="eventsList">
	<?php endif; ?>

			<thead>
				<tr>
				<?php if(version_compare(JVERSION, '3.0', 'ge')) : ?>
					<!-- Ordering HEADER Joomla 3.x -->
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
				<?php endif; ?>

					<!-- CheckBox HEADER -->
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>

<!-- Status HEADER -->
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>

<!-- Approval HEADER -->
					<th width="1%" style="min-width:55px" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'COM_ICAGENDA_EVENTS_APPROVAL', 'a.approval', $listDirn, $listOrder); ?>
					</th>

<!-- Image HEADER -->
					<th width="130px" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'COM_ICAGENDA_EVENTS_IMAGE', 'a.image', $listDirn, $listOrder); ?>
					</th>

<!-- Title HEADER -->
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_ICAGENDA_EVENTS_TITLE', 'a.title', $listDirn, $listOrder); ?> |
						<?php echo JHtml::_('grid.sort', 'COM_ICAGENDA_TITLE_CATEGORY', 'category', $listDirn, $listOrder); ?>
						<?php //echo JHtml::_('grid.sort', 'COM_ICAGENDA_FORM_FRONTEND_SUBMIT_ITEMID_LBL', 'a.site_itemid', $listDirn, $listOrder); ?>
					</th>


<!-- Image HEADER -->
					<th width="15%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort',  'COM_ICAGENDA_EVENTS_NEXT', 'a.next', $listDirn, $listOrder); ?>
					</th>


<!-- Ordering HEADER Joomla 2.5 -->
				<?php if(version_compare(JVERSION, '3.0', 'lt')) : ?>
					<?php if (isset($this->items[0]->ordering)) { ?>
					<th width="10%">
						<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'a.ordering', $listDirn, $listOrder); ?>
						<?php if ($canOrder && $saveOrder) :?>
							<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'events.saveorder'); ?>
						<?php endif; ?>
					</th>
                	<?php } ?>
				<?php endif; ?>



<!-- Access HEADER -->
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access', $listDirn, $listOrder); ?>
					</th>

<!-- Author HEADER -->
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort',  'JAUTHOR', 'a.username', $listDirn, $listOrder); ?>
					</th>

<!-- Language HEADER -->
					<th width="5%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
					</th>

<!-- ID HEADER -->
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>


				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="12">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>

			<tbody valign="top">
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($listOrder == 'a.ordering');
			$canCreate	= $user->authorise('core.create',		'com_icagenda');
			$canEdit	= $user->authorise('core.edit',			'com_icagenda');
			$canCheckin	= $user->authorise('core.manage',		'com_icagenda') || $item->checked_out == $userId || $item->checked_out == 0;;
			$canChange	= $user->authorise('core.edit.state',	'com_icagenda') && $canCheckin;
			$canEditOwn	= $user->authorise('core.edit.own',		'com_icagenda') && $item->created_by == $userId;
//					$canEditOwn = $user->authorise('core.edit.own',   'com_icagenda.events.'.$item->id) && $item->created_by == $userId;

			// Get Access Names
			$db = JFactory::getDBO();
			$db->setQuery(
				'SELECT `title`' .
				' FROM `#__viewlevels`' .
				' WHERE `id` = '. (int) $item->access
			);
			$access_title = $db->loadObject()->title;



			$todaydate = date('Y-m-d');
			$today =  strtotime($todaydate);
//			$dateformat = "%d %B %Y %H:%M";
			$dateformat = "%d %B %Y";

			$nextget = $item->next;
			$nextget=str_replace(' ', '-', $nextget);
			$nextget=str_replace(':', '-', $nextget);
			$ex_data=explode('-', $nextget);

			$dateform =  date ("Y-m-d", mktime (0,0,0,$ex_data['1'], $ex_data['2'], $ex_data['0']));
//			$datetime =  date ("H:i", mktime ($ex_data['3'],$ex_data['4'],0,0,0,0));

			$nextdate = strtotime($dateform);
//			$nexttime = strtotime($datetime);
//			$timeNext = strftime("%H %i",strtotime("$item->next"));

			?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid?>">

<!-- Ordering Joomla 3.x -->
				<?php if(version_compare(JVERSION, '3.0', 'ge')) : ?>
					<td class="order nowrap center hidden-phone">
					<?php if ($canChange) :
						$disableClassName = '';
						$disabledLabel	  = '';

						if (!$saveOrder) :
							$disabledLabel    = JText::_('JORDERINGDISABLED');
							$disableClassName = 'inactive tip-top';
						endif; ?>
						<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
							<i class="icon-menu"></i>
						</span>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
					<?php else : ?>
						<span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
					<?php endif; ?>
					</td>
				<?php endif; ?>


 <!-- CheckBox Joomla -->
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>

 <!-- Status Joomla -->
               <?php if (isset($this->items[0]->state)) { ?>
					<td class="center">
					<?php
					// Control of dates if valid (EDIT SINCE VERSION 3.0)
//					if (($nextdate == '943916400') OR ($nextdate == '943920000')) {
					if ($item->next == '0000-00-00 00:00:00') {
					    //echo JHtml::_('jgrid.published', $item->state, $i, 'events.', '', 'cb');
					    echo '<br/><i class="icon-warning"></i><br/><span style="color:red;"><strong>' . JText::_('COM_ICAGENDA_NO_VALID_DATE') . '</strong></span>';
						if ($item->state == '1') {
							//$state = 0;
        					$db		= Jfactory::getDbo();
							$query	= $db->getQuery(true);
        					$query->clear();
							$query->update(' #__icagenda_events ');
							$query->set(' state = 0 ' );
							$query->where(' id = ' . (int) $item->id );
							$db->setQuery((string)$query);
							$db->query($query);
 						}

					}
					else {
					    echo JHtml::_('jgrid.published', $item->state, $i, 'events.', $canChange, 'cb');
//					    echo JHtml::_('approveEvent', $item->approval, $i, 'events.', $canChange, 'cb');

					}
					 ?>
					</td>
					<td class="center">
					<?php
					require_once JPATH_COMPONENT .'/helpers/html/events.php';
					$approved = empty( $item->approval ) ? 0 : 1;
					echo JHtml::_('jgrid.state', JHtmlEvents::approveEvents(), $approved, $i, 'events.', (boolean) $approved);
					 ?>
					<?php
					//require_once JPATH_COMPONENT .'/helpers/approved.php';
					//echo JHtml::_('approved.approved', $item->approval, $i, 'events.'); ?>
					<?php //echo JHtml::_('approved.approved', $item->approval, $i); ?>
					<?php //echo icHtmlHelper::approveEvent($item->approval, $i, 'events', $canChange, 'cb'); ?>
					</td>
				<?php } ?>


<!-- Image Joomla -->
					<td class="small hidden-phone">
						<div style="background:#F4F4F4; padding:5px; width:120px; text-align:center; overflow:hidden;">
						<?php
						// Set if run iCthumb
						if (($item->image) AND ($thumb_generator == 1))
						{
							// Get media path
							$params_media = JComponentHelper::getParams('com_media');
							$image_path = $params_media->get('image_path', 'images');

							// Paths to thumbs folder
							$thumbsPath 			= $image_path.'/icagenda/thumbs';

							// Thumbnails width, height, quality and crop option
							$iCparams = JComponentHelper::getParams('com_icagenda');

							// Large Size Options
							$l_thumbOptions		= $iCparams->get('thumb_large');
							$l_width			= is_numeric($l_thumbOptions[0]) ? $l_thumbOptions[0] : '900';
							$l_height			= is_numeric($l_thumbOptions[1]) ? $l_thumbOptions[1] : '600';
							$l_quality			= is_numeric($l_thumbOptions[2]) ? $l_thumbOptions[2] : '100';
							$l_crop				= ! empty($l_thumbOptions[3]) ? true : false;

							// Medium Size Options
							$m_thumbOptions		= $iCparams->get('thumb_medium');
							$m_width			= is_numeric($m_thumbOptions[0]) ? $l_thumbOptions[0] : '300';
							$m_height			= is_numeric($m_thumbOptions[1]) ? $l_thumbOptions[1] : '300';
							$m_quality			= is_numeric($m_thumbOptions[2]) ? $l_thumbOptions[2] : '100';
							$m_crop				= ! empty($m_thumbOptions[3]) ? true : false;

							// Small Size Options
							$s_thumbOptions		= $iCparams->get('thumb_small');
							$s_width			= is_numeric($s_thumbOptions[0]) ? $s_thumbOptions[0] : '100';
							$s_height			= is_numeric($s_thumbOptions[1]) ? $s_thumbOptions[1] : '100';
							$s_quality			= is_numeric($s_thumbOptions[2]) ? $s_thumbOptions[2] : '100';
							$s_crop				= ! empty($s_thumbOptions[3]) ? true : false;

							// XSmall Size Options
							$xs_thumbOptions	= $iCparams->get('thumb_xsmall');
							$xs_width			= is_numeric($xs_thumbOptions[0]) ? $xs_thumbOptions[0] : '48';
							$xs_height			= is_numeric($xs_thumbOptions[1]) ? $xs_thumbOptions[1] : '48';
							$xs_quality			= is_numeric($xs_thumbOptions[2]) ? $xs_thumbOptions[2] : '80';
							$xs_crop			= ! empty($xs_thumbOptions[3]) ? true : false;

							// Generate large thumb if not exist
							iCThumbGet::thumbnail($item->image, $thumbsPath, 'themes',
								$l_width, $l_height, $l_quality, $l_crop, 'ic_large', null, true);

							// Generate medium thumb if not exist
							iCThumbGet::thumbnail($item->image, $thumbsPath, 'themes',
								$m_width, $m_height, $m_quality, $m_crop, 'ic_medium');

							// Generate small thumb if not exist
							iCThumbGet::thumbnail($item->image, $thumbsPath, 'themes',
								$s_width, $s_height, $s_quality, $s_crop, 'ic_small');

							// Generate x-small thumb if not exist
							iCThumbGet::thumbnail($item->image, $thumbsPath, 'themes',
								$xs_width, $xs_height, $xs_quality, $xs_crop, 'ic_xsmall');

							// Sub-folder Destination ($thumbsPath / 'subfolder' /)
							$subFolder = 'system';

							// Display thumbnail in admin events list
							echo iCThumbGet::thumbnailImgTagLinkModal($item->image, $thumbsPath, $subFolder, '120', '100', '100', false);
						}
						elseif ($item->image
							&& $thumb_generator == 0)
						{
							if (filter_var($item->image, FILTER_VALIDATE_URL))
							{
								echo '<a href="'.$item->image.'" class="modal">';
								echo '<img src="'.$item->image.'" alt="" /></a>';
							}
							else
							{
								echo '<a href="../'.$item->image.'" class="modal">';
								echo '<img src="../'.$item->image.'" alt="" /></a>';
							}
						}
						else
						{
							echo '<img style="max-width:120px; max-height:100px;" src="../media/com_icagenda/images/nophoto.jpg" alt="" />';
						}

						// END iCthumb

						?>
						</div>
					</td>
<!-- Title & Category -->
					<!--td class="nowrap has-context"-->
					<td class="has-context">
						<div class="pull-left">
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'events.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($item->language == '*'):?>
								<?php $language = JText::alt('JALL', 'language'); ?>
							<?php else:?>
								<?php $language = $item->language ? $this->escape($item->language) : JText::_('JUNDEFINED'); ?>
							<?php endif;?>
							<?php if ($canEdit || $canEditOwn) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_icagenda&task=event.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
								<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
							<?php endif; ?>
							<div class="small">
								<?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->category); ?>
							</div>
							<?php if (($item->place) OR ($item->city) OR ($item->country)) : ?>
							<!--div class="small" style="height:5px; border-bottom: solid 1px #D4D4D4">
							</div-->
							<p>
							<?php if ($item->place) : ?>
							<div class="small iC-italic-grey">
								<?php echo JText::_('COM_ICAGENDA_TITLE_LOCATION') . ": " . $this->escape($item->place); ?>
							</div>
							<?php endif; ?>
							<?php if ($item->city) : ?>
							<div class="small iC-italic-grey">
								<?php echo JText::_('COM_ICAGENDA_FORM_LBL_EVENT_CITY') . ": " . $this->escape($item->city); ?>
							</div>
							<?php endif; ?>
							<?php if ($item->country) : ?>
							<div class="small iC-italic-grey">
								<?php echo JText::_('COM_ICAGENDA_FORM_LBL_EVENT_COUNTRY') . ": " . $this->escape($item->country); ?>
							</div>
							</p>
							<?php endif; ?>
							<?php endif; ?>
							<?php if (!empty($item->site_itemid)) : ?>
							<a class="hasTooltip" href="<?php echo JURI::root() . 'index.php?option=com_icagenda&view=submit&Itemid=' . $item->site_itemid; ?>" title="<?php echo JText::_('COM_ICAGENDA_FORM_FRONTEND_SUBMIT_ITEMID_DESC'); ?>" target="_blank">
								<div class="btn btn-primary btn-mini">
									<?php echo JText::_('COM_ICAGENDA_FORM_FRONTEND_SUBMIT_ITEMID_LBL') . ": " . $this->escape($item->site_itemid); ?>
								</div>
							</a>
							<?php endif; ?>
						</div>

	<!-- DropDown Edit Joomla 3 -->
	<?php if(version_compare(JVERSION, '3.0', 'ge')) : ?>
						<div class="pull-left">
							<?php
							if ($canChange || $canEditOwn) {
								// Create dropdown items
								JHtml::_('dropdown.edit', $item->id, 'event.');
								JHtml::_('dropdown.divider');
								if ($item->state) :
									JHtml::_('dropdown.unpublish', 'cb' . $i, 'events.');
								else :
									JHtml::_('dropdown.publish', 'cb' . $i, 'events.');
								endif;

//								if ($item->featured) :
//									JHtml::_('dropdown.unfeatured', 'cb' . $i, 'events.');
//								else :
//									JHtml::_('dropdown.featured', 'cb' . $i, 'events.');
//								endif;

								JHtml::_('dropdown.divider');

								if ($archived) :
									JHtml::_('dropdown.unarchive', 'cb' . $i, 'events.');
								else :
									JHtml::_('dropdown.archive', 'cb' . $i, 'events.');
								endif;

								if ($item->checked_out) :
									JHtml::_('dropdown.checkin', 'cb' . $i, 'events.');
								endif;

								if ($trashed) :
									JHtml::_('dropdown.untrash', 'cb' . $i, 'events.');
								else :
									JHtml::_('dropdown.trash', 'cb' . $i, 'events.');
								endif;

								// Render dropdown list
								echo JHtml::_('dropdown.render');
							}
								?>
						</div>
	<?php endif; ?>

					</td>

<!-- Dates -->
				<td class="small hidden-phone">
					<?php

//					if (!$item->time == NULL) {
						// Change to use Joomla core string date format
//						$dateshow = strftime("%d %B %Y",strtotime("$item->next"));
//						$dateshow = JHtml::date($item->next, JText::_('DATE_FORMAT_LC3'));
//						$eventtime = '<small>(' . $item->time . ')</small>';
//					}
//					if ($item->time == NULL) {
//						$dateshow = strftime($dateformat,strtotime($item->next));
//						$eventtime = ' ';
//					}
					$dateshow = JHtml::date($item->next, JText::_('DATE_FORMAT_LC3'), null);

					if ($nextdate > $today) {
						echo '<div style="font-weight:bold; background:#555; color:#FFFFFF; padding: 2px 5px; border-radius: 3px;">'.JText::_( 'COM_ICAGENDA_EVENTS_NEXT_FUTUR' ).'<br /><center>';
					 	echo $dateshow . '</center></div>';
					 }
					if ($nextdate == $today) {
						echo '<div style="font-weight:bold; background:#c30000; color:#FFFFFF; padding: 2px 5px; border-radius: 5px;">'.JText::_( 'COM_ICAGENDA_EVENTS_NEXT_TODAY' ).'<br /><center>';
					 	echo $dateshow . '</center></div>';
					}
					// (EDIT SINCE VERSION 3.0)
					if (($nextdate < $today) && ($item->next != '0000-00-00 00:00:00')) {
						echo '<div style="font-style:italic; background:#E4E4E4; color:#777; padding: 2px 5px; border-radius: 5px;">'.JText::_( 'COM_ICAGENDA_EVENTS_NEXT_PAST' ).'<br /><center>';
					 	echo $dateshow . '</center></div>';
					}
					// Control of dates if valid (EDIT SINCE VERSION 3.0)
					if ($item->next == '0000-00-00 00:00:00'){
						echo '<div style="font-style:italic; background:#E4E4E4; color:#777; padding: 2px 5px; border-radius: 5px;">'.JText::_( 'COM_ICAGENDA_EVENTS_NEXT_ALERT' ).'';
					 	echo '</div>';
					}
					?>
				</td>


<!-- Ordering Joomla 2.5 -->
	<?php if(version_compare(JVERSION, '3.0', 'lt')) : ?>
                <?php if (isset($this->items[0]->ordering)) { ?>
				    <td class="order">
					    <?php if ($canChange) : ?>
						    <?php if ($saveOrder) :?>
							    <?php if ($listDirn == 'asc') : ?>
								    <span><?php echo $this->pagination->orderUpIcon($i, true, 'events.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								    <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'events.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							    <?php elseif ($listDirn == 'desc') : ?>
								    <span><?php echo $this->pagination->orderUpIcon($i, true, 'events.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								    <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'events.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							    <?php endif; ?>
						    <?php endif; ?>
						    <?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
						    <input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
					    <?php else : ?>
						    <?php echo $item->ordering; ?>
					    <?php endif; ?>
				    </td>
                <?php } ?>
	<?php endif; ?>

<!-- Access -->
					<td class="small hidden-phone">
					<?php echo $this->escape($access_title); ?>
				</td>

<!-- Username -->
					<td class="small hidden-phone">
					<?php
//					$user = JFactory::getUser();
//					$userId	= $user->get('id');

//					$name=$user->get('name');
//					$username=$user->get('username');
					$usernone=$item->username;

					if (($usernone=='') AND (!$item->created_by))
					{
						$undefined = '<i>'.JText::_('JUNDEFINED').'</i>';
						echo $undefined;
					}
					elseif ((!$item->created_by) OR (!$item->author_name))
					{
						echo $this->escape($item->username);
					}
					else
					{
						echo $this->escape($item->author_name);
						echo ' ['.$this->escape($item->author_username).']';
					}
					?>
					<?php //echo JText::_('JGLOBAL_USERNAME').': '.$this->escape($username); ?>
						<?php if ($item->created_by_alias) : ?>
					<p class="smallsub">
							<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?>
					</p>
						<?php endif; ?>
					</td>

<!-- Language -->
					<td class="small hidden-phone">
						<?php if ($item->language == '*'):?>
							<?php echo JText::alt('JALL', 'language'); ?>
						<?php else:?>
							<?php echo $item->language ? $this->escape($item->language) : JText::_('JUNDEFINED'); ?>
						<?php endif;?>
					</td>

<!-- ID -->
                	<?php if (isset($this->items[0]->id)) { ?>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
                	<?php }
                	?>

			</tr>
			<?php endforeach;

			// Old Joomla versions asset issue.
			$asset_issue = '';

			if ($asset_issue)
			{
				$ia = '0';
				unset($msg);
				unset($type);
				$msg			= '';
				$type			= '';
				$front_submit	= '';
				$edittx = '<b>'.JText::_( 'JACTION_EDIT' ).'</b>';
				$savetx = '<b>'.JText::_( 'JSAVE' ).'</b>';

            	foreach ($this->items as $i => $item)
            	{
                	if(($item->asset_id == '0') AND ($item->state == '-2'))
                	{
                		$ia = $ia+1;
                		$front_submit = '1';
                	}
				}

				if (($front_submit == 1) AND ($ia == 1))
				{
					$app->enqueueMessage(JText::sprintf( 'COM_ICAGENDA_TRASH_FRONTEND_SUBMITTED_1', $edittx, $savetx ), 'notice');
				}
				elseif (($front_submit == 1) AND ($ia > 1))
				{
					$app->enqueueMessage(JText::sprintf( 'COM_ICAGENDA_TRASH_FRONTEND_SUBMITTED', $edittx, $savetx ), 'notice');
				}

            	foreach ($this->items as $i => $item)
            	{
                	if (($item->asset_id == '0') AND ($item->state == '-2'))
                	{
						$editLink = 'index.php?option=com_icagenda&task=event.edit&id='.$item->id;
                		$msg	= '- '.$item->title.' ['.$item->id.'] : <a href="'.$editLink.'"><b>'.JText::_( 'JACTION_EDIT' ).'</b></a>';
                		$type	= JText::_( 'JGLOBAL_LIST' ).' :';
                	}
            		if (!empty($msg))
            		{
            			$app->enqueueMessage($msg, $type);
            		}
				}
			}
			?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</div>
</form>
<?php
}
else
{
	if (defined('IC_LIBRARY')) $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
	$app->redirect(htmlspecialchars_decode('index.php?option=com_icagenda&view=icagenda'));
}
