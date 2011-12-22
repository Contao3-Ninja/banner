<?php @error_reporting(0); @ini_set("display_errors", 0);  
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * Modul Banner - /config/runonce.php
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2011
 * @author     Glen Langer
 * @package    Banner
 * @license    GPL
 */

/**
 * Class BannerRunonceJob
 *
 * @copyright  Glen Langer 2007..2011
 * @author     Glen Langer
 * @package    Banner
 * @license    GPL
 */
class BannerRunonceJob extends Controller
{
	public function __construct()
	{
	    parent::__construct();
	    $this->import('Database');
	}
	public function run()
	{
		//nur ab Contao 2.9
		if (version_compare(VERSION, '2.8', '>'))
		{
			if ($this->Database->tableExists('tl_banner_category'))
			{
			    if ($this->Database->fieldExists('banner_template', 'tl_banner_category')
			    && !$this->Database->fieldExists('banner_template', 'tl_module'))
			    {
			    	//Migration mit Neufeldanlegung
			    	//Feld anlegen
			    	$this->Database->execute("ALTER TABLE `tl_module` ADD `banner_template` varchar(32) NOT NULL default ''");
			    	$addTemplate = true;
			    }
			    
			} // if tableExists('tl_banner_category')
			
			if ( ($this->Database->fieldExists('banner_template', 'tl_banner_category')
			   && $this->Database->fieldExists('banner_template', 'tl_module')) 
			   || $addTemplate === true)
			{
				$objTemplates = $this->Database->execute("SELECT count(banner_template) AS ANZ FROM tl_module WHERE banner_template !=''");
				while ($objTemplates->next())
				{
				    if ($objTemplates->ANZ > 0) 
				    {
				        //nicht gefuellt
				        $migration = false;
				    } 
				    else 
				    {
				        $migration = true;
				    }
				}
				
				if ($migration == true)
				{
				    //Feld versuchen zu fuellen
				    $objBannerTemplatesNew = $this->Database->execute("SELECT `id`, `name` , `banner_categories` FROM `tl_module` WHERE `type`='banner'");
				    
				    while ($objBannerTemplatesNew->next())
				    {
				        if (strpos($objBannerTemplatesNew->banner_categories,':') !== false)
				        {
				            $arrKat = deserialize($objBannerTemplatesNew->banner_categories,true);
				        } 
				        else 
				        {
				            $arrKat = array($objBannerTemplatesNew->banner_categories);
				        }
				        if (count($arrKat) == 1 && (int)$arrKat[0] >0) 
				        {	//nicht NULL
				            //eine eindeutige Zuordnung, kann eindeutig migriert werden
				            $objTemplatesOld = $this->Database->execute("SELECT `id`, `title`, `banner_template` FROM `tl_banner_category` WHERE id =".$arrKat[0]."");
				            
				            while ($objTemplatesOld->next())
				            {
				                $this->Database->prepare("UPDATE tl_module SET banner_template=? WHERE id=?")->execute($objTemplatesOld->banner_template, $objBannerTemplatesNew->id);
				                //Protokoll
				                $strText = 'Banner-Module "'.$objBannerTemplatesNew->name.'" has been migrated';
				                $this->Database->prepare("INSERT INTO tl_log (tstamp, source, action, username, text, func, ip, browser) VALUES(?, ?, ?, ?, ?, ?, ?, ?)")->execute(time(), 'BE', 'CONFIGURATION', '', specialchars($strText), 'Banner Modul Template Migration', '127.0.0.1', 'NoBrowser');
				            }
				        } 
				        elseif (count($arrKat) > 1) 
				        {
				            $objTemplatesOld = $this->Database->execute("SELECT `id`, `title`, `banner_template` FROM `tl_banner_category` WHERE id =".$arrKat[0]."");
				            
				            while ($objTemplatesOld->next())
				            {
				                //Protokoll
				                $strText = 'Banner-Module "'.$objBannerTemplatesNew->name.'" could not be migrated';
				                $this->Database->prepare("INSERT INTO tl_log (tstamp, source, action, username, text, func, ip, browser) VALUES(?, ?, ?, ?, ?, ?, ?, ?)")->execute(time(), 'BE', 'ERROR', '', specialchars($strText), 'Banner Modul Template Migration', '127.0.0.1', 'NoBrowser');
				            }
				        }
				    } // while
				} // migration == true
			}
		} // version > 2.8
		else
		{
			$this->Database->prepare("INSERT INTO tl_log (tstamp, source, action, username, text, func, ip, browser) VALUES(?, ?, ?, ?, ?, ?, ?, ?)")->execute(time(), 'FE', 'ERROR', ($GLOBALS['TL_USERNAME'] ? $GLOBALS['TL_USERNAME'] : ''), 'ERROR: Banner-Module requires at least Contao 2.9', 'ModulBanner Runonce', '127.0.0.1', 'NoBrowser');
		}
	}
}

?>