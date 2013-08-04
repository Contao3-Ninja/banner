<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Open Source CMS, Copyright (C) 2005-2013 Leo Feyer
 * 
 * Modul Banner Stat - Backend
 * 
 * @copyright	Glen Langer 2007..2013 <http://www.contao.glen-langer.de>
 * @author      Glen Langer (BugBuster)
 * @package     Banner
 * @license     GPL
 * @filesource
 */


/**
 * Class ModuleBannerStat
 *
 * @copyright  Glen Langer 2007..2013
 * @author     Glen Langer
 * @package    Banner
 */
class ModuleBannerStat extends BackendModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_banner_stat';
	
	/**
	 * Kat ID
	 * @var int
	 */
	protected $intKatID;

	/**
	 * Constructor
	 */
	public function __construct()
	{
	    parent::__construct();
	    
	    if ($this->Input->get('id') === null)
	    {
	        $this->intKatID = (int)$this->Input->post('id'); //banner reset, category reset
	    }
	    else 
	    {
	        $this->intKatID = (int)$this->Input->get('id'); //directly category link
	    }
	    
	    if ($this->Input->post('act',true)=='zero') //banner reset, category reset
	    {
	    	$this->setZero();
	    }
	}
	
	/**
	 * Generate module
	 */
	protected function compile()
	{
		require_once(TL_ROOT . '/system/modules/banner/ModuleBannerVersion.php');
		
	    if ($this->intKatID == 0) 
	    { //direkter Aufruf ohne ID
    	    $objBannerKatID = $this->Database->prepare("SELECT MIN(pid) AS ANZ from tl_banner")->execute();
    	    $objBannerKatID->next();
    	    if ($objBannerKatID->ANZ === null) 
    	    {
    	    	$this->intKatID = 0;
    	    } 
    	    else 
    	    {
    	        $this->intKatID = $objBannerKatID->ANZ;
    	    }
	    } // if intKatID == 0
	    if ($this->intKatID == -1) 
	    { // alle Kat
	    	$objBanners = $this->Database->prepare("SELECT tb.id, tb.banner_type, tb.banner_name, tb.banner_url, tb.banner_jumpTo, tb.banner_image, tb.banner_image_extern, tb.banner_weighting, tb.banner_start, tb.banner_stop, tb.banner_published, tb.banner_until, tb.banner_comment, tb.banner_views_until, tb.banner_clicks_until, tbs.banner_views, tbs.banner_clicks"
		                                     	. " FROM tl_banner tb, tl_banner_stat tbs"
	                                         	. " WHERE tb.id=tbs.id"
	                                         	. " ORDER BY tb.pid,tb.banner_name")
					                 	 ->execute();
	    } 
	    else 
	    {
			$objBanners = $this->Database->prepare("SELECT tb.id, tb.banner_type, tb.banner_name, tb.banner_url, tb.banner_jumpTo, tb.banner_image, tb.banner_image_extern, tb.banner_weighting, tb.banner_start, tb.banner_stop, tb.banner_published, tb.banner_until, tb.banner_comment, tb.banner_views_until, tb.banner_clicks_until, tbs.banner_views, tbs.banner_clicks"
		                                     	. " FROM tl_banner tb, tl_banner_stat tbs"
	                                         	. " WHERE tb.id=tbs.id"
	                                         	. " AND tb.pid =?"
	                                         	. " ORDER BY tb.banner_name")
					                 	 ->execute($this->intKatID);
	    }
        $intRows = $objBanners->numRows;
		if ($intRows>0) 
		{// Banner vorhanden in Statistik Tabelle
            while ($objBanners->next())
    		{
    		    //Banner Ziel per Page?
                if ($objBanners->banner_jumpTo >0) 
                {
                	//url generieren
                	$objBannerNextPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
                                                        ->limit(1)
                                                        ->execute($objBanners->banner_jumpTo);
                	if ($objBannerNextPage->numRows)
                	{
                		$objBanners->banner_url = $this->generateFrontendUrl($objBannerNextPage->fetchAssoc());
                	} 
                }
    		    if ($objBanners->banner_url == '') 
    		    {
    		    	$objBanners->banner_url = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['NoURL'];
    		    	if ($objBanners->banner_clicks == 0) 
    		    	{
    		    		$objBanners->banner_clicks = '--';
    		    	}
    		    }
    		    if ( ($objBanners->banner_published == 1) && 
    		         ($objBanners->banner_start=='' || $objBanners->banner_start<=time()) && 
    		         ($objBanners->banner_stop==''  || $objBanners->banner_stop>time())
    		       ) 
    		    {
    		    	$objBanners->banner_published = '<span class="banner_stat_yes">'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_yes'].'</span>';
    		    	$intMaxViews =0;
    		    	$intMaxClicks=0;
    		    	if ($objBanners->banner_until==1 && $objBanners->banner_views_until !='' && $objBanners->banner_views>=$objBanners->banner_views_until) 
    		    	{
    		    	    //max views erreicht
    		    	    $intMaxViews=1;
    		    		$objBanners->banner_published = '<span class="banner_stat_no">'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_no'].'</span>';
    		    	}
    		    	if ($objBanners->banner_until==1 && $objBanners->banner_clicks_until !='' && $objBanners->banner_clicks>=$objBanners->banner_clicks_until) 
    		    	{
    		    	    //max clicks erreicht
    		    	    $intMaxClicks=1;
    		    		$objBanners->banner_published = '<span class="banner_stat_no">'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_no'].'</span>';
    		    	}
    		    } 
    		    else 
    		    {
    		    	$objBanners->banner_published = '<span class="banner_stat_no">'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_no'].'</span>';
    		    }
    		    // 1 = GIF, 2 = JPG, 3 = PNG
    		    // 4 = SWF, 13 = SWC (zip-like swf file)
    		    // 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order)
    		    // 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF
    		    if ($objBanners->banner_type == 'banner_image') 
    		    {
    		        //Interne Banner Grafik
    		        $arrImageSize = @getimagesize(TL_ROOT . '/' . $objBanners->banner_image);
    		        if ($arrImageSize===false) 
    		        {
				    	//Workaround fuer PHP ohne zlib bei SWC Files
				    	$arrImageSize = $this->getimagesizecompressed(TL_ROOT . '/' . $objBanners->banner_image);
				    }
    		    } 
    		    if ($objBanners->banner_type == 'banner_image_extern') 
    		    {
    		    	$arrImageSize = $this->getImageSizeExternal($objBanners->banner_image_extern);
    		    }
    		    if ($objBanners->banner_type == 'banner_text') 
    		    {
    		    	$arrImageSize = array(999,999,999);
    		    }
    		    $banner_url = html_entity_decode($objBanners->banner_url, ENT_NOQUOTES, 'UTF-8');
    		    $oriSize = false;
    		    switch ($arrImageSize[2]) 
    		    {
                    case 1:
                    case 2:
                    case 3:
                    case 4:  // Flash swf
                    case 13: // Flash swc
                        if ($arrImageSize[0] > $arrImageSize[1]) 
                        { // Breite > Hoehe
                            if ($arrImageSize[0] >250) 
                            {
                            	$intWidth  = 250;
                            	$intHeight = ceil($intWidth*$arrImageSize[1]/$arrImageSize[0]);
                            } 
                            else 
                            {
                            	$intWidth  = $arrImageSize[0];
                            	$intHeight = $arrImageSize[1];
                            	$oriSize = true; // Merkmal fuer Bilder ohne Umrechnung
                            }
                        } 
                        else 
                        { // Hoehe >= Breite, ggf. Hoehe verkleinern
                            if ($arrImageSize[1]>250) 
                            {
                                // pruefen ob bei neuer Hoehe die Breite zu klein wird
                            	if ((250*$arrImageSize[0]/$arrImageSize[1]) < 40) 
                            	{
                            		// Breite statt Hoehe setzen
                            		$intWidth  = 40;
                            		$intHeight = ceil($intWidth*$arrImageSize[1]/$arrImageSize[0]);
                            	} 
                            	else 
                            	{
                            		$intHeight = 250;
                            		$intWidth  = ceil($intHeight*$arrImageSize[0]/$arrImageSize[1]);
                            	}
                            } 
                            else 
                            {
                                $intWidth  = $arrImageSize[0];
                                $intHeight = $arrImageSize[1];
                                $oriSize = true; // Merkmal fuer Bilder ohne Umrechnung
                            }
                        }
        		        break;
                    default:
                        break;
        	   }  
 		       switch ($arrImageSize[2]) 
 		       {
                    case 1:
                    case 2:
                    case 3:
                        if ($objBanners->banner_type == 'banner_image') 
                        { // internes Bild
                            if ($oriSize) 
                            {
                            	$objBanners->banner_image = $this->urlEncode($objBanners->banner_image); 
                            } 
                            else 
                            {
                                $objBanners->banner_image = $this->getImage($this->urlEncode($objBanners->banner_image), $intWidth, $intHeight);
                            }
                        } 
                        else 
                        { // externes Bild
                        	$objBanners->banner_image = $objBanners->banner_image_extern;
                        }
               		    $arrBannersStat[] = array
            			(
            			    'banner_id'           => $objBanners->id,
            			    'banner_style'        => '',
            				'banner_name'         => specialchars(ampersand($objBanners->banner_name)),
            				'banner_alt'          => specialchars(ampersand($objBanners->banner_name)),
            				'banner_title'        => $banner_url,
            				'banner_url'          => (strlen($banner_url) <61 ? $banner_url : substr($banner_url, 0, 28)."[...]".substr($banner_url,-24,24) ),
            				'banner_image'        => $objBanners->banner_image,
            				'banner_width'        => $intWidth,
            				'banner_height'       => $intHeight,
            				'banner_prio'         => $objBanners->banner_weighting,
            				'banner_views'        => ($intMaxViews)  ? $objBanners->banner_views .'<br />'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['max_yes']  : $objBanners->banner_views,
            				'banner_clicks'       => ($intMaxClicks) ? $objBanners->banner_clicks .'<br />'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['max_yes'] : $objBanners->banner_clicks,
            				'banner_active'       => $objBanners->banner_published,
            				'banner_zero'         => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['zero_text'],
            				'banner_confirm'      => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['zero_confirm'],
            				'banner_pic'          => true, // Es ist ein Bild
            				'banner_flash'        => false,
            				'banner_text'         => false
            			);
            		    break;
                    case 4:  // Flash swf
                    case 13: // Flash swc
                        if ($objBanners->banner_type == 'banner_image_extern') 
                        {
                            $objBanners->banner_image = $objBanners->banner_image_extern;
                        }
               		    $arrBannersStat[] = array
            			(
            			    'banner_id'           => $objBanners->id,
            			    'banner_style'        => '',
            				'banner_name'         => specialchars(ampersand($objBanners->banner_name)),
            				'banner_url'          => (strlen($banner_url) <61 ? $banner_url : substr($banner_url, 0, 28)."[...]".substr($banner_url,-24,24) ),
            				'swf_src'             => $objBanners->banner_image,
            				'swf_width'           => $intWidth,
            				'swf_height'          => $intHeight,
            				'banner_prio'         => $objBanners->banner_weighting,
            				'banner_views'        => ($intMaxViews)  ? $objBanners->banner_views  .'<br />'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['max_yes'] : $objBanners->banner_views,
            				'banner_clicks'       => ($intMaxClicks) ? $objBanners->banner_clicks .'<br />'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['max_yes'] : $objBanners->banner_clicks,
            				'banner_active'       => $objBanners->banner_published,
            				'banner_zero'         => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['zero_text'],
            				'banner_confirm'      => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['zero_confirm'],
            				'banner_pic'          => false, // Es ist ein SWF
            				'banner_flash'        => true,
            				'banner_text'         => false
    		            );
                        break;
                    case 999: //Textbanner
                        // Kurz URL (nur Domain)
	                	if (preg_match('@^(?:http://)?([^/]+)@i',$banner_url, $treffer))
	                	{
	                		$banner_url_kurz = $treffer[1];
	                	} 
	                	else 
	                	{
	                		$banner_url_kurz = '';
	                	}
                        $arrBannersStat[] = array
            			(
            			    'banner_id'           => $objBanners->id,
            				'banner_name'         => specialchars(ampersand($objBanners->banner_name)),
            				'banner_comment'      => nl2br($objBanners->banner_comment),
            				'banner_url_kurz'     => $banner_url_kurz,
            				'banner_url'          => (strlen($banner_url) <61 ? $banner_url : substr($banner_url, 0, 28)."[...]".substr($banner_url,-24,24) ),
            				'banner_prio'         => $objBanners->banner_weighting,
            				'banner_views'        => ($intMaxViews)  ? $objBanners->banner_views .'<br />'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['max_yes']  : $objBanners->banner_views,
            				'banner_clicks'       => ($intMaxClicks) ? $objBanners->banner_clicks .'<br />'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['max_yes'] : $objBanners->banner_clicks,
            				'banner_active'       => $objBanners->banner_published,
            				'banner_zero'         => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['zero_text'],
            				'banner_confirm'      => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['zero_confirm'],
            				'banner_pic'          => false, // Es ist kein Bild
            				'banner_flash'        => false,
            				'banner_text'         => true   // Es ist ein Textbanner
            			);
            		    break;
                    default:
                        //Banner laut Statistik, aber Datei nicht gefunden / Lesefehler
                        //Umschalten auf extern, falls definiert
                        if ($objBanners->banner_type == 'banner_image_extern')
                        {
                            $objBanners->banner_image = $objBanners->banner_image_extern;
                        }
                        $arrBannersStat[] = array
                        (
                            'banner_pic'    => true, 
                            'banner_flash'  => false,
                            'banner_text'   => false,
                            'banner_prio'   => $objBanners->banner_weighting,
                            'banner_views'  => ($intMaxViews)  ? $objBanners->banner_views .'<br />'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['max_yes']  : $objBanners->banner_views,
                            'banner_clicks' => ($intMaxClicks) ? $objBanners->banner_clicks .'<br />'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['max_yes'] : $objBanners->banner_clicks,
                            'banner_active' => $objBanners->banner_published,
                            'banner_zero'   => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['zero_text'],
                            'banner_confirm'=> $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['zero_confirm'],
                            'banner_style'  => 'color:red;font-weight:bold;',
                            'banner_alt'    => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['read_error'], 
                            'banner_url'    => $objBanners->banner_image .''
                        );
                        break;
    		    }
    		}
		} 
		else 
		{
		    $arrBannersStat[] = array();
		}
		
		$this->Template->bannersstat      = $arrBannersStat;
		$this->Template->banner_export_title = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['export_button_title'];
		$this->Template->header_id        = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['id'];
		$this->Template->header_picture   = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['picture'];
		$this->Template->header_name      = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['name'];
		$this->Template->header_url       = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['URL'];
		$this->Template->header_active    = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['active'];
		$this->Template->header_prio      = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['Prio'];
		$this->Template->header_clicks    = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['clicks'];
		$this->Template->header_views     = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['views'];
		$this->Template->banner_version   = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['modname'] . ' ' . BANNER_VERSION .'.'. BANNER_BUILD;
		$this->Template->banner_footer    = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['comment'];
		$this->Template->banner_base      = $this->Environment->base;
		$this->Template->theme            = $this->getTheme();
		$this->Template->theme0           = 'default';
		
		if (version_compare(VERSION . '.' . BUILD, '2.9.0', '<'))
		{
		   // Code für Versionen < 2.9.0
		   $this->Template->banner_footer = "ERROR: From version 2.0.0, Banner-Module requires at least Contao 2.9";
		}
		
		// Kat sammeln
		$objBannerKat = $this->Database->prepare("SELECT id , title FROM tl_banner_category WHERE id IN "
		                                     . " ( SELECT pid FROM tl_banner "
                                             . " LEFT JOIN tl_banner_category ON tl_banner.pid = tl_banner_category.id "
                                             . " GROUP BY tl_banner.pid )"
                                             . " ORDER BY title")
					                   ->execute();
		$intKatRows = $objBannerKat->numRows;
		
		if ($intKatRows>0) 
		{
    		if ($intRows==0) 
    		{ // gewählte Kat hat keine Banner, es gibt aber weitere Kats
    			$arrBannerKats[] = array
    		    (
                    'id'    => '0',
                    'title' => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['select']
    		    );
    		    $this->intKatID = 0; // template soll nichts anzeigen
		    }
		    $arrBannerKats[] = array
		    (
                'id'    => '-1',
                'title' => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['allkat']
		    );
			while ($objBannerKat->next())
			{
			    $arrBannerKats[] = array
			    (
                    'id'    => $objBannerKat->id,
                    'title' => $objBannerKat->title
			    );
			}
		} 
		else 
		{ // es gibt keine Kat mit Banner
			$arrBannerKats[] = array
		    (
                'id'    => '0',
                'title' => '---------'
		    );
		}
		$this->Template->bannerkats    = $arrBannerKats;
		$this->Template->bannerkatid   = $this->intKatID;
		$this->Template->bannerstatkat = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['kat'];
		$this->Template->exportfield   = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['kat'].' '.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['export'];
		$this->Template->bannercatzero        = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['cat_zero'];
		$this->Template->bannercatzerobutton  = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['cat_zero_button'];
		$this->Template->bannercatzerotext    = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['cat_zero_text'];
		$this->Template->bannercatzeroconfirm = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['cat_zero_confirm'];
		
		// Code für Versionen ab 2.9.0
		$this->Template->banner_base_be = $this->Environment->base . 'contao';
		// Ausgabe
	}
	
	/**
	 * Statistic, set on zero
	 */
	protected function setZero()
	{
	    //Banner
	    $intBID = preg_replace('@\D@', '', $this->Input->post('zid')); //  only digits 
	    if ($intBID>0) 
	    {
            $this->Database->prepare("UPDATE tl_banner_stat SET tstamp=?, banner_views=0, banner_clicks=0 WHERE id=?")
    					   ->execute( time() , $intBID );
	    }
	    //Category
        $intCatBID = preg_replace('@\D@', '', $this->Input->post('catzid')); //  only digits
	    if ($intCatBID>0)
	    {
	        $this->Database->prepare("UPDATE tl_banner_stat INNER JOIN tl_banner USING ( id ) SET tl_banner_stat.tstamp=?, banner_views=0, banner_clicks=0 WHERE pid=?")
	                       ->execute( time() , $intCatBID );
	    }
	    return ;
	}
	
	private function swc_data($filename) 
	{
		$size = 0;
		$width = 0;
		$height = 0;
	
		$file = @fopen($filename,"rb") ;
		if (!$file) 
		{
			return false;
		}
		if ("CWS" != fread($file,3)) 
		{
			return false;
		} 
		// Version
		fread($file,1) ;
		for ($i=0;$i<4;$i++) 
		{
			$t = ord(fread($file,1));
			$size += ($t<<(8*$i));
		}
		$buffer = gzuncompress(gzread($file,$size),$size) ;
		$buffer = substr($buffer,0,20) ; // first 20 Byte enough
	
		$b = ord(substr($buffer,0,1)) ;
		$buffer = substr($buffer,1) ;
		$cbyte 	= $b ;
		$bits 	= $b>>3 ;
	
		$cval 	= "" ;
		$cbyte &= 7 ;
		$cbyte<<= 5 ;
		$cbit 	= 2 ;
		// RECT
		for ($vals=0;$vals<4;$vals++) 
		{
			$bitcount = 0 ;
			while ($bitcount<$bits) 
			{
				if ($cbyte&128) 
				{
					$cval .= "1" ;
				} 
				else 
				{
					$cval .= "0" ;
				}
				$cbyte<<=1 ;
				$cbyte &= 255 ;
				$cbit-- ;
				$bitcount++ ;
				if ($cbit<0) 
				{
					$cbyte	= ord(substr($buffer,0,1)) ;
					$buffer = substr($buffer,1) ;
					$cbit = 7 ;
				}
			}
			$c 		= 1 ;
			$val 	= 0 ;
			$tval = strrev($cval) ;
			for ($n=0;$n<strlen($tval);$n++) 
			{
				$atom = substr($tval,$n,1) ;
				if ($atom=="1") $val+=$c ;
				$c*=2 ;
			}
			// TWIPS to PIXELS
			$val/=20 ;
			switch ($vals) 
			{
				case 0:
					// tmp value
					$width = $val ;
				break ;
				case 1:
					$width = $val - $width ;
				break ;
				case 2:
					// tmp value
					$height = $val ;
				break ;
				case 3:
					$height = $val - $height ;
				break ;
			}
			$cval = "" ;
		}
		fclose($file) ;
		$buffer ='';
		return array($width,$height);
	}
	
	/**
	 * getimagesize without zlib doesn't work
	 * workaround for this
	 *
	 * @param string $BannerImage
	 * @return array
	 */
	public function getimagesizecompressed($BannerImage) 
	{
		$arrImageSize = false;
		$res = $this->swc_data($BannerImage);
		if ($res) 
		{
			// width,height
			$arrImageSize = array($res[0], $res[1], 13);
		}
		return $arrImageSize;
	}
	
	/**
	 * Get external banner image
	 *
	 * @param string $BannerImageExternal
	 * @return array
	 */
	public function getImageSizeExternal($BannerImageExternal) 
	{
	    //log_message('[getimagesizeexternal] Externe Banner Grafik gefunden', 'debug.log');
	    $token = md5(uniqid(rand(), true));
	    $tmpImage = 'system/tmp/mod_banner_st_'.$token.'.tmp';
	    $objRequest = new Request();
		$objRequest->send(html_entity_decode($BannerImageExternal, ENT_NOQUOTES, 'UTF-8'));
		// Test auf chunked
		if ( array_key_exists('Transfer-Encoding',$objRequest->headers) && $objRequest->headers['Transfer-Encoding'] == 'chunked') 
		{
			try
			{ 
	    		$objFile = new File($tmpImage);
	    		$objFile->write($this->decodeChunked($objRequest->response));
	    		$objFile->close();
			}
			// Temp directory not writeable
			catch (Exception $e)
			{
				if ($e->getCode() == 0)
				{
					log_message('[getimagesizeexternal] tmpFile Problem: notWriteable', 'debug.log');
				} 
				else 
				{
				    log_message('[getimagesizeexternal] tmpFile Problem: error', 'debug.log');
				}
			}
		} 
		else 
		{
			try
			{ 
	    		$objFile = new File($tmpImage);
	    		$objFile->write($objRequest->response);
	    		$objFile->close();
			}
			// Temp directory not writeable
			catch (Exception $e)
			{
				if ($e->getCode() == 0)
				{
					log_message('[getimagesizeexternal] tmpFile Problem: notWriteable', 'debug.log');
				} 
				else 
				{
				    log_message('[getimagesizeexternal] tmpFile Problem: error', 'debug.log');
				}
			} 
		}
		$objRequest=null;
		unset($objRequest);
		$arrImageSize = @getimagesize(TL_ROOT . '/' . $tmpImage);
		if ($arrImageSize===false) 
		{
	    	//Workaround fuer PHP ohne zlib bei SWC Files
	    	$arrImageSize = $this->getimagesizecompressed(TL_ROOT . '/' . $tmpImage);
	    }
        //log_message('[getimagesizeexternal] Image Details: '.print_r($arrImageSize,true).'', 'debug.log');
		$objFile->delete();
		$objFile=null;
		unset($objFile);
				
		return $arrImageSize;	
	}
	
	private function decodeChunked($chunked)
    {
        $decBody = '';
        $m = '';
        while (trim($chunked)) 
        {
            preg_match("/^([\da-fA-F]+)[^\r\n]*\r\n/sm", $chunked, $m);
            $length = hexdec(trim($m[1]));
            $cut = strlen($m[0]);

            $decBody .= substr($chunked, $cut, $length);
            $chunked = substr($chunked, $cut + $length + 2);
        }
        return $decBody;
    }
}

?>