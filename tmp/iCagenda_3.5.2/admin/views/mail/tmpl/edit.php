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
 * @version     3.2.13 2014-01-29
 * @since       1.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$app = JFactory::getApplication();

// Access Administration Newsletter check.
if (JFactory::getUser()->authorise('icagenda.access.newsletter', 'com_icagenda'))
{



?>


<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'event.cancel' || document.formvalidator.isValid(document.id('event-form'))) {
			Joomla.submitform(task, document.getElementById('event-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}

	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_icagenda&view=mail&layout=edit') ?>" method="post" name="adminForm" id="event-form" class="form-validate" enctype="multipart/form-data">
	<div class="container">
		<!-- iCagenda Header -->
		<header>
			<h1><?php echo JText::_('COM_ICAGENDA_TITLE_MAIL'); ?> <span>iCagenda</span></h1>
			<h2>
				<?php echo JText::_('COM_ICAGENDA_COMPONENT_DESC'); ?>
				<!--nav class="iCheader-videos">
					<span style="font-variant:small-caps">Tutorial Videos</span>
					<a href="#">Video</a>
				</nav-->
			</h2>
		</header>

		<div>&nbsp;</div>

		<!-- Begin Content -->
		<div class="row-fluid">
			<div class="span12">
				<div class="row-fluid">
					<div class="span10 iCleft">
						<div class="control-group">
							<?php echo $this->form->getLabel('subject'); ?>
							<div class="controls">
								<?php echo $this->form->getInput('subject'); ?>
							</div>
						</div>
						<div class="control-group">
							<?php echo $this->form->getLabel('message'); ?>
							<div class="controls">
								<?php echo $this->form->getInput('message'); ?>
							</div>
						</div>
					</div>
					<div class="span2 iCleft">
						<div class="control-group">
							<?php echo $this->form->getLabel('list'); ?>
							<div class="controls">
								<?php echo $this->form->getInput('list'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
</form>
<?php
}
else
{
	$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
	$app->redirect(htmlspecialchars_decode('index.php?option=com_icagenda&view=icagenda'));
}
?>
