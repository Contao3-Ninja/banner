<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Banner
 * @link    https://contao.org
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
	// Model
	'BannerCategoryModel'                               => 'system/modules/banner/model/BannerCategoryModel.php',
	'BannerModel'                                       => 'system/modules/banner/model/BannerModel.php',

	// Modules
	'BugBuster\Banner\ModuleBanner'                     => 'system/modules/banner/modules/ModuleBanner.php',
	'BugBuster\BannerStatistics\ModuleBannerStatistics' => 'system/modules/banner/modules/ModuleBannerStatistics.php',

	// Classes
	'BugBuster\Banner\BannerHelper'                     => 'system/modules/banner/classes/BannerHelper.php',
	'BugBuster\Banner\ModuleBannerTag'                  => 'system/modules/banner/classes/ModuleBannerTag.php',
	'BugBuster\Banner\DcaBanner'                        => 'system/modules/banner/classes/DcaBanner.php',
	'BugBuster\BannerStatistics\BannerStatisticsHelper' => 'system/modules/banner/classes/BannerStatisticsHelper.php',
	'BugBuster\Banner\BannerCheckHelper'                => 'system/modules/banner/classes/BannerCheckHelper.php',
	'BugBuster\Banner\BannerImage'                      => 'system/modules/banner/classes/BannerImage.php',
	'BugBuster\Banner\DcaBannerCategory'                => 'system/modules/banner/classes/DcaBannerCategory.php',
	'BugBuster\Banner\BannerReferrer'                   => 'system/modules/banner/classes/BannerReferrer.php',
	'BugBuster\Banner\DcaModuleBanner'                  => 'system/modules/banner/classes/DcaModuleBanner.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_banner_tag'      => 'system/modules/banner/templates',
	'mod_banner_empty'    => 'system/modules/banner/templates',
	'mod_banner_list_all' => 'system/modules/banner/templates',
	'mod_banner_list_min' => 'system/modules/banner/templates',
	'mod_banner_stat'     => 'system/modules/banner/templates',
));
