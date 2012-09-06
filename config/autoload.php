<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2012 Leo Feyer
 * 
 * @package Banner
 * @link    http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'BugBuster\Banner',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Banner
	'BugBuster\Banner\ModuleBanner'         => 'system/modules/banner/ModuleBanner.php',
	'ModuleBannerFile'                      => 'system/modules/banner/ModuleBannerFile.php',
	'BugBuster\Banner\ModuleBannerReferrer' => 'system/modules/banner/ModuleBannerReferrer.php',
	'BugBuster\Banner\ModuleBannerStat'     => 'system/modules/banner/ModuleBannerStat.php',
	'ModuleBannerVersion'                   => 'system/modules/banner/ModuleBannerVersion.php',
	'banner_clicks'                         => 'system/modules/banner/banner_clicks.php',

	// Export
	'BannerStatExport'                      => 'system/modules/banner/export/BannerStatExport.php',
	'csv'                                   => 'system/modules/banner/export/csv.php',
	'excel'                                 => 'system/modules/banner/export/excel.php',
	'excel95'                               => 'system/modules/banner/export/excel95.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_banner_empty'    => 'system/modules/banner/templates',
	'mod_banner_list_all' => 'system/modules/banner/templates',
	'mod_banner_list_min' => 'system/modules/banner/templates',
	'mod_banner_stat'     => 'system/modules/banner/templates',
));
