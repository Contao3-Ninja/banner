<?php
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Banner Statistik Export
 *
 * wird per export button direkt über formular aufgerufen als popup
 * 
 * PHP version 5
 * @copyright  Glen Langer 2007..2011
 * @author     Glen Langer
 * @package    Banner
 * @license    GPL
 * @filesource
 */

/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require('../../../initialize.php');

/**
 * Class BannerStatExport
 *
 * @copyright  Glen Langer 2007..2011
 * @author     Glen Langer
 * @package    Banner
 */
class BannerStatExport extends Backend // Backend bringt DB mit
{
    /**
	 * Export Type
	 */
	protected $strExportType ='';
	
	/**
	 * Export Delimiter
	 */
    protected $strExportDelimiter ='';
    
    /**
	 * Set the current file
	 */
	public function __construct()
	{
		$this->import('BackendUser', 'User');
		parent::__construct(); 
		$this->User->authenticate(); 
	    $this->loadLanguageFile('default');
		$this->loadLanguageFile('modules');
		$this->loadLanguageFile('tl_banner'); 
	}
	
    // Die parametrisierte Factorymethode
    public function factory($type)
    {
        if (@include(realpath(dirname(__FILE__)) . '/' . $type . '.php')) {
            $classname = 'BannerStatExport' . $type;
            return new $classname;
        } else {
            return false;
        }
    }

    public function run()
	{
   	    if ( (!$this->Input->get('tl_field',true)=='csvc') && 
   	         (!$this->Input->get('tl_field',true)=='csvs') && 
   	         (!$this->Input->get('tl_field',true)=='excel') 
   	       ) {
   	        echo "<html><body>Missing Parameter(s)!</body></html>";
            return ;
	    }
	    if ((int)$this->Input->get('tl_katid',true) < -1 || (int)$this->Input->get('tl_katid',true) == 0) {
	    	echo "<html><body>Wrong Parameter(s)!</body></html>";
            return ;
	    }
	    $intBannerKatId = (int)$this->Input->get('tl_katid',true);
	    switch ($this->Input->get('tl_field',true)) {
	    	case "csvc":
                $this->strExportType = 'csv';
	    	    $this->strExportDelimiter = ',';
	    		break;
	    	case "csvs":
                $this->strExportType = 'csv';
	    	    $this->strExportDelimiter = ';';
	    		break;
	    	case "excel":
                $this->strExportType = 'excel95';
	    	    $this->strExportDelimiter = ',';
	    		break;
	    	default:
	    		break;
	    }
	    $objExport = BannerStatExport::factory($this->strExportType);
	    if ($objExport===false) {
            echo "<html><body>Driver ".$this->strExportType." not found!</body></html>";
	    	return ;
	    }
	    if ($intBannerKatId == -1) {
	   	    $objBanners = $this->Database->prepare("SELECT tbc.title, tb.id, tb.banner_type, tb.banner_name, tb.banner_url, tb.banner_image, tb.banner_image_extern, tb.banner_weighting, tb.banner_start, tb.banner_stop, tb.banner_published, tbs.banner_views, tbs.banner_clicks"
			                                     . " FROM tl_banner AS tb"
			                                     . " LEFT JOIN tl_banner_stat AS tbs ON (tbs.id=tb.id)"
	                                             . " LEFT JOIN tl_banner_category AS tbc ON (tbc.id=tb.pid)"
	                                             . " ORDER BY tbc.id, tbc.title, tb.id")
						                 ->execute();
	    } else {
	   	    $objBanners = $this->Database->prepare("SELECT tbc.title, tb.id, tb.banner_type, tb.banner_name, tb.banner_url, tb.banner_image, tb.banner_image_extern, tb.banner_weighting, tb.banner_start, tb.banner_stop, tb.banner_published, tbs.banner_views, tbs.banner_clicks"
			                                     . " FROM tl_banner AS tb"
			                                     . " LEFT JOIN tl_banner_stat AS tbs ON (tbs.id=tb.id)"
	                                             . " LEFT JOIN tl_banner_category AS tbc ON (tbc.id=tb.pid)"
	                                             . " WHERE tbc.id =?"
	                                             . " ORDER BY tbc.title, tb.id")
						                 ->execute($intBannerKatId);
	    }
	    $objExport->export($objBanners,$this->strExportDelimiter,$intBannerKatId);
	}
}

/**
 * Instantiate
 */
$objBannerStatExport = new BannerStatExport();
$objBannerStatExport->run();

?>