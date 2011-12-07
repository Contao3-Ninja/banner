<?php
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Banner Statistik Export - Excel Variante
 *
 * wird von BannerStatExport.php aufgerufen als popup
 * 
 * PHP version 5
 * @copyright  Glen Langer 2007..2011
 * @author     Glen Langer
 * @package    Banner
 * @license    GPL
 * @filesource
 */


/**
 * Class BannerStatExportexcel
 *
 * @copyright  Glen Langer 2007..2011
 * @author     Glen Langer
 * @package    Banner
 */
class BannerStatExportexcel95
{
    protected $BannerExportLib = 'excel95';
    protected $BrowserAgent ='';
    
    /**
	 * Constructor
	 */
	public function __construct()
	{
	    //IE or other?
	    $log_version ='';
        $HTTP_USER_AGENT = getenv("HTTP_USER_AGENT");
        if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $HTTP_USER_AGENT, $log_version)) {
            $this->BrowserAgent = 'IE';
        } else {
            $this->BrowserAgent = 'NOIE';
        }
	}
	
    public function getLibName() {
        return $this->BannerExportLib;
    }
    
    public function export($objBanners,$csv_delimiter,$intBannerKatId) {
        // Download
        if ($intBannerKatId == -1) {
        	$intBannerKatId = 'all';
        }
        if (file_exists(TL_ROOT . "/plugins/xls_export/xls_export.php")) {
	    	include(TL_ROOT . "/plugins/xls_export/xls_export.php");
			$xls = new xlsexport();
			$sheet = 'BannerStatExport-'.$intBannerKatId.'';
			$xls->addworksheet($sheet);
			//Kopfdaten
	        $arrBannersStatHeader = explode(",",html_entity_decode($GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['export_headline'],ENT_NOQUOTES,'UTF-8'));
	        
	        $intRowCounter = 1;
			for ($c = 1; $c <= 11; $c++)
			{
				$xls->setcolwidth ($sheet,$c-1,0x1000);
				$xls->setcell(array("sheetname" => $sheet,"row" => 0, "col" => $c-1, 'fontweight' => XLSFONT_BOLD, 'hallign' => XLSXF_HALLIGN_CENTER, "data" => utf8_decode($arrBannersStatHeader[$c-1])));
			}
			
			while ($objBanners->next())
	        {
	        	$objBanners->banner_image = ($objBanners->banner_type == 'banner_image') ? $objBanners->banner_image : $objBanners->banner_image_extern;
	        	$arrBannersStat[0] = utf8_decode($objBanners->title);
	            $arrBannersStat[1] = $objBanners->id;
	    		$arrBannersStat[2] = utf8_decode($objBanners->banner_name);
	    		$arrBannersStat[3] = html_entity_decode($objBanners->banner_url,ENT_NOQUOTES,'UTF-8');
	    		$arrBannersStat[4] = html_entity_decode($objBanners->banner_image,ENT_NOQUOTES,'UTF-8');
	    		$arrBannersStat[5] = $objBanners->banner_weighting;
	    		$arrBannersStat[6] = $objBanners->banner_start=='' ? 'NULL' : date($GLOBALS['TL_CONFIG']['datimFormat'], $objBanners->banner_start);
	    		$arrBannersStat[7] = $objBanners->banner_stop==''  ? 'NULL' : date($GLOBALS['TL_CONFIG']['datimFormat'], $objBanners->banner_stop);
	    		$arrBannersStat[8] = $objBanners->banner_published=='' ? $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_no'] : $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_yes'];
	    		$arrBannersStat[9] = $objBanners->banner_views=='' ? '0' : $objBanners->banner_views;
	    		$arrBannersStat[10] = $objBanners->banner_clicks=='' ? '0' : $objBanners->banner_clicks;
	    		
	    		for ($c = 1; $c <= 11; $c++) {
	    			if ($c==3 || $c==4 || $c==5) {
	    				$xls->setcell(array("sheetname" => $sheet,"row" => $intRowCounter, "col" => $c-1, 'hallign' => XLSXF_HALLIGN_LEFT, "data" => $arrBannersStat[$c-1]));
	    			} else {
	        			$xls->setcell(array("sheetname" => $sheet,"row" => $intRowCounter, "col" => $c-1, 'hallign' => XLSXF_HALLIGN_CENTER, "data" => $arrBannersStat[$c-1]));
	    			}
	        	}
	        	$intRowCounter++;
	        } // while
	        $xls->sendFile($sheet . ".xls");
        } else {
			echo "<html><head><title>Need extension xls_export</title></head><body>"
			    ."Please install the extension 'xls_export'.<br /><br />"
			    ."Bitte die Erweiterung 'xls_export' installieren.<br /><br />"
			    ."Installer l'extension 'xls_export' s'il vous plaît."
			    ."</body></html>";
		}
    } // function
}
?>