<?php
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Banner Statistik Export - Excel Variante
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
 * Class BannerStatExportexcel
 *
 * @copyright  Glen Langer 2007..2010
 * @author     Glen Langer
 * @package    Banner
 */
class BannerStatExportexcel
{
    protected $BannerExportLib = 'excel';
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
        header('Content-Type: application/vnd.ms-excel');
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: attachment; filename="BannerStatExport-'.$intBannerKatId.'.utf8.xls"');
        if ($this->BrowserAgent == 'IE') {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        } else {
            header('Pragma: no-cache');
        }
        //$csv_enclosure = '"'; 
        $excel_header = '
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<style id="Classeur1_16681_Styles">
</style>

</head>
<body>

<div id="Classeur1_16681" align=center x:publishsource="Excel">

<table x:str border=0 cellpadding=0 cellspacing=0 width=100% style="border-collapse: collapse">
';
        $excel_footer = '
</table>
</div>
</body>
</html>
';
        //$out = fopen(TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/BannerStatExport.csv', 'w+');
        $out = fopen('php://output', 'w');
        fputs($out, $excel_header);
        //Kopfdaten
        $arrBannersStatHeader = explode(",",html_entity_decode($GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['export_headline'],ENT_NOQUOTES,'UTF-8'));
        $arrBannersStat[] = '<td class=xl2216681 nowrap><b>'.$arrBannersStatHeader[0].'</b></td>';
        $arrBannersStat[] = '<td class=xl2216681 nowrap><b>'.$arrBannersStatHeader[1].'</b></td>';
        $arrBannersStat[] = '<td class=xl2216681 nowrap><b>'.$arrBannersStatHeader[2].'</b></td>';
        $arrBannersStat[] = '<td class=xl2216681 nowrap><b>'.$arrBannersStatHeader[3].'</b></td>';
        $arrBannersStat[] = '<td class=xl2216681 nowrap><b>'.$arrBannersStatHeader[4].'</b></td>';
        $arrBannersStat[] = '<td class=xl2216681 nowrap><b>'.$arrBannersStatHeader[5].'</b></td>';
        $arrBannersStat[] = '<td class=xl2216681 nowrap><b>'.$arrBannersStatHeader[6].'</b></td>';
        $arrBannersStat[] = '<td class=xl2216681 nowrap><b>'.$arrBannersStatHeader[7].'</b></td>';
        $arrBannersStat[] = '<td class=xl2216681 nowrap><b>'.$arrBannersStatHeader[8].'</b></td>';
        $arrBannersStat[] = '<td class=xl2216681 nowrap><b>'.$arrBannersStatHeader[9].'</b></td>';
        $arrBannersStat[] = '<td class=xl2216681 nowrap><b>'.$arrBannersStatHeader[10].'</b></td>';
        fputs($out, '<tr>'.implode("",$arrBannersStat).'</tr>');
        //fputcsv($out, $arrBannersStat, $csv_delimiter, $csv_enclosure);
        unset($arrBannersStat);
        //Daten
        while ($objBanners->next())
        {
            $objBanners->banner_image = ($objBanners->banner_type == 'banner_image') ? $objBanners->banner_image : $objBanners->banner_image_extern;
            $arrBannersStat[] = '<td class=xl2216681 nowrap>'.$objBanners->title.'</td>';
            $arrBannersStat[] = '<td class=xl2216681 nowrap>'.$objBanners->id.'</td>';
    		$arrBannersStat[] = '<td class=xl2216681 nowrap>'.$objBanners->banner_name.'</td>';
    		$arrBannersStat[] = '<td class=xl2216681 nowrap>'.$objBanners->banner_url.'</td>';
    		$arrBannersStat[] = '<td class=xl2216681 nowrap>'.$objBanners->banner_image.'</td>';
    		$arrBannersStat[] = '<td class=xl2216681 nowrap>'.$objBanners->banner_weighting.'</td>';
    		$arrBannersStat[] = $objBanners->banner_start=='' ? '<td class=xl2216681 nowrap>NULL</td>' : '<td class=xl2216681 nowrap>'.date($GLOBALS['TL_CONFIG']['datimFormat'], $objBanners->banner_start).'</td>';
    		$arrBannersStat[] = $objBanners->banner_stop==''  ? '<td class=xl2216681 nowrap>NULL</td>' : '<td class=xl2216681 nowrap>'.date($GLOBALS['TL_CONFIG']['datimFormat'], $objBanners->banner_stop).'</td>';
    		$arrBannersStat[] = $objBanners->banner_published=='' ? '<td class=xl2216681 nowrap>'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_no'].'</td>' : '<td class=xl2216681 nowrap>'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_yes'].'</td>';
    		$arrBannersStat[] = '<td class=xl2216681 nowrap>'.$objBanners->banner_views.'</td>';
    		$arrBannersStat[] = $objBanners->banner_clicks=='' ? '<td class=xl2216681 nowrap>0</td>' : '<td class=xl2216681 nowrap>'.$objBanners->banner_clicks.'</td>';
    		fputs($out, '<tr>'.implode("",$arrBannersStat).'</tr>');
            //fputcsv($out, $arrBannersStat, $csv_delimiter, $csv_enclosure);
            unset($arrBannersStat);
        }
        fputs($out, $excel_footer);
        fclose($out);
    }
}
?>