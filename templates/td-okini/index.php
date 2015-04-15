<?php
/**
 * @subpackage	TD-Okini v3.0
 * @copyright	Copyright (C) 2013 TempoDesign - All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
JHTML::_('behavior.framework');

$BoxOn = ($this->countModules('position-9') or $this->countModules('position-10') or $this->countModules('position-11'));
$LeftMenuOn = ($this->countModules('position-4') or $this->countModules('position-5') or $this->countModules('position-7'));
$RightMenuOn = ($this->countModules('position-6') or $this->countModules('position-8'));

$app = JFactory::getApplication();
$sitename = $app->getCfg('sitename');

// Header Settings
$logopath = $this->baseurl . '/templates/' . $this->template . '/images/logo.gif';
$logo = $this->params->get('logo', $logopath);
$logoimage = $this->params->get('logoimage');
$sitetitle = $this->params->get('sitetitle');
$sitedescription = $this->params->get('sitedescription');

// Slideshow Settings
$slides = $this->params->get('slides');
$slideseffect = $this->params->get('slideseffect');
$slidesanimSpeed = $this->params->get('slidesanimSpeed');
$slidesinterval = $this->params->get('slidesinterval');
$slidesheight = $this->params->get('slidesheight');
$slidesfolder = $this->params->get('slidesfolder','-1');
if ($slides == "0") {
	// Show Slider on all pages
	$slideson = TRUE;
} elseif ($slides == "1") {
	// Show Slider on homepage only
	$menu = $app->getMenu();
	$lang = JFactory::getLanguage();
	if ($menu->getActive() == $menu->getDefault($lang->getTag())) {
		$slideson = TRUE;
	} else {
		// Hide Slider
		$slideson = FALSE;
	}
} else {
	// Hide Slider
	$slideson = FALSE;
}
if ($slidesfolder != '-1' && $slideson) {
	// Use images from Media Manager
	$slidesdir = "images/" . $slidesfolder . "/";
} elseif ($slideson) {
	// Use default image folder
	$slidesdir = 'templates/' . $this->template . '/images/slides/';
}
if ($slideson) {
	if (file_exists($slidesdir) && is_readable($slidesdir)) {
		$images = preg_grep("/(jpg|jpeg|gif|png|JPG|JPEG|GIF|PNG)$/i",glob($slidesdir . "*"));
	} else {
		echo '<span style="color:yellow;background-color:red">&nbsp; - Slideshow image folder not found! - &nbsp;</span>';
	}
}


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
	<meta name="viewport" content="width=1068">
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/general.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
	<link href='//fonts.googleapis.com/css?family=Open+Sans:400italic,400,700' rel='stylesheet' type='text/css' />
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/js/sfhover.js"></script>
	<?php if ($slideson): ?>
	<!-- Slides Scripts -->	
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/js/NivooSlider.js"></script>
	<script type="text/javascript">
		window.addEvent('domready', function () {
     		new NivooSlider($('Slider'), {
     			animSpeed: <?php echo $slidesanimSpeed; ?>,
     			interval: <?php echo $slidesinterval; ?>,
     			controlNavItem: 'disc', 
     			effect: '<?php echo $slideseffect; ?>',
     			directionNavHide: true
     		});
		});
	</script>
	<style type="text/css">
		#slideshow {
			min-width: 1068px;
			background: url('<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/images/slide_shadow.png') no-repeat center bottom;
			background-color: #000;
			margin-bottom: -1px;
		}

		#Slider {
			padding: 0 0 40px 0;
			clear: both;
			position:relative;
			width:1000px;
			margin: auto;
		}

		.nivoo-slider,
		.nivoo-slider-holder {
    		height: <?php echo $slidesheight; ?>px;
    		overflow: hidden;
    		position: relative;
    		width: 1000px;
		}

		.nivoo-slider.got-control-nav {
    		height: <?php echo $slidesheight; ?>px;
		}
	</style>
	<?php else: ?>
	<style type="text/css">
		#slideshow {
			min-width: 1068px;
			background-color: #fff;
			margin-bottom: -1px;
		}

		#Slider {
			clear: both;
			position:relative;
			width:1000px;
			margin: auto;
			overflow: hidden;
		}
	</style>
	<?php endif; ?>	
</head>
<body>

<div id="wrapper">

	<div id="header_wrap">
		<div id="header">

			<!-- LOGO -->
			<div id="logo">

			<?php if ($logo && $logoimage == 1): ?>
				<a href="<?php echo $this->baseurl ?>"><img src="<?php echo htmlspecialchars($logo); ?>"  alt="<?php echo $sitename; ?>" /></a>
			<?php endif; ?>
			<?php if (!$logo || $logoimage == 0): ?>

				<?php if ($sitetitle): ?>
					<a href="<?php echo $this->baseurl ?>"><?php echo htmlspecialchars($sitetitle); ?></a><br/>
				<?php endif; ?>

				<?php if ($sitedescription): ?>
					<div class="sitedescription"><?php echo htmlspecialchars($sitedescription); ?></div>
				<?php endif; ?>

			<?php endif; ?>

	  		</div>

	  		<!-- SEARCH -->
			<div id="search">		
				<jdoc:include type="modules" name="position-0" />
			</div>

			<!-- TOPMENU -->
			<div id="topmenu">
				<jdoc:include type="modules" name="position-1" />
			</div>
		</div>
	</div>
	


	<!-- SLIDESHOW -->
	<?php if ($slideson): ?>
	<div id="slideshow">
		<div id="Slider" class="nivoo-slider">
			<?php
			foreach ($images as $image) {
				echo '<img src="' . $image . '" alt="" />';
			}
			?>
		</div>
	</div>
	<?php endif; ?>	
	

	<!-- NO SLIDESHOW -->
	<?php if (!$slideson && $this->countModules('position-15') ): ?>
	<div id="slideshow">
		<div id="Slider">
			<jdoc:include type="modules" name="position-15" />
		</div>
	</div>
	<?php endif; ?>
	


	<!-- CONTENT/MENU WRAP -->
	<div id="content-menu_wrap_bg">
	<div id="content-menu_wrap">
		
		

		<!-- BREADCRUMBS -->
		<?php if ($this->countModules('position-2')): ?>
		<div id="breadcrumbs">
			<jdoc:include type="modules" name="position-2" />
		</div>
		<?php endif; ?>


		<!-- LEFT MENU -->
		<?php if($LeftMenuOn ): ?>
		<div id="leftmenu">
			<jdoc:include type="modules" name="position-7" style="xhtml" />
			<jdoc:include type="modules" name="position-4" style="xhtml" />
			<jdoc:include type="modules" name="position-5" style="xhtml" />
		</div>
		<?php endif; ?>


		<!-- CONTENTS -->
		<?php if($LeftMenuOn AND $RightMenuOn): ?>
		<div id="content-w1">
		<?php elseif($LeftMenuOn OR $RightMenuOn): ?>
		<div id="content-w2">	
		<?php else: ?>
		<div id="content-w3">	
		<?php endif; ?>
		
			<?php if ($this->countModules('position-12')): ?>
			<div id="content-top">
				<jdoc:include type="modules" name="position-12" />
			</div>
			<?php endif; ?>

			<jdoc:include type="message" />
			<jdoc:include type="component" />
		</div>


		<!-- RIGHT MENU -->
		<?php if($RightMenuOn ): ?>
		<div id="rightmenu">
			<jdoc:include type="modules" name="position-6" style="xhtml" />
			<jdoc:include type="modules" name="position-8" style="xhtml" />
			<jdoc:include type="modules" name="position-3" style="xhtml" />
		</div>
		<?php endif; ?>


	</div>
	</div>


	<!-- FOOTER -->
	<div id="footer_wrap">
		<div id="footer">
			<jdoc:include type="modules" name="position-14" />
		</div>
	</div>	

	
	<!-- BANNER/LINKS -->
	<?php if($BoxOn ): ?>
	<div id="box_wrap">
		<div id="box_placeholder">
			<div id="box1"><jdoc:include type="modules" name="position-9" style="xhtml" /></div>
			<div id="box2"><jdoc:include type="modules" name="position-10" style="xhtml" /></div>
			<div id="box3"><jdoc:include type="modules" name="position-11" style="xhtml" /></div>
		</div>
	</div>
	<?php endif; ?>
	

</div>


<!-- PAGE END -->









<div id="copyright_wrap">
	<div id="copyright">
		&copy;<?php echo date('Y'); ?> <?php echo $sitename; ?> | Designed by <a href="http://www.tempodesign.dk">TempoDesign</a><br/>&nbsp;
	</div>
</div>


</body>
</html>