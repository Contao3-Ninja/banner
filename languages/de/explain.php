<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Banner Sprachdateien
 * 
 * Language file for explains (de).
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
		'Bannername',
		'Der Bannername wird in der Banner&uuml;bersicht '.
		'und im mitgelieferten Template unterhalb der Bannergrafik angezeigt.<br /><br />'.
		'Beim Textbanner ist dies die Überschrift die gleichzeitig mit der Ziel URL verlinkt wird.'
	),
	array
	(
		'Banner-URL',
		'Die URL des Zieles bei Klick auf die Bannergrafik.<br />'.
		'Die Eingabe muss inklusive http:// erfolgen, Beispiel:<br /><br />'.
		"<pre>\n".
		"   http://www.contao.org\n".
		'</pre><br />'.
		'Kein Pflichtfeld. Ohne URL wird die Bannergrafik ohne Verlinkung angezeigt.<br /><br />'.
		'Beim Textbanner wird die Domain als Kurzform angezeigt und verlinkt.'
	),
	array
	(
		'Bannergewichtung',
		'Je h&ouml;her die Gewichtung desto h&auml;ufiger wird dieser Werbebanner angezeigt.<br />'
	),
	array
	(
		'Bannerbild',
		'Aus dem Filesystem kann hier eine Grafik als Banner ausgew&auml;hlt werden.'
	),
	array
	(
		'Bannerkommentar',
		'Erste Zeile wird als "title" Tag im HTML Quellcode eingebaut und angezeigt, '.
		'wenn man den Mauszeiger &uuml;ber die Bannergrafik bewegt.<br /><br />'.
		'Beim Textbanner ist dies die Textzeile.'
	),
	array
	(
		'Anzeigen ab (Datum+Uhrzeit)',
		'Wenn Sie hier Datum mit Uhrzeit erfassen, wird dieser Werbebanner erst ab diesem Zeitpunkt angezeigt.'.
		'<br />'.
		'Die gesetzte Uhrzeit kann &uuml;berschrieben werden.<br />Beispiel:<br /><br />'.
		'<pre>'.
		'   24.12.2007 20:15'.
		'</pre><br />'
	),
	array
	(
		'Anzeigen bis (Datum+Uhrzeit)',
		'Wenn Sie hier Datum mit Uhrzeit erfassen, wird dieser Werbebanner nur bis zu diesem Zeitpunkt angezeigt.'.
		'<br />'.
		'Die gesetzte Uhrzeit kann &uuml;berschrieben werden.<br />Beispiel:<br /><br />'.
		'<pre>'.
		'   24.12.2007 23:59'.
		'</pre><br />'
	),
	array
	(
		'Begrenzung der Views',
		'Wenn Sie hier eine Zahl eingeben, wird dieser Werbebanner nach dieser Anzahl von Views nicht mehr angezeigt.'.
		'<br />'.
		'Die Option "Begrenzung der Views und Klicks" muss dabei aktiviert bleiben.<br />Beispiel:<br /><br />'.
		'<pre>'.
		'  1000'.
		'</pre><br />'
	),
	array
	(
		'Begrenzung der Klicks',
		'Wenn Sie hier eine Zahl eingeben, wird dieser Werbebanner nach dieser Anzahl von Klicks nicht mehr angezeigt.'.
		'<br />'.
		'Die Option "Begrenzung der Views und Klicks" muss dabei aktiviert bleiben.<br />Beispiel:<br /><br />'.
		'<pre>'.
		'   100'.
		'</pre><br />'
	),
	array
	(
		'HTTP_USER_AGENT Teilkennung',
		'Mit &Auml;nderung der Browserkennung durch ein eindeutigen String '.
		'und Eintragung in dieses Feld kann verhindert werden, '.
		'dass die eigenen Zugriffe mitgez&auml;hlt werden.<br />'.
		'Genaue Anleitung dazu sind im Wiki / Forum zu finden.'
	)
);

?>