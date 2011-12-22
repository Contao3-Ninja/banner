<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Banner Config - Backend
 *
 * This is the banner configuration file.
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2011
 * @author     Glen Langer
 * @package    Banner 
 * @license    GPL 
 * @filesource
 */


/**
 * -------------------------------------------------------------------------
 * BACK END MODULES
 * -------------------------------------------------------------------------
 *
 * Back end modules are stored in a global array called "BE_MOD". Each module 
 * has certain properties like an icon, an optional callback function and one 
 * or more tables. Each module belongs to a particular group.
 * 
 *   $GLOBALS['BE_MOD'] = array
 *   (
 *       'group_1' => array
 *       (
 *           'module_1' => array
 *           (
 *               'tables'       => array('table_1', 'table_2'),
 *               'key'          => array('Class', 'method'),
 *               'callback'     => 'ClassName',
 *               'icon'         => 'path/to/icon.gif',
 *               'stylesheet'   => 'path/to/stylesheet.css'
 *               'javascript'   => 'path/to/javascript.js'
 *           )
 *       )
 *   );
 * 
 * Use function array_insert() to modify an existing modules array.
 */
$GLOBALS['BE_MOD']['content']['banner'] = array
(
	'tables'     => array('tl_banner_category', 'tl_banner'),
	//'icon'       => ModuleBannerFile::BannerIcon('iconBanner.gif'),
	'icon'       => 'system/modules/banner/iconBanner.gif',
	//'stylesheet' => ModuleBannerFile::BannerCss('mod_banner_be.css')
	'stylesheet' => 'system/modules/banner/mod_banner_be.css'
);

array_insert($GLOBALS['BE_MOD']['system'], 0, array
(
	'bannerstat' => array
	(
		'callback'   => 'ModuleBannerStat',
		//'icon'       => ModuleBannerFile::BannerIcon('iconBannerStat.gif'),
		'icon'       => 'system/modules/banner/iconBannerStat.gif',
		//'stylesheet' => ModuleBannerFile::BannerCss('mod_banner_be.css')
		'stylesheet' => 'system/modules/banner/mod_banner_be.css'
	)
));

/**
 * -------------------------------------------------------------------------
 * FRONT END MODULES
 * -------------------------------------------------------------------------
 *
 * List all fontend modules and their class names.
 * 
 *   $GLOBALS['FE_MOD'] = array
 *   (
 *       'group_1' => array
 *       (
 *           'module_1' => 'Contentlass',
 *           'module_2' => 'Contentlass'
 *       )
 *   );
 * 
 * Use function array_insert() to modify an existing CTE array.
 */
array_insert($GLOBALS['FE_MOD']['miscellaneous'], 0, array
(
	'banner' => 'ModuleBanner')
);

?>