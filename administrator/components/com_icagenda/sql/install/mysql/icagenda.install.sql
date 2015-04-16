--
-- iCagenda: Install Database `icagenda`
--

-- --------------------------------------------------------

--
-- Table structure for table `#__icagenda`
--

CREATE TABLE IF NOT EXISTS `#__icagenda` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` VARCHAR(255) DEFAULT NULL,
  `releasedate` VARCHAR(255) DEFAULT NULL,
  `params` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Dumping data for table `#__icagenda`
--

INSERT IGNORE INTO `#__icagenda` (`id`, `version`, `releasedate`, `params`) VALUES
(3,'3.5.3','2015-03-25','');

-- --------------------------------------------------------

--
-- Table structure for table `#__icagenda_category`
--

CREATE TABLE IF NOT EXISTS `#__icagenda_category` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ordering` INT(11) NOT NULL,
  `state` TINYINT(1) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NOT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `color` VARCHAR(255) NOT NULL,
  `desc` TEXT(65535) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__icagenda_events`
--

CREATE TABLE IF NOT EXISTS `#__icagenda_events` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT(10) NOT NULL DEFAULT '0',
  `ordering` INT(11) NOT NULL,
  `state` TINYINT(1) NOT NULL DEFAULT '1',
  `approval` INT(11) NOT NULL DEFAULT '0',
  `site_itemid` INT(10) NOT NULL DEFAULT '0',
  `checked_out` INT(11) NOT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `access` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `language` CHAR(7) NOT NULL,
  `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_by_alias` VARCHAR(255) NOT NULL,
  `created_by_email` VARCHAR(100) NOT NULL,
  `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `username` VARCHAR(255) NOT NULL,
  `catid` INT(11) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `file` VARCHAR(255) NOT NULL,
  `displaytime` INT(10) NOT NULL DEFAULT '1',
  `weekdays` VARCHAR(255) NOT NULL,
  `daystime` VARCHAR(255) NOT NULL,
  `startdate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enddate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `period` TEXT(65535) NOT NULL,
  `dates` TEXT(65535) NOT NULL,
  `next` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `time` VARCHAR(255) NOT NULL,
  `place` VARCHAR(255) NOT NULL,
  `website` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `city` VARCHAR(255) NOT NULL,
  `country` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `coordinate` VARCHAR(255) NOT NULL,
  `lat` FLOAT( 20, 16 ) NOT NULL,
  `lng` FLOAT( 20, 16 ) NOT NULL,
  `shortdesc` TEXT NOT NULL,
  `desc` TEXT(65535) NOT NULL ,
  `metadesc` TEXT NOT NULL,
  `params` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__icagenda_registration`
--

CREATE TABLE IF NOT EXISTS `#__icagenda_registration` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ordering` INT(11) NOT NULL,
  `state` TINYINT(1) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NOT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userid` INT(11) NOT NULL,
  `itemid` INT(11) NOT NULL,
  `eventid` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(255) NOT NULL,
  `date` TEXT(65535) NOT NULL,
  `period` TINYINT(1) NOT NULL DEFAULT '0',
  `people` INT(2) NOT NULL,
  `notes` TEXT(65535) NOT NULL ,
  `custom_fields` TEXT NOT NULL ,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__icagenda_customfields`
--

CREATE TABLE IF NOT EXISTS `#__icagenda_customfields` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ordering` INT(11) NOT NULL,
  `state` TINYINT(1) NOT NULL DEFAULT '1',
  `checked_out` INT(11) NOT NULL,
  `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `parent_form` INT(11) NOT NULL DEFAULT '0',
  `type` VARCHAR(255) NOT NULL,
  `options` mediumtext,
  `default` VARCHAR(255) NOT NULL,
  `required` tinyint(3) NOT NULL DEFAULT '0',
  `language` varchar(10) NOT NULL DEFAULT '*',
  `params` mediumtext,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__icagenda_customfields_data`
--

CREATE TABLE IF NOT EXISTS `#__icagenda_customfields_data` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `state` TINYINT(1) NOT NULL DEFAULT '1',
  `slug` VARCHAR(255) NOT NULL,
  `parent_form` INT(11) NOT NULL DEFAULT '0',
  `parent_id` INT(11) NOT NULL DEFAULT '0',
  `value` VARCHAR(255) NOT NULL,
  `language` varchar(10) NOT NULL DEFAULT '*',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__icagenda_feature`
--

CREATE TABLE IF NOT EXISTS  `#__icagenda_feature` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `desc` mediumtext NOT NULL,
  `icon` varchar(255) NOT NULL,
  `icon_alt` varchar(255) NOT NULL,
  `show_filter` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__icagenda_feature_xref`
--

CREATE TABLE IF NOT EXISTS  `#__icagenda_feature_xref` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
