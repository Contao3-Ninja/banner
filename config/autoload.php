<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2012 Leo Feyer
 * 
 * @package Banner
 * @link    http://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'BugBuster',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Modules
	'BugBuster\Banner\ModuleBanner'        => 'system/modules/banner/modules/ModuleBanner.php',

	// Classes
	'BugBuster\Banner\BannerHelper'        => 'system/modules/banner/classes/BannerHelper.php',
	'BugBuster\Banner\DCA_banner'          => 'system/modules/banner/classes/DCA_banner.php',
	'BugBuster\Banner\BannerCheckHelper'   => 'system/modules/banner/classes/BannerCheckHelper.php',
	'BugBuster\Banner\BannerImage'         => 'system/modules/banner/classes/BannerImage.php',
	'BugBuster\Banner\DCA_banner_category' => 'system/modules/banner/classes/DCA_banner_category.php',
	'BugBuster\Banner\BannerReferrer'      => 'system/modules/banner/classes/BannerReferrer.php',
	'BugBuster\Banner\DCA_module_banner'   => 'system/modules/banner/classes/DCA_module_banner.php',
	
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
