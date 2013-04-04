<?php 
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * 
 * Modul Banner - Backend DCA tl_banner_blocker
 * 
 * This is the data container array for table tl_banner_blocker.
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2013
 * @author     Glen Langer
 * @package    Banner
 * @license    LGPL
 */

/**
 * Table tl_banner_blocker
 */
$GLOBALS['TL_DCA']['tl_banner_blocker'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
        'sql' => array
        (
            'keys' => array
            (
                'id'    => 'primary',
                'type'  => 'index'
            )
        ),
	),
	// Fields
	'fields' => array
	(
    	'id' => array
    	(
    	        'sql'           => "int(10) unsigned NOT NULL auto_increment"
    	),
        'bid' => array
        (
                'sql'           => "int(10) unsigned NOT NULL default '0'"
        ),
    	'tstamp' => array
    	(
    	        'sql'           => "int(10) unsigned NOT NULL default '0'"
    	),
        'ip' => array
        (
                'sql'           => "varchar(40) NOT NULL default '0.0.0.0'"
        ),
        'type' => array
        (
                'sql'           => "char(1) NOT NULL default ''"
        )
	)
);


