<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Banner Sprachdateien
 * 
 * Language file for table tl_banner_category (de).
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
$GLOBALS['TL_LANG']['tl_banner_category']['title']            = array('Kategorie', 'Bitte geben Sie den Namen der Kategorie ein.');
$GLOBALS['TL_LANG']['tl_banner_category']['tstamp']           = array('&Auml;nderungsdatum', 'Datum und Uhrzeit der letzten &Auml;nderung');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_default']       = array('Banner Standarddatei', 'Wenn Sie diese Option w&auml;hlen, k&ouml;nnen Sie eine Bannerdatei ausw&auml;hlen die angezeigt wird, wenn kein Banner in der Kategorie aktiv ist. Eine Auswahl hat Vorrang vor der Modul Definition "Ausblenden, wenn leer".');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_name']  = array('Bannername', 'Bannername, erscheint auch als title Tag.');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_image'] = array('Banner Standarddatei', 'Bitte eine Bannerdatei ausw&auml;hlen.(GIF,JPG,PNG,SWF)');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_url']   = array('Bannerziel-URL', 'Bannerziel URL: http://...');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_target']= array('Interner Link', 'Wenn Sie diese Option w&auml;hlen, wird das Bannerziel im selben Browserfenster ge&ouml;ffnet.');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_protected']     = array('Kategorie schützen', 'Den Inhalt der Kategorie nur bestimmten Gruppen im Frontend anzeigen.');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_groups']        = array('Erlaubte Mitgliedergruppen', 'Hier können Sie festlegen, welche Mitgliedergruppen die Banner der Kategorie im Frontend sehen dürfen.');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_numbers']       = array('Alle Banner anzeigen', 'Wenn Sie diese Option w&auml;hlen, werden alle aktiven Banner der Kategorie angezeigt im Frontend. Die Anzahl kann nachfolgend limitiert werden.');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_random']        = array('Zuf&auml;llige Reihenfolge', 'Wenn Sie diese Option w&auml;hlen, werden die Banner zus&auml;tzlich in zuf&auml;lliger Reihenfolge angezeigt.');
$GLOBALS['TL_LANG']['tl_banner_category']['banner_limit']		  = array('Anzahl limitieren','0: alle Banner anzeigen (default), sonst maximale Anzahl der Banner die angezeigt werden.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_banner_category']['title_legend']     = 'Kategorie und Bannervorlage'; 
$GLOBALS['TL_LANG']['tl_banner_category']['default_legend']   = 'Angaben zum Defaultbanner'; 
$GLOBALS['TL_LANG']['tl_banner_category']['protected_legend'] = 'Zugriffsschutz'; 
$GLOBALS['TL_LANG']['tl_banner_category']['number_legend']    = 'Anzeige Definition'; 

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_banner_category']['deleteConfirm'] = 'Wenn Sie eine Kategorie l&ouml;schen werden auch alle darin enthaltenen Banner gel&ouml;scht. Wollen Sie die Kategorie ID %s wirklich l&ouml;schen?';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_banner_category']['new']    = array('Neue Kategorie', 'Eine neue Kategorie anlegen');
$GLOBALS['TL_LANG']['tl_banner_category']['edit']   = array('Kategorie bearbeiten', 'Kategorie ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_banner_category']['copy']   = array('Kategorie duplizieren', 'Kategorie ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_banner_category']['delete'] = array('Kategorie l&ouml;schen', 'Kategorie ID %s l&ouml;schen');
$GLOBALS['TL_LANG']['tl_banner_category']['show']   = array('Kategoriedetails', 'Details der Kategorie ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_banner_category']['stat']   = array('Kategoriestatistik', 'Bannerstatistik f&uuml;r die Kategorie ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_banner_category']['editheader'] = array('Einstellungen der Kategorie bearbeiten', 'Einstellungen der Kategorie ID %s bearbeiten');

/**
 * Icon
 */
$GLOBALS['TL_LANG']['tl_banner_category']['banner_protected_catagory'] = 'Kategorie geschützt';

?>