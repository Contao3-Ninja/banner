<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Banner Sprachdateien
 * 
 * Language file for table tl_banner (de).
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
$GLOBALS['TL_LANG']['tl_banner']['banner_type']      = array('Bannerquelle', 'Auswahl ob die Quelle der Bannerdatei intern oder extern ist, oder ob es ein Textbanner werden soll.');
$GLOBALS['TL_LANG']['tl_banner']['banner_name']      = array('Bannername', 'Bannername. Bei Textbanner: Überschrift');
$GLOBALS['TL_LANG']['tl_banner']['banner_url']       = array('Bannerziel-URL', 'Bannerziel-URL: http://... Als Alternative k&ouml;nnen Sie im n&auml;chsten Feld eine Zielseite ausw&auml;hlen.');
$GLOBALS['TL_LANG']['tl_banner']['banner_jumpTo']    = array('Bannerziel-Seite', 'Bitte w&auml;hlen Sie die Zielseite aus. Diese Auswahl hat Vorrang vor der direkten Bannerziel-URL.'); 
$GLOBALS['TL_LANG']['tl_banner']['banner_target']    = array('Interner Link', 'Wenn Sie diese Option w&auml;hlen, wird das Bannerziel im selben Browserfenster ge&ouml;ffnet.');
$GLOBALS['TL_LANG']['tl_banner']['banner_image']     = array('Bannerdatei', 'Bitte eine Bannerdatei ausw&auml;hlen.(GIF,JPG,PNG,SWF)');
$GLOBALS['TL_LANG']['tl_banner']['banner_image_extern'] = array('Bannerbild-URL', 'Externe Bannerbild-URL: http://... ');
$GLOBALS['TL_LANG']['tl_banner']['banner_imgSize']   = array('Bannerbreite und Bannerh&ouml;he', 'Geben Sie die Bannerbreite und/oder die Bannerh&ouml;he in Pixel ein, um die Bannergr&ouml;&szlig;e anzupassen. Wenn Sie keine Angaben machen, wird das Banner in seiner Originalgr&ouml;&szlig;e angezeigt.<br />ACHTUNG: Animierte GIFs werden bei Gr&ouml;&szlig;en-Angaben durch die GD-Neuberechnung leider zum Standbild.');
$GLOBALS['TL_LANG']['tl_banner']['banner_comment']   = array('Bannerkommentar', 'Erste Zeile erscheint als title Tag. Bei Textbanner: Textzeile');
$GLOBALS['TL_LANG']['tl_banner']['banner_weighting'] = array('Bannergewichtung', 'Bitte die Priorit&auml;t ausw&auml;hlen.');
$GLOBALS['TL_LANG']['tl_banner']['banner_published'] = array('Ver&ouml;ffentlicht', 'Solange Sie diese Option nicht w&auml;hlen, wird dieser Banner nicht f&uuml;r die Anzeige ber&uuml;cksichtigt.');
$GLOBALS['TL_LANG']['tl_banner']['banner_start']     = array('Anzeigen ab' , 'Wenn Sie hier Datum mit Uhrzeit erfassen, wird der Banner erst ab diesem Zeitpunkt angezeigt.');
$GLOBALS['TL_LANG']['tl_banner']['banner_stop']      = array('Anzeigen bis', 'Wenn Sie hier Datum mit Uhrzeit erfassen, wird der Banner nur bis zu diesem Zeitpunkt angezeigt.');
$GLOBALS['TL_LANG']['tl_banner']['banner_until']     = array('Begrenzung der Views und Klicks', 'Wenn Sie diese Option w&auml;hlen, k&ouml;nnen Sie eine maximale View Anzahl und/oder maximale Klick Anzahl definieren.');
$GLOBALS['TL_LANG']['tl_banner']['banner_views_until']  = array('Begrenzung der Views', 'Wenn Sie hier eine Zahl eingeben, wird dieser Banner nach dieser Anzahl von Views nicht mehr angezeigt.');
$GLOBALS['TL_LANG']['tl_banner']['banner_clicks_until'] = array('Begrenzung der Klicks', 'Wenn Sie hier eine Zahl eingeben, wird dieser Banner nach dieser Anzahl von Klicks nicht mehr angezeigt.');
$GLOBALS['TL_LANG']['tl_banner']['banner_domain']       = array('Domain Filter', 'Wenn Sie hier eine Domain eingeben, wird dieser Banner nur f&uuml;r diese Domain genutzt. Ohne Angabe wird dieser Banner f&uuml;r alle Domains genutzt.');
$GLOBALS['TL_LANG']['tl_banner']['banner_cat_not_found'] = 'Keine Kategorie im Banner Modul ausgew&auml;hlt.';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_banner']['title_legend']       = 'Name und Gewichtung';
$GLOBALS['TL_LANG']['tl_banner']['destination_legend'] = 'Bannerziele';
$GLOBALS['TL_LANG']['tl_banner']['image_legend']       = 'Bannerdatei';
$GLOBALS['TL_LANG']['tl_banner']['comment_legend']     = 'Kommentar';
$GLOBALS['TL_LANG']['tl_banner']['publish_legend']     = 'Ver&ouml;ffentlichung';
$GLOBALS['TL_LANG']['tl_banner']['filter_legend']      = 'Filterung';

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_banner']['1'] = 'H&ouml;chste Priorit&auml;t';
$GLOBALS['TL_LANG']['tl_banner']['2'] = 'Normale Priorit&auml;t';
$GLOBALS['TL_LANG']['tl_banner']['3'] = 'Niedrigste Priorit&auml;t';

$GLOBALS['TL_LANG']['tl_banner_type']['default']             = 'Bitte ausw&auml;hlen';
$GLOBALS['TL_LANG']['tl_banner_type']['banner_image']        = 'Interne Bannergrafik';
$GLOBALS['TL_LANG']['tl_banner_type']['banner_image_extern'] = 'Externe Bannergrafik';
$GLOBALS['TL_LANG']['tl_banner_type']['banner_text']         = 'Textbanner';

/**
 * Banner Overview
 */
$GLOBALS['TL_LANG']['tl_banner']['tl_be_start']  = 'Anzeigen ab';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_stop']   = 'Anzeigen bis';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_yes']    = 'ja';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_no']     = 'nein';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_start']  = 'sofort';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_stop']   = 'immer';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_views']       = 'max. Ansichten';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_clicks']      = 'max. Klicks';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] = 'unbegrenzt';
$GLOBALS['TL_LANG']['tl_banner']['tl_be_read_error']      = 'Bannerdatei nicht gefunden oder Lesefehler!';
$GLOBALS['TL_LANG']['tl_banner']['source_intern']      = 'intern';
$GLOBALS['TL_LANG']['tl_banner']['source_extern']      = 'extern';
$GLOBALS['TL_LANG']['tl_banner']['source_fallback']    = 'Bild-Fallback';
$GLOBALS['TL_LANG']['tl_banner']['source_fallback_no'] = 'Image-Fallback nicht gefunden.';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_banner']['new']    = array('Banner neu', 'Einen neuen Banner anlegen');
$GLOBALS['TL_LANG']['tl_banner']['edit']   = array('Banner editieren', 'Banner ID %s editieren');
$GLOBALS['TL_LANG']['tl_banner']['copy']   = array('Banner kopieren', 'Banner ID %s kopieren');
$GLOBALS['TL_LANG']['tl_banner']['delete'] = array('Banner l&ouml;schen', 'Banner ID %s l&ouml;schen');
$GLOBALS['TL_LANG']['tl_banner']['show']   = array('Banner Details', 'Zeige Details von Banner ID %s');
$GLOBALS['TL_LANG']['tl_banner']['toggle'] = array('Banner ein- oder ausschalten', 'Banner ID %s ein- oder ausschalten');

?>