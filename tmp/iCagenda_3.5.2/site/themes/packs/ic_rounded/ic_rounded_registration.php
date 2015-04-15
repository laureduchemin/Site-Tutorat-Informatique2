<?php
/**
 *------------------------------------------------------------------------------
 *  iCagenda v3 by Jooml!C - Events Management Extension for Joomla! 2.5 / 3.x
 *------------------------------------------------------------------------------
 * @package     com_icagenda
 * @copyright   Copyright (c)2012-2015 Cyril Rezé, Jooml!C - All rights reserved
 *
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Cyril Rezé (Lyr!C)
 * @link        http://www.joomlic.com
 *
 * @themepack	ic_rounded
 * @template	event_registration
 * @version 	3.4.1 2015-01-23
 * @since       2.0
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die(); ?>

<!--
 *
 * iCagenda by Jooml!C
 * ic_rounded Theme Pack
 *
 * @template	event_registration
 * @version 	3.5.2
 *
-->

<?php // Header of Registration page ?>
<?php // Show event ?>
<div class="ic-event ic-clearfix">
	<div class="ic-box">
		<?php if ($EVENT_NEXT): ?>
		<div class="ic-reg-icon ic-float-left ic-align-center">
			<img src="media/com_icagenda/images/registration-48.png" alt="">
		</div>
		<?php endif; ?>
		<div class="ic-content">

			<?php // Category ?>
			<div class="ic-reg-cat">
				<?php echo $CATEGORY_TITLE; ?>
			</div>

			<?php // Event Title with link to event ?>
			<h2>
				<a href="<?php echo $EVENT_URL; ?>" alt="<?php echo $EVENT_TITLE; ?>"><?php echo $EVENT_TITLE; ?></a>
			</h2>
			<?php // Add Registration infos (places left) ?>
			<?php if ($SEATS_AVAILABLE): ?>
			<div class="ic-reg-info">
				<?php echo JTEXT::_('COM_ICAGENDA_REGISTRATION_PLACES_LEFT');  ?>: <?php echo $SEATS_AVAILABLE; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php // END Header ?>
