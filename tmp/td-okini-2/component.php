<?php
/**
 * @subpackage	TD-Okini v3.0
 * @copyright	Copyright (C) 2013 TempoDesign - All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/general.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css' />
</head>
<body class="contentpane">
	<div id="all">
		<div id="main">
			<jdoc:include type="message" />
			<jdoc:include type="component" />
		</div>
	</div>
</body>
</html>
