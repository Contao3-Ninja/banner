<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Banner Sprachdateien
 * 
 * Language file for explains (en).
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2010
 * @author     Glen Langer
 * @package    BannerLanguage
 * @license    GPL
 * @filesource
 */

$GLOBALS['TL_LANG']['XPL']['banner_help'] = array
(
	array
	(
		'Banner Name',
		'The banner name is shown in the banners overview '.
		'and in the provided template below the banner image.<br /><br />'.
		'At text banners is it the banner title and linked with the destination URL.'
	),
	array
	(
		'Banner URL',
		'The URL of the destination with click on the banner image.<br />'.
		'The banner URL must start with http://, e.g.:<br /><br />'.
		"<pre>\n".
		"   http://www.typolight.org\n".
		'</pre><br/>'.
		'Using no URL, the banner image is shown without a link.<br /><br />'.
		'At text banners is the domain shown as a short form and linked with the destination URL.'
	),
	array
	(
		'Banner Weighting',
		'A banner with an higher weight value, is shown more frequently than a banner with a lower value.<br />'
	),
	array
	(
		'Banner Image',
		'An image file can be selected here as banner from the file system.'
	),
	array
	(
		'Banner Comment',
		'Comment is used for title tag in the HTML source code, and will be shown, '.
		'when moving the mouse over the banner image.<br /><br />'.
		'At text banners is this the text line.'
	),
	array
	(
		'Show from (Date & Time)',
		'By entering a date and time value (with datepicker), the current banner will not be shown on the website before this moment. '.
		'The time value can be changed manually. <br/>Example:<br/><br/>'.
		"<pre>\n".
		"   24.12.2007 20:15\n".
		'</pre><br />'
	),
	array
	(
		'Show until (Date & Time)',
		'By entering a date and time value (with datepicker) the current banner will not be shown on the website after this moment. '.
		'The time value can be changed manually. <br/>Example:<br/><br/>'.
		"<pre>\n".
		"   24.12.2007 23:59\n".
		'</pre><br />'
	),
	array
	(
		'Limiting the number of Views',
		'By entering a number, the current banner is no longer displayed after this number of views.'.
		'<br />'.
		'The option "Limiting the number of Views and Clicks" must remain activated.<br />Example:<br /><br />'.
		'<pre>'.
		'  1000'.
		'</pre><br />'
	),
	array
	(
		'Limiting the number of Clicks',
		'By entering a number, the current banner is no longer displayed after this number of clicks.'.
		'<br />'.
		'The option "Limiting the number of Views and Clicks" must remain activated.<br />Example:<br /><br />'.
		'<pre>'.
		'   100'.
		'</pre><br />'
	),
	array
	(
		'HTTP_USER_AGENT partial identifier',
		'With the change of a user agent with a unique string '.
		'and entry in this field can be prevented, '.
		'that will count your own requests.<br />'.
		'Detailed instructions, can be found in the Wiki.'
	)
);

?>