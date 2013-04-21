<?php 
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Banner Sprachdateien
 * 
 * Language file for table tl_banner (en).
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2010
 * @author     Glen Langer
 * @package    BannerLanguage 
 * @license    GPL 
 * @filesource
 */


/**
 * Fields
 */
//$GLOBALS['TL_LANG']['tl_banner'][''] = array('', '');
$GLOBALS['TL_LANG']['tl_banner']['banner_type']      = array('Banner Source', 'Selection whether the banner file of internally or externally will be used or is it a text banner.');
$GLOBALS['TL_LANG']['tl_banner']['banner_name']      = array('Banner Name', 'Please enter the banner name. For text banner: title.');
$GLOBALS['TL_LANG']['tl_banner']['banner_url']       = array('Banner target URL', 'Please enter the banner target URL: http://... ');
$GLOBALS['TL_LANG']['tl_banner']['banner_jumpTo']    = array('Banner target page', 'Please select the target page from the page tree. This selection takes priority over the banner target URL.'); 
$GLOBALS['TL_LANG']['tl_banner']['banner_target']    = array('Internal Link', 'If you choose this option, the banner target opens in the same browser window.');
$GLOBALS['TL_LANG']['tl_banner']['banner_image']     = array('Banner Image', 'Please select the banner.(GIF,JPG,PNG,SWF)');
$GLOBALS['TL_LANG']['tl_banner']['banner_image_extern'] = array('Banner Image URL', 'External banner image URL: http://... ');
$GLOBALS['TL_LANG']['tl_banner']['banner_imgSize']   = array('Banner width and height', 'If you enter only width or only height (in pixel), the banner will be resized proportionally. If you enter both measures, the banner will be cropped if necessary. If you leave both fields blank, the original size will be displayed.<br />NOTE: Animated GIFs, with data sizes, the GD-recalculation outcome of this is a still picture.');
$GLOBALS['TL_LANG']['tl_banner']['banner_comment']   = array('Banner Comment', 'Comment is for title tag. For text banner: text line.');
$GLOBALS['TL_LANG']['tl_banner']['banner_weighting'] = array('Banner Weighting', 'Please select the priority.');
$GLOBALS['TL_LANG']['tl_banner']['banner_published'] = array('Published', 'Unless you choose this option, this banner is not considered to show.');
$GLOBALS['TL_LANG']['tl_banner']['banner_start']     = array('Show from' , 'If you enter a date and time value here the current banner will not be shown on the website before this moment.');
$GLOBALS['TL_LANG']['tl_banner']['banner_stop']      = array('Show until', 'If you enter a date and time value here the current banner will not be shown on the website after this moment.');
$GLOBALS['TL_LANG']['tl_banner']['banner_until']     = array('Limiting the number of Views and Clicks', 'If you select this option, you can define a maximum number of views and/or maximum number of clicks.');
$GLOBALS['TL_LANG']['tl_banner']['banner_views_until']  = array('Limiting the number of Views',  'By entering a number, the current banner is no longer displayed after this number of views.');
$GLOBALS['TL_LANG']['tl_banner']['banner_clicks_until'] = array('Limiting the number of Clicks', 'By entering a number, the current banner is no longer displayed after this number of clicks.');
$GLOBALS['TL_LANG']['tl_banner']['banner_domain']    = array('Filter Domain', 'If you enter a domain name, this banner is used only for this domain. If you leave the field empty, this banner is used for all domains.');
$GLOBALS['TL_LANG']['tl_banner']['banner_cat_not_found'] = 'No category selected in banner modul.';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_banner']['title_legend']       = 'Name and weighting';
$GLOBALS['TL_LANG']['tl_banner']['destination_legend'] = 'Banner targets';
$GLOBALS['TL_LANG']['tl_banner']['image_legend']       = 'Banner image';
$GLOBALS['TL_LANG']['tl_banner']['comment_legend']     = 'Comment';
$GLOBALS['TL_LANG']['tl_banner']['publish_legend']     = 'Publish settings';
$GLOBALS['TL_LANG']['tl_banner']['filter_legend']      = 'Filtering';

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_banner']['1'] = 'Highest priority';
$GLOBALS['TL_LANG']['tl_banner']['2'] = 'Normal priority';
$GLOBALS['TL_LANG']['tl_banner']['3'] = 'Lowest priority';

$GLOBALS['TL_LANG']['tl_banner_type']['default']             = 'Please select';
$GLOBALS['TL_LANG']['tl_banner_type']['banner_image']        = 'Internal banner image';
$GLOBALS['TL_LANG']['tl_banner_type']['banner_image_extern'] = 'External banner image';
$GLOBALS['TL_LANG']['tl_banner_type']['banner_text']         = 'Text banner';

/**
 * Banner Overview
 */
$GLOBALS['TL_LANG']['tl_banner']['tl_be_start']  = 'Show from';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_stop']   = 'Show until';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_yes']    = 'yes';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_no']     = 'no';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_start']  = 'immediately';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_stop']   = 'always';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_views']       = 'max. Views';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_clicks']      = 'max. Clicks';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] = 'unlimited';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_read_error']      = 'Banner file not found or read error!';
$GLOBALS['TL_LANG']['tl_banner']['source_intern']      = 'internal';
$GLOBALS['TL_LANG']['tl_banner']['source_extern']      = 'external';
$GLOBALS['TL_LANG']['tl_banner']['source_fallback']    = 'Image-Fallback';
$GLOBALS['TL_LANG']['tl_banner']['source_fallback_no'] = 'Image-Fallback not found.';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_banner']['new']    = array('New Banner', 'Add a new banner');
$GLOBALS['TL_LANG']['tl_banner']['edit']   = array('Edit Banner', 'Edit banner ID %s');
$GLOBALS['TL_LANG']['tl_banner']['copy']   = array('Copy Banner', 'Copy banner ID %s');
$GLOBALS['TL_LANG']['tl_banner']['delete'] = array('Delete Banner', 'Delete banner ID %s');
$GLOBALS['TL_LANG']['tl_banner']['show']   = array('Banner Details', 'Show details of Banner ID %s');
$GLOBALS['TL_LANG']['tl_banner']['toggle'] = array('Toggle visibility', 'Toggle the visibility of banner ID %s');

?>