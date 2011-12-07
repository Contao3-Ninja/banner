<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Banner Sprachdateien
 * 
 * Language file for table tl_banner_category (en).
 *
 * PHP version 5
 * @copyright  Glen Langer 2008..2010
 * @author     Glen Langer
 * @package    BannerLanguage
 * @license    GPL
 * @filesource
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_banner_category']['title']            = array('Category', 'Please enter the name of the category.');
$GLOBALS['TL_LANG']['tl_banner_category']['tstamp']           = array('Revision date', 'Date and time of latest revision');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_default']       = array('Default banner image', 'This banner will be showed, when no active banners are found.<br />This selection takes priority over modul definition "Hide when empty".');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_name']  = array('Banner Name', 'Name is for title tag too.');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_image'] = array('Banner default image', 'Please select the banner.(GIF,JPG,PNG,SWF)');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_url']   = array('Banner target URL', 'Please enter the banner target URL: http://... ');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_target']= array('Internal Link', 'If you choose this option, the banner target opens in the same browser window.');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_protected']     = array('Protect category', 'Show banner of this category to certain member groups only.');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_groups']        = array('Allowed member groups', 'Here you can choose which groups will be allowed to see the banners of this category.');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_numbers']       = array('Show all banners', 'If you choose this option, all active banners will be showed in frontend.');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_random']        = array('Random order', 'If you choose this option, the banners will shown in a random order.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_banner_category']['title_legend']     = 'Category and Banner Template'; 
$GLOBALS['TL_LANG']['tl_banner_category']['default_legend']   = 'Details for default banner'; 
$GLOBALS['TL_LANG']['tl_banner_category']['protected_legend'] = 'Access protection'; 
$GLOBALS['TL_LANG']['tl_banner_category']['number_legend']    = 'Number of banners'; 

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_banner_category']['deleteConfirm'] = 'Deleting a category will also delete all its Banners! Do you really want to delete category ID %s?';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_banner_category']['new']    = array('New category', 'Create a new category');
$GLOBALS['TL_LANG']['tl_banner_category']['edit']   = array('Edit category', 'Edit category ID %s');
$GLOBALS['TL_LANG']['tl_banner_category']['copy']   = array('Copy category', 'Copy category ID %s');
$GLOBALS['TL_LANG']['tl_banner_category']['delete'] = array('Delete category', 'Delete category ID %s');
$GLOBALS['TL_LANG']['tl_banner_category']['show']   = array('Category details', 'Show details of category ID %s');
$GLOBALS['TL_LANG']['tl_banner_category']['stat']   = array('Categorie statistics', 'Show categorie statistics of category ID %s');

/**
 * Icon
 */
$GLOBALS['TL_LANG']['tl_banner_category']['banner_protected_catagory'] = 'Protected category';

?>