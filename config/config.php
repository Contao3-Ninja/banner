<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * 
 * Modul Banner Config - Backend
 *
 * This is the banner configuration file.
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer
 * @package    Banner 
 * @license    GPL 
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
	'icon'       => 'system/modules/banner/iconBanner.gif',
	'stylesheet' => 'system/modules/banner/mod_banner_be.css'
);

array_insert($GLOBALS['BE_MOD']['system'], 0, array
(
	'bannerstat' => array
	(
		'callback'   => 'ModuleBannerStat',
		'icon'       => 'system/modules/banner/iconBannerStat.gif',
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