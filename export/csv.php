<?php
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Banner Statistik Export - CSV Variante
 *
 * wird von BannerStatExport.php aufgerufen als popup
 * 
 * PHP version 5
 * @copyright  Glen Langer 2007..2010
 * @author     Glen Langer
 * @package    Banner
 * @license    GPL
 * @filesource
 */


/**
 * Class BannerStatExportcsv
 *
 * @copyright  Glen Langer 2007..2010
 * @author     Glen Langer
 * @package    Banner
 */
class BannerStatExportcsv
{
    protected $BannerExportLib = 'csv';
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
        header('Content-Type: text/comma-separated-values; charset=' . $GLOBALS['TL_CONFIG']['characterSet']);
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: attachment; filename="BannerStatExport-'.$intBannerKatId.'.utf8.csv"');
        if ($this->BrowserAgent == 'IE') {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        } else {
            header('Pragma: no-cache');
        }
        $csv_enclosure = '"'; 
        //$out = fopen(TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/BannerStatExport.csv', 'w+');
        $out = fopen('php://output', 'w');
        //Kopfdaten
        $arrBannersStat = explode(",",html_entity_decode($GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['export_headline'],ENT_NOQUOTES,'UTF-8'));
        fputcsv($out, $arrBannersStat, $csv_delimiter, $csv_enclosure);
        unset($arrBannersStat);
        //Daten
        while ($objBanners->next())
        {
            $arrBannersStat[] = $objBanners->title;
            $arrBannersStat[] = $objBanners->id;
    		$arrBannersStat[] = $objBanners->banner_name;
    		$arrBannersStat[] = $objBanners->banner_url;
    		$arrBannersStat[] = ($objBanners->banner_type == 'banner_image') ? $objBanners->banner_image : $objBanners->banner_image_extern;
    		$arrBannersStat[] = $objBanners->banner_weighting;
    		$arrBannersStat[] = $objBanners->banner_start=='' ? '' : date($GLOBALS['TL_CONFIG']['datimFormat'], $objBanners->banner_start);
    		$arrBannersStat[] = $objBanners->banner_stop==''  ? '' : date($GLOBALS['TL_CONFIG']['datimFormat'], $objBanners->banner_stop);
    		$arrBannersStat[] = $objBanners->banner_published=='' ? $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_no'] : $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_yes'];
    		$arrBannersStat[] = $objBanners->banner_views;
    		$arrBannersStat[] = $objBanners->banner_clicks=='' ? 0 : $objBanners->banner_clicks;
            fputcsv($out, $arrBannersStat, $csv_delimiter, $csv_enclosure);
            unset($arrBannersStat);
        }
        fclose($out);
    }
}
?>