<?php 
/**
<<<<<<< HEAD
 * Contao Open Source CMS, Copyright (C) 2005-2013 Leo Feyer
 * 
=======
 * Contao Open Source CMS, Copyright (C) 2005-2012 Leo Feyer
 *
>>>>>>> c3v3.1
 * Modul Banner Config - Backend
 *
 * This is the banner configuration file.
 *
<<<<<<< HEAD
 * @copyright	Glen Langer 2007..2013 <http://www.contao.glen-langer.de>
 * @author      Glen Langer (BugBuster)
 * @package     Banner 
 * @license     GPL 
 * @filesource
=======
 * @copyright  Glen Langer 2007..2013
 * @author     Glen Langer
 * @package    Banner 
 * @license    LGPL 
>>>>>>> c3v3.1
 */

define('BANNER_VERSION', '3.1');
define('BANNER_BUILD'  , '0');

/**
 * -------------------------------------------------------------------------
 * BACK END MODULES
 * -------------------------------------------------------------------------
 */
$GLOBALS['BE_MOD']['content']['banner'] = array
(
	'tables'     => array('tl_banner_category', 'tl_banner'),
	'icon'       => 'system/modules/banner/assets/iconBanner.gif',
	'stylesheet' => 'system/modules/banner/assets/mod_banner_be.css'
);

$GLOBALS['BE_MOD']['system']['bannerstat'] = array
(
	'callback'   => 'BannerStatistics\ModuleBannerStatistics',
	'icon'       => 'system/modules/banner/assets/iconBannerStat.gif',
	'stylesheet' => 'system/modules/banner/assets/mod_banner_be.css'
);

/**
 * -------------------------------------------------------------------------
 * FRONT END MODULES
 * -------------------------------------------------------------------------
 */
$GLOBALS['FE_MOD']['miscellaneous']['banner'] = '\Banner\ModuleBanner';

/**
 * -------------------------------------------------------------------------
 * HOOKS
 * -------------------------------------------------------------------------
 */
$GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array('Banner\BannerCheckHelper', 'checkExtensions');

