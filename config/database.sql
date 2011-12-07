-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************


-- --------------------------------------------------------

-- 
-- Table `tl_banner`
-- 

CREATE TABLE `tl_banner` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0', 
  `banner_type` varchar(32) NOT NULL default 'banner_image',
  `banner_name` varchar(64) NOT NULL default '', 
  `banner_url` varchar(255) NOT NULL default '', 
  `banner_jumpTo` int(10) unsigned NOT NULL default '0', 
  `banner_target` char(1) NOT NULL default '', 
  `banner_image` varchar(255) NOT NULL default '', 
  `banner_image_extern` varchar(255) NOT NULL default '',
  `banner_imgSize` varchar(255) NOT NULL default '', 
  `banner_comment` text NOT NULL, 
  `banner_weighting` tinyint(3) unsigned NOT NULL default '2', 
  `banner_published` char(1) NOT NULL default '', 
  `banner_start` varchar(10) NOT NULL default '',
  `banner_stop` varchar(10) NOT NULL default '',
  `banner_until` char(1) NOT NULL default '',
  `banner_views_until` varchar(10) NOT NULL default '', 
  `banner_clicks_until` varchar(10) NOT NULL default '', 
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Table `tl_banner_category`
-- 

CREATE TABLE `tl_banner_category` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `title` varchar(60) NOT NULL default '', 
  `banner_template` varchar(32) NOT NULL default '', 
  `banner_default` char(1) NOT NULL default '', 
  `banner_default_name` varchar(64) NOT NULL default '', 
  `banner_default_image` varchar(255) NOT NULL default '', 
  `banner_default_url` varchar(128) NOT NULL default '', 
  `banner_default_target` char(1) NOT NULL default '', 
  `banner_numbers` char(1) NOT NULL default '', 
  `banner_random` char(1) NOT NULL default '', 
  `banner_protected` char(1) NOT NULL default '',
  `banner_groups` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Table `tl_banner_stat`
-- 

CREATE TABLE `tl_banner_stat` (
  `id` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `banner_views` int(10) unsigned NOT NULL default '0',
  `banner_clicks` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Table `tl_banner_blocker`
-- 

CREATE TABLE `tl_banner_blocker` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `bid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `ip` varchar(40) NOT NULL default '0.0.0.0',
  `type` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`), 
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- 
-- Table `tl_module`
-- 
CREATE TABLE `tl_module` (
  `banner_hideempty` char(1) NOT NULL default '0',
  `banner_categories` varchar(255) NOT NULL default '',
  `banner_template` varchar(32) NOT NULL default '',
  `banner_redirect` varchar(32) NOT NULL default '',
  `banner_useragent` varchar(64) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 
