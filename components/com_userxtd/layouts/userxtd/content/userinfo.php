<?php
/**
 * Part of joomla330 project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die;

/**
 * @var \Windwalker\View\Layout\FileLayout $this
 * @var JRegistry                          $params
 * @var JUser                              $user
 */

$params = $displayData['params'];

$title_field = $params->get('UserInfo_TitleField', 'name');
$about_field = $params->get('UserInfo_AboutField', 'BASIC_ABOUT');
$usersArticles = $params->get('UserInfo_UsersArticle_Link', 1);
$allowGuestSee = $params->get('UserProfile_GuestSeeProfile', 1);

$website_link = $displayData['website_link'];
$image = $displayData['image'];
$user  = $displayData['user'];
$link  = $displayData['link'];
?>
<!-- UserXTD Information Box -->
<div class="ux-user-info-warp well">
	<div class="ux-user-inner row-fluid">
		<div class="ux-user-left">
			<div class="ux-user-left-inner">
				<img src="<?php echo $this->escape($image); ?>" alt="<?php echo $this->escape($user->get($title_field)); ?>" />
			</div>
		</div>
		<div class="ux-user-right">
			<div class="ux-user-right-inner">
				<h2 class="ux-user-info-heading">
					<?php if ($usersArticles): ?>
						<a href="<?php echo Userxtd\Router\Route::_('user_content', array('username' => $user->username)) ?>">
							<?php echo $this->escape($user->get($title_field)); ?>
						</a>
					<?php else: ?>
						<?php echo $this->escape($user->get($title_field)); ?>
					<?php endif; ?>
				</h2>
				<div class="ux-user-about">
					<?php echo nl2br($this->escape($user->get($about_field))); ?>
				</div>
			</div>
		</div>
		<div class="pull-right more-about">
			<?php if ($website_link): ?>
			<a href="<?php echo $this->escape($website_link); ?>" target="_blank">
				<?php echo \JText::_('COM_USERXTD_USER_INFO_WEBSITE'); ?>
			</a>
			|
			<?php endif; ?>

			<?php if ($allowGuestSee): ?>
			<a href="<?php echo $this->escape($link) ?>">
				<?php echo \JText::_('COM_USERXTD_USER_INFO_MORE'); ?>
			</a>
			<?php endif; ?>
		</div>
	</div>
</div>
