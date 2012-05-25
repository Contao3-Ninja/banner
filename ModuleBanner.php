<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Banner - Frontend
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer 
 * @package    Banner
 * @license    GPL
 * @filesource
 */


/**
 * Class ModuleBanner
 *
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer 
 * @package    Banner
 */
class ModuleBanner extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_banner_list_all';
	
	/**
	 * Banner Data
	 */
	protected $arrBannerData = array();
    
	/**
	 * Banner Seen
	 */
	public static $arrBannerSeen = array();

	/**
	 * Banner Categories
	 */
	protected $arrBannerCategories = array();
	
	/**
	 * Banner User Agent Filter
	 */
	protected $useragent_filter = '';

	/**
	 * Page Output Format
	 *
	 * @var string
	 */
	protected $strFormat = 'xhtml';
	
	/**
	 * Banner Random Blocker
	 */
	protected $statusRandomBlocker = false;
	
	/**
	 * Banner First View Selection
	 */
	protected $selectBannerFirstView = 0;
	
	/**
	 * Banner First View Status
	 */
	protected $statusBannerFirstView = false;
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### BANNER MODUL ###';
			$objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            if (version_compare(VERSION . '.' . BUILD, '2.8.9', '>'))
			{
			   // Code für Versionen ab 2.9.0
			   $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
			}
			else
			{
			   // Code für Versionen < 2.9.0
			   $objTemplate->wildcard = '### BANNER MODULE ONLY FOR CONTAO 2.9 AND ABOVE ###';
			}
			return $objTemplate->parse();
		}

		$this->useragent_filter = $this->banner_useragent;
		$this->selectBannerFirstView = $this->banner_firstview;
		return parent::generate();
	}
	
	
	/**
	 * Generate module
	 * @todo : Complete rewrite. In several methods.
	 */
	protected function compile()
	{
		require_once(TL_ROOT . '/system/modules/banner/ModuleBannerVersion.php');
		
		//alte und neue Art gemeinsam zum Array bringen
		if (strpos($this->banner_categories,':') !== false) 
		{
			$this->arrBannerCategories = deserialize($this->banner_categories, true);
		} else {
			$this->arrBannerCategories = array($this->banner_categories);
		}

		// Return if there are no categories
		if (!is_array($this->arrBannerCategories) || !is_numeric($this->arrBannerCategories[0]))
		{
		    $this->log($GLOBALS['TL_LANG']['tl_banner']['banner_cat_not_found'], 'ModulBanner Compile', 'ERROR');
			return;
		}
		
		//FE Login Check / Filtering Categories
		if ($this->BannerCheckFE()===false) 
		{
    	    // Eingeloggter FE Nutzer darf nichts sehen
    	    // auf Leer umschalten
            $this->strTemplate='mod_banner_empty';
            $this->Template = new FrontendTemplate($this->strTemplate); 
            return ;
		}
		$arrBanners = array();
		$arrResults = array();
		$intPrio1   = 99;
		$intPrio2   = 99;
		$intShowBannerId = -1;
		//$aresult = array();
		$intTime = time();
		//Domain Name ermitteln
		$http_host = $this->Environment->host;
		
		// Test auf Banner ALL und Limit
		$objBannerAll = $this->Database->prepare("SELECT id AS BALL, banner_random, banner_limit FROM tl_banner_category "
		                                      . " WHERE id IN (?) AND banner_numbers=?")
		                                ->execute(implode(',', $this->arrBannerCategories), 1);
		$intAllRows = $objBannerAll->numRows;
		$bolBRAND = false;
		$intBALL = 0;
		$intBannerLimit = 1; // default for single banner
		if ($intAllRows >0) 
		{
			$objBannerAll->next(); 
			//Kat mit Banner All Eigenschaft
			$intBALL = 1;
			$intBannerCategory = $objBannerAll->BALL;
			$intBannerLimit    = $objBannerAll->banner_limit; // 0:all, others = max
			if ($objBannerAll->banner_random == 1) 
			{
				$bolBRAND = true;
			}
		}
        if (count(self::$arrBannerSeen)) 
        {
            $strSqlExcludeSeen = " AND TLB.id NOT IN (".implode(",", self::$arrBannerSeen).")";
            $this->Template->headline_stop = true;
        } 
        else 
        {
            $strSqlExcludeSeen = '';
            $this->Template->headline_stop = false;
        }
		if ($intBALL !==1) 
		{
			/*
			____ _ _  _ ____ _    ____    ___  ____ _  _ _  _ ____ ____ 
			[__  | |\ | | __ |    |___    |__] |__| |\ | |\ | |___ |__/ 
			___] | | \| |__] |___ |___    |__] |  | | \| | \| |___ |  \ 
			*/
		    /*
		     FIRST VIEW BANNER ?
		    */
		    if ($this->selectBannerFirstView && $this->BannerGetFirstView() === true) 
		    {
		        //first aktiv banner in category
		        $objBanners = $this->Database->prepare("SELECT TLB.* FROM tl_banner AS TLB "
	                                                . " LEFT JOIN tl_banner_category ON (tl_banner_category.id=TLB.pid)"
	                                                //. " LEFT OUTER JOIN tl_banner_stat AS TLS ON TLB.id=TLS.id"
	                                                . " WHERE pid IN(" . implode(',', $this->arrBannerCategories) . ")"
	                                                //. " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_views_until>TLS.banner_views)   OR (TLB.banner_until=1 AND TLB.banner_views_until=?)  OR (TLB.banner_until=1 AND TLS.banner_views is NULL))"
			                                        //. " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_clicks_until>TLS.banner_clicks) OR (TLB.banner_until=1 AND TLB.banner_clicks_until=?) OR (TLB.banner_until=1 AND TLS.banner_clicks is NULL))"
	                                                //. " AND TLB.banner_weighting =? AND TLB.banner_published =1"
		                                            . " AND TLB.banner_published =1"
	                                                . " AND (TLB.banner_start=? OR TLB.banner_start<=?) AND (TLB.banner_stop=? OR TLB.banner_stop>=?)"
		                                            . " AND (TLB.banner_domain=? OR RIGHT(?, CHAR_LENGTH(TLB.banner_domain)) = TLB.banner_domain)" 
	                                                //.$strSqlExcludeSeen
	                                                //.$intRandomBlocker
		                                            . " ORDER BY sorting"
	                                                )
	                                         ->limit(1)
	    									 ->execute('', $intTime, '', $intTime, '', $http_host);
        	    $intRows = $objBanners->numRows;
		    } 
		    else 
		    {
    			//Weighting searching...
    			$intRandomBlocker = " AND TLB.id !=" .$this->BannerGetRandomBlocker();
    			$maxloop =0;
    			do
    			{
    			    //first with RandomBlocker
        			$objBanners1 = $this->Database->prepare("SELECT TLB.banner_weighting AS BW, count(TLB.id) AS ANZ"
        			                                     . " FROM tl_banner AS TLB "
        			                                     . " LEFT JOIN tl_banner_category ON (tl_banner_category.id=TLB.pid)"
        			                                     . " LEFT OUTER JOIN tl_banner_stat AS TLS ON TLB.id=TLS.id"
        			                                     . " WHERE pid IN(" . implode(',', $this->arrBannerCategories) . ")"
        			                                     . " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_views_until>TLS.banner_views)   OR (TLB.banner_until=1 AND TLB.banner_views_until=?)  OR (TLB.banner_until=1 AND TLS.banner_views is NULL))"
        			                                     . " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_clicks_until>TLS.banner_clicks) OR (TLB.banner_until=1 AND TLB.banner_clicks_until=?) OR (TLB.banner_until=1 AND TLS.banner_clicks is NULL))"
        			                                     . " AND TLB.banner_weighting >0 AND TLB.banner_weighting <? AND TLB.banner_published =1"
        			                                     . " AND (TLB.banner_start=? OR TLB.banner_start<=?) AND (TLB.banner_stop=? OR TLB.banner_stop>=?)"
        			                                     . " AND (TLB.banner_domain=? OR RIGHT(?, CHAR_LENGTH(TLB.banner_domain)) = TLB.banner_domain)"
        			                                     .$strSqlExcludeSeen
        			                                     .$intRandomBlocker
        			                                     . " GROUP BY 1")
        										  ->execute( '', '', '', '', 4, '', $intTime, '', $intTime, '', $http_host );
        			$intRows = $objBanners1->numRows;
        			$intRandomBlocker=''; //next loop without RandomBlocker
        			$maxloop++;
        			//log_message('BannerSingle Weighting LoopL '.$maxloop,'Banner.log');
    			} while ( ($intRows ==0) && ($maxloop<2));
    			$intPrioW2 = 0; // test for empty prio 2
    			$arrPrioW = array();
    		    while ($objBanners1->next()) 
    		    {
    		    	if ($objBanners1->BW == 1)  { $arrPrioW[] = 1; } 
    		    	if ($objBanners1->BW == 2)  { $arrPrioW[] = 2; $intPrioW2 = 1; } 
    		    	if ($objBanners1->BW == 3)  { $arrPrioW[] = 3; } 
    		    }
    		    
    	        $arrPrio[0] = array('start'=>0,  'stop'=>0);
    	        $arrPrio[1] = array('start'=>1,  'stop'=>90);
    	        $arrPrio[2] = array('start'=>91, 'stop'=>150);
    	        $arrPrio[3] = array('start'=>151,'stop'=>180);
    	        if ($intPrioW2 == 0) 
    	        {
    	        	// no prio 2 banner
    	        	$arrPrio[2] = array('start'=>0,  'stop'=>0);
    	        	$arrPrio[3] = array('start'=>91, 'stop'=>120);
    	        }
    	        $intPrio1 = (count($arrPrioW)) ? min($arrPrioW) : 0 ;
    	        $intPrio2 = (count($arrPrioW)) ? max($arrPrioW) : 0 ;
    	        if ($intPrio1>0) 
    	        {
    	            $intWeightingHigh = mt_rand($arrPrio[$intPrio1]['start'],$arrPrio[$intPrio2]['stop']);
    	            // 1-180 auf 1-3 umrechnen
    	            if ($intWeightingHigh<=$arrPrio[3]['stop']) 
    	            {
    	            	$intWeighting=3;
    	            }
    	            if ($intWeightingHigh<=$arrPrio[2]['stop']) 
    	            {
    	            	$intWeighting=2;
    	            }
    	            if ($intWeightingHigh<=$arrPrio[1]['stop']) 
    	            {
    	            	$intWeighting=1;
    	            }
    	        } 
    	        else 
    	        {
    	            $intWeighting=0;
    	        }
    			
    		    // Banner suchen...
    	        $intRandomBlocker = " AND TLB.id !=" .$this->BannerGetRandomBlocker();
    	        $maxloop =0;
    	        do 
    	        {
        	        $objBanners = $this->Database->prepare("SELECT TLB.id FROM tl_banner AS TLB "
        	                                            . " LEFT JOIN tl_banner_category ON (tl_banner_category.id=TLB.pid)"
        	                                            . " LEFT OUTER JOIN tl_banner_stat AS TLS ON TLB.id=TLS.id"
        	                                            . " WHERE pid IN(" . implode(',', $this->arrBannerCategories) . ")"
        	                                            . " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_views_until>TLS.banner_views)   OR (TLB.banner_until=1 AND TLB.banner_views_until=?)  OR (TLB.banner_until=1 AND TLS.banner_views is NULL))"
        			                                    . " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_clicks_until>TLS.banner_clicks) OR (TLB.banner_until=1 AND TLB.banner_clicks_until=?) OR (TLB.banner_until=1 AND TLS.banner_clicks is NULL))"
        	                                            . " AND TLB.banner_weighting =? AND TLB.banner_published =1"
        	                                            . " AND (TLB.banner_start=? OR TLB.banner_start<=?) AND (TLB.banner_stop=? OR TLB.banner_stop>=?)"
        	                                            . " AND (TLB.banner_domain=? OR RIGHT(?, CHAR_LENGTH(TLB.banner_domain)) = TLB.banner_domain)"
        	                                            .$strSqlExcludeSeen
        	                                            .$intRandomBlocker
        	                                            )
        										 ->execute('', '', '', '', $intWeighting, '', $intTime, '', $intTime, '', $http_host);
        			$intRows = $objBanners->numRows;
        			$intRandomBlocker=''; //next loop without RandomBlocker
        			$maxloop++;
        			//log_message('BannerSingle Banner Loop '.$maxloop,'Banner.log');
    	        } while ( ($intRows ==0) && ($maxloop<2));
    	
    			if($intRows == 1) 
    			{ // one Banner
    			    $intShowBannerId = 0;
    			}
    			if($intRows >1 )  
    			{ // more Banners
    			    $intShowBannerId =  mt_rand(0,$intRows-1);
    			}
    			if ($intShowBannerId>-1) 
    			{
    	    		// direkt mit Limit und offset
    	            //$objBanners = $this->Database->prepare("SELECT TLB.*, banner_template FROM tl_banner AS TLB "
    			    $intRandomBlocker = " AND TLB.id !=" .$this->BannerGetRandomBlocker();
    			    $maxloop =0;
    			    do
    			    {
        	            $objBanners = $this->Database->prepare("SELECT TLB.* FROM tl_banner AS TLB "
        	                                                . " LEFT JOIN tl_banner_category ON (tl_banner_category.id=TLB.pid)"
        	                                                . " LEFT OUTER JOIN tl_banner_stat AS TLS ON TLB.id=TLS.id"
        	                                                . " WHERE pid IN(" . implode(',', $this->arrBannerCategories) . ")"
        	                                                . " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_views_until>TLS.banner_views)   OR (TLB.banner_until=1 AND TLB.banner_views_until=?)  OR (TLB.banner_until=1 AND TLS.banner_views is NULL))"
        			                                        . " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_clicks_until>TLS.banner_clicks) OR (TLB.banner_until=1 AND TLB.banner_clicks_until=?) OR (TLB.banner_until=1 AND TLS.banner_clicks is NULL))"
        	                                                . " AND TLB.banner_weighting =? AND TLB.banner_published =1"
        	                                                . " AND (TLB.banner_start=? OR TLB.banner_start<=?) AND (TLB.banner_stop=? OR TLB.banner_stop>=?)"
        	                                                . " AND (TLB.banner_domain=? OR RIGHT(?, CHAR_LENGTH(TLB.banner_domain)) = TLB.banner_domain)"
        	                                                .$strSqlExcludeSeen
        	                                                .$intRandomBlocker
        	                                                )
        	                                         ->limit(1,$intShowBannerId)
        	    									 ->execute('', '', '', '', $intWeighting, '', $intTime, '', $intTime, '', $http_host);
        	    		$intRows = $objBanners->numRows;
        	    		$intRandomBlocker=''; //next loop without RandomBlocker
        	    		$maxloop++;
        	    		//log_message('BannerSingle BannerLimit Loop '.$maxloop,'Banner.log');
    			    } while ( ($intRows ==0) && ($maxloop<2));
    			}
		    } // else no firstview		
		} 
		else 
		{
			/*
			_  _ _  _ _    ___ _    ___  ____ _  _ _  _ ____ ____ 
			|\/| |  | |     |  |    |__] |__| |\ | |\ | |___ |__/ 
			|  | |__| |___  |  |    |__] |  | | \| | \| |___ |  \ 
			*/
			// Sortiert oder Random
			if ($bolBRAND) 
			{
				$strBannerSort = 'RAND()';
			} 
			else 
			{
				$strBannerSort = 'sorting';
			}
			//$objBanners = $this->Database->prepare("SELECT TLB.*, banner_template FROM tl_banner AS TLB "
			$objBannersStmt = $this->Database->prepare("SELECT TLB.* FROM tl_banner AS TLB "
	                                                . " LEFT JOIN tl_banner_category ON (tl_banner_category.id=TLB.pid)"
	                                                . " LEFT OUTER JOIN tl_banner_stat AS TLS ON TLB.id=TLS.id"
	                                                . " WHERE pid=?"
	                                                . " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_views_until>TLS.banner_views)   OR (TLB.banner_until=1 AND TLB.banner_views_until=?)  OR (TLB.banner_until=1 AND TLS.banner_views is NULL))"
			                                        . " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_clicks_until>TLS.banner_clicks) OR (TLB.banner_until=1 AND TLB.banner_clicks_until=?) OR (TLB.banner_until=1 AND TLS.banner_clicks is NULL))"
	                                                . " AND TLB.banner_published =1"
	                                                . " AND (TLB.banner_start=? OR TLB.banner_start<=?) AND (TLB.banner_stop=? OR TLB.banner_stop>=?)"
			                                        . " AND (TLB.banner_domain=? OR RIGHT(?, CHAR_LENGTH(TLB.banner_domain)) = TLB.banner_domain)"
	                                                . " ORDER BY banner_weighting, ".$strBannerSort."");
			if ($intBannerLimit > 0) 
			{
				$objBannersStmt->limit($intBannerLimit);
			}
			$objBanners = $objBannersStmt->executeUncached($intBannerCategory, '', '', '', '', '', $intTime, '', $intTime, '', $http_host);
	    	$intRows = $objBanners->numRows;
		}
		/*
		___  ____ _  _ _  _ ____ ____    _  _ ____ ____ _  _ ____ _  _ ___  ____ _  _ 
		|__] |__| |\ | |\ | |___ |__/    |  | |  | |__/ |__| |__| |\ | |  \ |___ |\ | 
		|__] |  | | \| | \| |___ |  \     \/  |__| |  \ |  | |  | | \| |__/ |___ | \| 
		*/
		if($intRows > 0)  
		{
			if (version_compare(VERSION, '2.9', '>'))
			{
				// Contao 2.10 beta and above
    			global $objPage;
				if ($objPage->outputFormat == 'html5')
				{
					$this->strFormat = 'html5';
				}
			}
            while ($objBanners->next()) 
            {
	            self::$arrBannerSeen[] = $objBanners->id;
	            if (!$this->statusRandomBlocker) {
	                $this->BannerSetRandomBlocker($objBanners->id);
	            }
			    $arrValue = deserialize($objBanners->banner_imgSize);
			    $size[0] = '';
			    $size[1] = '';
			    $size[3] = '';
			    $oriSize = false;
			    $arrBanners = array();
			    // 1 = GIF, 2 = JPG, 3 = PNG
			    // 4 = SWF, 13 = SWC (zip-like swf file)
			    // 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order)
			    // 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF
			    if ($objBanners->banner_type == 'banner_image') {
	    		    //Interne Banner Grafik
	    		    $arrImageSize = @getimagesize(TL_ROOT . '/' . $objBanners->banner_image);
	    		    if ($arrImageSize===false) {
				    	//Workaround fuer PHP ohne zlib bei SWC Files
				    	$arrImageSize = $this->getimagesizecompressed(TL_ROOT . '/' . $objBanners->banner_image);
				    }
	    		} 
	    		if ($objBanners->banner_type == 'banner_image_extern') {
	                $arrImageSize = $this->getImageSizeExternal($objBanners->banner_image_extern);
	    		}
	    		if ($objBanners->banner_type == 'banner_text') {
	    			$arrImageSize = false;
	    		}
	    		//Banner Ziel per Page?
	            if ($objBanners->banner_jumpTo >0) {
	            	//url generieren
	            	$objBannerNextPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
	                                                    ->limit(1)
	                                                    ->execute($objBanners->banner_jumpTo);
	            	if ($objBannerNextPage->numRows)
	            	{
	            		$objBanners->banner_url = $this->generateFrontendUrl($objBannerNextPage->fetchAssoc());
	            	} 
	            }
	            if ($arrImageSize !== false) 
	            {
	    		    if ($arrValue[0]>0 && $arrValue[1]>0) {
	    		        $size[0] = $arrValue[0];  // neue Breite
	    		        $size[1] = $arrValue[1];  // neue Höhe
	    		    } elseif ($arrValue[0]>0) {
	    		    	$size[0] = $arrValue[0];  // nur neue Breite
	    		    	$size[1] = ceil($size[0]*$arrImageSize[1]/$arrImageSize[0]);
	    		    } elseif ($arrValue[1]>0) {
	    		    	$size[1] = $arrValue[1];  // nur neue Höhe
	    		    	$size[0] = ceil($size[1]*$arrImageSize[0]/$arrImageSize[1]);
	    		    } else {
	    		          $size[0] = $arrImageSize[0];  // eigene Breite (Flash braucht das)
	    		          $size[1] = $arrImageSize[1];  // eigene Höhe   (Flash braucht das)
	    		          $oriSize = true; // Merkmal fuer Bilder ohne Umrechnung
	    		    }
	    		    if ($this->strFormat == 'xhtml') {
	    		    	$banner_target = ($objBanners->banner_target == '1') ? LINK_BLUR : LINK_NEW_WINDOW;
	    		    } else {
	    		    	$banner_target = ($objBanners->banner_target == '1') ? '' : ' target="_blank"';
	    		    }
	                switch ($arrImageSize[2]) {
	                	case 1:
	                	case 2:
	                    case 3:
	                        if ($objBanners->banner_type == 'banner_image') 
	                        {
	                            //Interne Banner Grafik
	                            if ($oriSize) {
	                            	//Bild ohne Umrechnug wichtig fuer GIF
	                            	$src = $this->urlEncode($objBanners->banner_image);
	                            } 
	                            else 
	                            {
	                                $src = $this->getImage($this->urlEncode($objBanners->banner_image), $size[0], $size[1]);
	                            }
	                            if (($imgSize = @getimagesize(TL_ROOT . '/' . $src)) !== false)
	                    		{
	                    			$size[3] = ' ' . $imgSize[3];
	                    		}
	                        } 
	                        else 
	                        {
	                            //Externe Banner Grafik
	                            //Umwandlung bei Parametern
	                            $src = html_entity_decode($objBanners->banner_image_extern, ENT_NOQUOTES, 'UTF-8');
	                            //$src = $objBanners->banner_image_extern;
	                            $size[3] = ' height="'.$size[1].'" width="'.$size[0].'"';
	                        }
	                        //First Line for title
	                        //strpos: This functions throws an "Offset not contained in string" 
	                        //        error if the offset is not in between 0 and the length of string.
	                        if ( strlen($objBanners->banner_comment) > 1 )
	                        {
	                            $banner_comment_pos = strpos($objBanners->banner_comment,"\n",1);
    	                        if ($banner_comment_pos !== false) 
    	                        {
    	                            $objBanners->banner_comment = substr($objBanners->banner_comment,0,$banner_comment_pos);
    	                        }
	                        }
	            		    $arrBanners[] = array
	            			(
	            			    'banner_key'     => 'bid=',
	            			    'banner_id'      => $objBanners->id,
	            				'banner_name'    => specialchars(ampersand($objBanners->banner_name)),
	            				'banner_url'     => $objBanners->banner_url,
	            				'banner_target'  => $banner_target,
	            				'banner_comment' => specialchars(ampersand($objBanners->banner_comment)),
	            				'src'            => specialchars(ampersand($src)),
	            				'alt'            => specialchars(ampersand($objBanners->banner_name)),
	            				'size'           => $size[3],
	            				'banner_pic'     => true,
	            				'banner_flash'   => false,
	            				'banner_text'    => false,
	            				'banner_empty'   => false	// issues 733
	            			);
	                		break;
	                    case 4:  // Flash swf
	                    case 13: // Flash swc
	                    	list($usec, ) = explode(" ", microtime());
	                    	//Check for Fallback Image, only for local flash files
	            			if ($objBanners->banner_type == 'banner_image') 
	            			{
	            				$fallback_ext = 'text';
	            				$path_parts = pathinfo($objBanners->banner_image);
	            				if (@getimagesize(TL_ROOT . '/' . $path_parts['dirname'].'/'.$path_parts['filename'].'.jpg') !== false) {
	            					$fallback_ext = '.jpg';
	            				} elseif (@getimagesize(TL_ROOT . '/' . $path_parts['dirname'].'/'.$path_parts['filename'].'.png') !== false) {
	            					$fallback_ext = '.png';
	            				} elseif (@getimagesize(TL_ROOT . '/' . $path_parts['dirname'].'/'.$path_parts['filename'].'.gif') !== false) {
	            					$fallback_ext = '.gif';
	            				}
	            				if ($fallback_ext == 'text') 
	            				{
	            					if ($this->strFormat == 'xhtml') 
	            					{
	            						$fallback_content = $objBanners->banner_image ."<br />". specialchars(ampersand($objBanners->banner_comment)) ."<br />". specialchars(ampersand($objBanners->banner_name)); 
	            					} else {
	            						$fallback_content = $objBanners->banner_image ."<br>". specialchars(ampersand($objBanners->banner_comment)) ."<br>". specialchars(ampersand($objBanners->banner_name)); 
	            					}
	            				} else {
	            					//Get Image with sizes of flash
	            					$src_fallback = $this->getImage($this->urlEncode($path_parts['dirname'].'/'.$path_parts['filename'].$fallback_ext), $size[0], $size[1],'proportional');
	            					if ($this->strFormat == 'xhtml') {
	            						$fallback_content = '<img src="' . $src_fallback . '" alt="'.specialchars(ampersand($objBanners->banner_comment)).'" height="'.$size[1].'" width="'.$size[0].'" />'; 
	            					} else {
	            						$fallback_content = '<img src="' . $src_fallback . '" alt="'.specialchars(ampersand($objBanners->banner_comment)).'" height="'.$size[1].'" width="'.$size[0].'">'; 
	            					}
	            				}
	            			}
	                        $arrBanners[] = array
	                		(
	            			    'banner_key'     => 'bid=',
	                		    'banner_id'      => $objBanners->id,
	            				'banner_name'    => specialchars(ampersand($objBanners->banner_name)),
	            				'banner_url'     => $objBanners->banner_url,
	            				'banner_target'  => $banner_target,
	            				'banner_comment' => specialchars(ampersand($objBanners->banner_comment)),
	            				'swf_src'        => ($objBanners->banner_type == 'banner_image') ? $objBanners->banner_image : $objBanners->banner_image_extern,
	            				'swf_width'      => $size[0],
	            				'swf_height'     => $size[1],
	            				'swf_id'         => round((float)$usec*100000,0).'_'.$objBanners->id,
	            				'alt'            => specialchars(ampersand($objBanners->banner_name)),
	            				'fallback_content'=> $fallback_content,
	            				'banner_pic'     => false,
	            				'banner_flash'   => true,
	            				'banner_text'    => false,
	            				'banner_empty'   => false	// issues 733
	            			);
	            			// Add JavaScript
			                //not in TL2.8 // $GLOBALS['TL_JAVASCRIPT'][] = 'plugins/swfobject/swfobject.js'; 
	                        break;
	                	default:
	                	    $arrBanners[] = array
	                		(
	                		    'banner_key'     => 'bid=',
	                		    'banner_id'      => 0,
	                			'banner_name'    => '',
	                			'banner_url'     => '',
	                			'banner_target'  => '',
	                			'banner_comment' => '',
	                			'src'            => '',
	                			'alt'            => '',
	                			'size'           => '',
	                			'banner_pic'     => true,
	                		);
	                		break;
	                } //switch
	                //if (($objBanners->banner_template != $this->strTemplate) && ($objBanners->banner_template != '')) {
	                if (($this->banner_template != $this->strTemplate) && ($this->banner_template != '')) 
	                {
	                    $this->strTemplate = $this->banner_template;
	                    $this->Template = new FrontendTemplate($this->strTemplate);
	    		    }
	                $arrResults[] = $arrBanners[0];
	        		//$this->Template->banners = $arrResults;
	        		
	        		$this->arrBannerData = $arrResults;
	        		$this->BannerStatViewUpdate();
	    		} 
	    		else 
	    		{
	    			if ($objBanners->banner_type != 'banner_text') 
	    			{
	        		    // read Error, empty template
		    		    $banner_error = ($objBanners->banner_type == 'banner_image') ? $objBanners->banner_image : $objBanners->banner_image_extern;
		    		    $this->log('Banner File read error '.$banner_error.'', 'ModulBanner Compile', 'ERROR');
		    		    $this->strTemplate='mod_banner_empty';
		                $this->Template = new FrontendTemplate($this->strTemplate);
		                //$this->Template->banners = $arrResults; 
	                } 
	                else 
	                {
	                	// Text Banner
	                	// Kurz URL (nur Domain)
	                	$treffer = '';
	                	if (preg_match('@^(?:http://)?([^/]+)@i',$objBanners->banner_url, $treffer))
	                	{
	                		$banner_url_kurz = $treffer[1];
	                	} else {
	                		$banner_url_kurz = '';
	                	}
	                	if ($this->strFormat == 'xhtml') {
	    		    		$banner_target = ($objBanners->banner_target == '1') ? LINK_BLUR : LINK_NEW_WINDOW;
		    		    } else {
		    		    	$banner_target = ($objBanners->banner_target == '1') ? '' : ' target="_blank"';
		    		    }
	                	$arrBanners[] = array
	        			(
	        			    'banner_key'     => 'bid=',
	        			    'banner_id'      => $objBanners->id,
	        				'banner_name'    => specialchars(ampersand($objBanners->banner_name)),
	        				'banner_url'     => $objBanners->banner_url,
	        				'banner_url_kurz'=> $banner_url_kurz,
	        				'banner_target'  => $banner_target,
	        				'banner_comment' => ampersand(nl2br($objBanners->banner_comment)),
	        				'banner_pic'     => false,
	        				'banner_flash'   => false,
	        				'banner_text'    => true,
	        				'banner_empty'   => false	// issues 733
	        			);
	        			//if (($objBanners->banner_template != $this->strTemplate) && ($objBanners->banner_template != '')) {
	        			if (($this->banner_template != $this->strTemplate) && ($this->banner_template != '')) {
			                $this->strTemplate = $this->banner_template;
			                $this->Template = new FrontendTemplate($this->strTemplate);
					    }
			            $arrResults[] = $arrBanners[0];
			    		//$this->Template->banners = $arrResults;
			    		
			    		$this->arrBannerData = $arrResults;
			    		$this->BannerStatViewUpdate();
	                }
	    		}
			} // while schleife alle Banner bzw. ein Banner
			$this->Template->banners = $arrResults;
		} 
		else 
		{
			/*
			_  _ ____ _ _  _    ___  ____ _  _ _  _ ____ ____    _  _ ____ ____ _  _ ____ _  _ ___  ____ _  _ 
			|_/  |___ | |\ |    |__] |__| |\ | |\ | |___ |__/    |  | |  | |__/ |__| |__| |\ | |  \ |___ |\ | 
			| \_ |___ | | \|    |__] |  | | \| | \| |___ |  \     \/  |__| |  \ |  | |  | | \| |__/ |___ | \| 
			*/
		    // Default Banner definiert?
		    //$objBanners = $this->Database->prepare("SELECT id,banner_template,banner_default_image,banner_default_name,banner_default_url,banner_default_target FROM tl_banner_category "
		    $objBanners = $this->Database->prepare("SELECT id,banner_default_image,banner_default_name,banner_default_url,banner_default_target FROM tl_banner_category "
                                                . " WHERE id IN (" . implode(',', $this->arrBannerCategories) . ")"
                                                . " AND banner_default =? "
                                                . " AND banner_default_image !=? ")
    									 ->execute('1', '');
			$intDefaultRows = $objBanners->numRows;
			if ($intDefaultRows) 
			{
				/*
				___  ____ ____ ____ _  _ _    ___    ___  ____ _  _ _  _ ____ ____    ___  ____ ____ _ _  _ _ ____ ____ ___ 
				|  \ |___ |___ |__| |  | |     |     |__] |__| |\ | |\ | |___ |__/    |  \ |___ |___ | |\ | | |___ |__/  |  
				|__/ |___ |    |  | |__| |___  |     |__] |  | | \| | \| |___ |  \    |__/ |___ |    | | \| | |___ |  \  | 
				*/				
				//falls $intDefaultRows>1 dann auf einen Treffer filtern
				$DefaultBannerNr = mt_rand(0,$intDefaultRows-1);
				for ($dbnr=0; $dbnr<=$DefaultBannerNr; $dbnr++) {
				    $objBanners->next();
				}
				//Template check
				//if (($objBanners->banner_template != $this->strTemplate) && ($objBanners->banner_template != '')) {
				if (($this->banner_template != $this->strTemplate) && ($this->banner_template != '')) {
                    $this->strTemplate = $this->banner_template;
                    $this->Template = new FrontendTemplate($this->strTemplate);
    		    }
    		    if ($this->strFormat == 'xhtml') {
    		    	$banner_default_target = ($objBanners->banner_default_target == '1') ? LINK_BLUR : LINK_NEW_WINDOW;
    		    } else {
    		    	$banner_default_target = ($objBanners->banner_default_target == '1') ? '' : ' target="_blank"';
    		    }
				//Banner Art bestimmen
				$arrImageSize = @getimagesize(TL_ROOT . '/' . $objBanners->banner_default_image);
    		    if ($arrImageSize===false) {
			    	//Workaround fuer PHP ohne zlib bei SWC Files
			    	$arrImageSize = $this->getimagesizecompressed(TL_ROOT . '/' . $objBanners->banner_default_image);
			    }
			    switch ($arrImageSize[2]) {
                	case 1:
                	case 2:
                    case 3:
						$arrBanners[] = array
		    			(
		    			    'banner_key'     => 'defbid=',
		    			    'banner_id'      => $objBanners->id,
		    				'banner_name'    => specialchars(ampersand($objBanners->banner_default_name)),
		    				'banner_url'     => $objBanners->banner_default_url,
		    				'banner_target'  => $banner_default_target,
		    				'banner_comment' => specialchars(ampersand($objBanners->banner_default_name)),
		    				'src'            => $this->urlEncode($objBanners->banner_default_image),
		    				'alt'            => specialchars(ampersand($objBanners->banner_default_name)),
		    				'size'     		 => '',
		    				'banner_pic'     => true,
		    				'banner_flash'   => false,
		    				'banner_text'    => false,
		    				'banner_empty'   => false	// issues 733
		    			);
		    			break;
                    case 4:  // Flash swf
                    case 13: // Flash swc
                    	list($usec, ) = explode(" ", microtime());
                        $arrBanners[] = array
                		(
            			    'banner_key'     => 'defbid=',
                		    'banner_id'      => $objBanners->id,
            				'banner_name'    => specialchars(ampersand($objBanners->banner_default_name)),
            				'banner_url'     => $objBanners->banner_default_url,
            				'banner_target'  => $banner_default_target,
            				'banner_comment' => specialchars(ampersand($objBanners->banner_default_name)),
            				'swf_src'        => $objBanners->banner_default_image,
            				'swf_width'      => $arrImageSize[0],
            				'swf_height'     => $arrImageSize[1],
            				'swf_id'         => round((float)$usec*100000,0).'_'.$objBanners->id,
            				'alt'            => specialchars(ampersand($objBanners->banner_default_name)),
            				'banner_pic'     => false,
            				'banner_flash'   => true,
            				'banner_text'    => false,
            				'banner_empty'   => false	// issues 733
            			);
            			// Add JavaScript
		                //not in TL2.8 // $GLOBALS['TL_JAVASCRIPT'][] = 'plugins/swfobject/swfobject.js'; 
                        break;
			    }
    			$arrResults[] = $arrBanners[0];
        		$this->Template->banners = $arrResults;
			} 
			else 
			{
				/*
				_  _ ____ _ _  _    ___  ____ ____ ____ _  _ _    ___    ___  ____ _  _ _  _ ____ ____ 
				|_/  |___ | |\ |    |  \ |___ |___ |__| |  | |     |     |__] |__| |\ | |\ | |___ |__/ 
				| \_ |___ | | \|    |__/ |___ |    |  | |__| |___  |     |__] |  | | \| | \| |___ |  \ 
				*/
    			$NoBannerFound = ($GLOBALS['TL_LANG']['MSC']['tl_banner']['noBanner']) ? $GLOBALS['TL_LANG']['MSC']['tl_banner']['noBanner'] : 'no banner, no default banner';
    			$arrBanners[] = array
        		(
        		    'banner_key'  => 'bid=',
        		    'banner_id'   => 0,
        			'banner_name' => specialchars(ampersand($NoBannerFound)),
        			'banner_url'  => '',
        			'banner_target'  => '',
        			'banner_comment' => '',
        			'src' 			=> '',
        			'alt' 			=> '',
        			'size'     		=> '',
        			'banner_pic' 	=> false,
    				'banner_flash'  => false,
    				'banner_text'   => false,
    				'banner_empty'  => true	// issues 733
        		);
                $arrResults[] = $arrBanners[0];
                // Banner ausblenden wenn kein Banner vorhanden?
                //test, muesste auch so gehen, kommt ja ueber tl_module mit
                if ($this->banner_hideempty == 1) 
                {
                    // auf Leer umschalten
                    $this->strTemplate='mod_banner_empty';
                    $this->Template = new FrontendTemplate($this->strTemplate);
                }
                /*
                $objBannersHide = $this->Database->prepare("SELECT banner_hideempty FROM tl_module WHERE type =?")
        									     ->execute('Banner');
                $objBannersHide->next();
                if ($objBannersHide->banner_hideempty == 1) {
                    // auf Leer umschalten
                    $this->strTemplate='mod_banner_empty';
                    $this->Template = new FrontendTemplate($this->strTemplate); 
                }*/
                // Anzeigen
        		$this->Template->banners = $arrResults;
        		// keine Statistik
    		}
		} // else keine Banner
	}

	/**
	 * Insert/Update Banner View Stat
	 */
	protected function BannerStatViewUpdate()
	{
	    if ($this->BannerCheckBot() == true) 
	    {
	    	return; //Bot gefunden, wird nicht gezaehlt
	    }
	    if ($this->CheckUserAgent() == true) 
	    {
	    	return ; //User Agent Filterung
	    }
	    // Blocker
	    $intCatID = ($this->arrBannerCategories[0] >0) ? $this->arrBannerCategories[0] : 42 ; // Answer to the Ultimate Question of Life, the Universe, and Everything
	    //log_message('BannerStatViewUpdate $intCatID:'.$intCatID,'Banner.log');
	    $ClientIP = bin2hex(sha1($intCatID . $this->Environment->remoteAddr,true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    $lastBanner = array_pop($this->arrBannerData);
	    $BannerID = $lastBanner['banner_id'];
	    if ($BannerID==0) 
	    { // kein Banner, nichts zu tun
	        return;
	    }
	    $BannerBlockTime = time() - 60*10;   // 10 Minuten, 0-10 min wird geblockt
	    $BannerCleanTime = time() - 60*10*3; // 30 Minuten, Einträge >= 30 Minuten werden gelöscht
	    if (isset($GLOBALS['TL_CONFIG']['mod_banner_block_time']) && intval($GLOBALS['TL_CONFIG']['mod_banner_block_time'])>0) 
	    {
	        $BannerBlockTime = time() - 60*intval($GLOBALS['TL_CONFIG']['mod_banner_block_time']);
	        $BannerCleanTime = time() - 60*3*intval($GLOBALS['TL_CONFIG']['mod_banner_block_time']);
	    }
	    $this->Database->prepare("DELETE FROM tl_banner_blocker WHERE tstamp<? AND type=?")
	                   ->execute($BannerCleanTime, 'v');

	    $objBanners = $this->Database->prepare("SELECT id FROM tl_banner_blocker WHERE bid=? AND tstamp>? AND ip=? AND type=?")
								 	 ->limit(1)
									 ->execute( $BannerID, $BannerBlockTime, $ClientIP, 'v' );
		if (0 == $objBanners->numRows) 
		{
		    // noch kein Eintrag bzw. ausserhalb Blockzeit
		    $arrSet = array
            (
                'bid'    => $BannerID,
                'tstamp' => time(),
                'ip'     => $ClientIP,
                'type'   => 'v'
            );
		    $this->Database->prepare("INSERT INTO tl_banner_blocker %s")->set($arrSet)->execute();
		    // nicht blocken
		} 
		else 
		{
			// Eintrag innerhalb der Blockzeit
			return; // blocken, nicht zählen
		}

	    //alte Daten lesen
		$objBanners = $this->Database->prepare("SELECT * FROM tl_banner_stat WHERE id=?")
									 ->limit(1)
									 ->execute($BannerID);
        $objBanners->fetchAssoc();
		if (0 == $objBanners->numRows) 
		{
		    //insert
		    $arrSet = array
            (
                'id'     => $BannerID,
                'tstamp' => time(),
                'banner_views' => 1
            );
		    $this->Database->prepare("INSERT INTO tl_banner_stat %s")->set($arrSet)->execute();
		} 
		else 
		{
		    //update
   		    $arrSet = array
            (
                'id'     => $BannerID,
                'tstamp' => time(),
                'banner_views' => $objBanners->banner_views + 1
            );
            $this->Database->prepare("UPDATE tl_banner_stat SET tstamp=?,banner_views=? WHERE id=?")
						   ->execute($arrSet['tstamp'], $arrSet['banner_views'], $arrSet['id']);
		}
	} // BannerStatViewUpdate	

	/**
	 * Random Blocker, Set Banner-ID
	 */
	protected function BannerSetRandomBlocker($BannerID=0)
	{
	    // Blocker
	    $intCatID = ($this->arrBannerCategories[0] >0) ? $this->arrBannerCategories[0] : 42 ; // Answer to the Ultimate Question of Life, the Universe, and Everything
	    //log_message('BannerStatViewUpdate $intCatID:'.$intCatID,'Banner.log');
	    $ClientIP = bin2hex(sha1($intCatID . $this->Environment->remoteAddr,true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    //log_message('BannerSetRandomBlocker $bid:'.$BannerID,'Banner.log');
	    if ($BannerID==0) 
	    { // kein Banner, nichts zu tun
	        return;
	    }
	    $this->Database->prepare("DELETE FROM tl_banner_random_blocker WHERE ip=?")
	                   ->execute($ClientIP);
	    $arrSet = array
	    (
	            'bid'    => $BannerID,
	            'tstamp' => time(),
	            'ip'     => $ClientIP,
	    );
	    $this->Database->prepare("INSERT INTO tl_banner_random_blocker %s")->set($arrSet)->execute();
	    $this->statusRandomBlocker = true;
	    return ;
	}
	
	/**
	 * Random Blocker, Get Banner-ID
	 * @return    integer    Banner-ID
	 */
	protected function BannerGetRandomBlocker()
	{
	    // Blocker
	    $intCatID = ($this->arrBannerCategories[0] >0) ? $this->arrBannerCategories[0] : 42 ; // Answer to the Ultimate Question of Life, the Universe, and Everything
	    //log_message('BannerStatViewUpdate $intCatID:'.$intCatID,'Banner.log');
	    $ClientIP = bin2hex(sha1($intCatID . $this->Environment->remoteAddr,true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    $objBanners = $this->Database->prepare("SELECT * FROM tl_banner_random_blocker WHERE ip=?")
                    	   ->limit(1)
                    	   ->execute($ClientIP);
	    $objBanners->fetchAssoc();
	    if (0 == $objBanners->numRows) 
	    {
	        return 0;
	    }
	    else
	    {
	        return $objBanners->bid;
	    }
	}

	protected function BannerGetFirstView() 
	{
	    //ugly hack, bid is here category, not banner id
	    $cid = ($this->arrBannerCategories[0] >0) ? $this->arrBannerCategories[0] : 42 ; // Answer to the Ultimate Question of Life, the Universe, and Everything
	    $ClientIP = bin2hex(sha1($cid . $this->Environment->remoteAddr,true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    $BannerFirstViewBlockTime = time() - 60*10; // 10 Minuten, Einträge >= 10 Minuten werden gelöscht
	    
	    $this->import('ModuleVisitorReferrer');
	    $this->ModuleVisitorReferrer->checkReferrer();
	    $ReferrerDNS = $this->ModuleVisitorReferrer->getReferrerDNS();
	    // o own , w wrong

	    if ($ReferrerDNS === 'o')
	    {
	        // eigener Referrer, Begrenzung auf First View nicht nötig.
	        $this->statusBannerFirstView = false;
	        return false;
	    }
	    
	    $this->Database->prepare("DELETE FROM tl_banner_blocker WHERE bid =? AND tstamp<? AND type=?")
	                   ->execute($cid, $BannerFirstViewBlockTime, 'f');
	    $objBanners = $this->Database->prepare("SELECT id FROM tl_banner_blocker WHERE bid =? AND tstamp>? AND ip=? AND type=?")
                    	   ->limit(1)
                    	   ->executeUncached($cid, $BannerFirstViewBlockTime, $ClientIP, 'f' );
	    if (0 == $objBanners->numRows) 
	    {
	        // noch kein Eintrag bzw. ausserhalb Blockzeit
	        $arrSet = array
	        (
	                'bid'    => $cid,
	                'tstamp' => time(),
	                'ip'     => $ClientIP,
	                'type'   => 'f'
	        );
	        $this->Database->prepare("INSERT INTO tl_banner_blocker %s")->set($arrSet)->executeUncached();
	        // kein firstview block gefunden, Anzeigen erlaubt
	        $this->statusBannerFirstView = true;
	        return true;
	    } 
	    else 
	    {
	        $this->statusBannerFirstView = false;
	        return false;
	    }
	}
	
	
	
	/**
	 * Spider Bot Check
	 */
	protected function BannerCheckBot()
	{
	    if (isset($GLOBALS['TL_CONFIG']['mod_banner_bot_check']) && intval($GLOBALS['TL_CONFIG']['mod_banner_bot_check'])==0) 
	    {
	        //log_message('BannerCheckBot abgeschaltet','Banner.log');
	        return false; //Bot Suche abgeschaltet ueber localconfig.php
	    }
	    if (!in_array('botdetection', $this->Config->getActiveModules()))
		{
			//botdetection Modul fehlt, Abbruch
			$this->log('BotDetection extension required!', 'ModulBanner BannerCheckBot', TL_ERROR);
			return false;
		}
	    // Import Helperclass ModuleBotDetection
	    $this->import('ModuleBotDetection');
	    if ($this->ModuleBotDetection->BD_CheckBotAgent() || $this->ModuleBotDetection->BD_CheckBotIP()) 
	    {
	    	//log_message('BannerCheckBot True','Banner.log');
	    	return true;
	    }
	    //log_message('BannerCheckBot False','Banner.log');
	    return false;
	} //BannerCheckBot
	
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
	    $tmpImage = 'system/tmp/mod_banner_fe_'.$token.'.tmp';
	    $objRequest = new Request();
		$objRequest->send(html_entity_decode($BannerImageExternal, ENT_NOQUOTES, 'UTF-8'));
		// Test auf chunked
		if ( array_key_exists('Transfer-Encoding',$objRequest->headers) && $objRequest->headers['Transfer-Encoding'] == 'chunked') {
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
				} else {
				    log_message('[getimagesizeexternal] tmpFile Problem: error', 'debug.log');
				}
				return false;
			}
		} else {
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
				} else {
				    log_message('[getimagesizeexternal] tmpFile Problem: error', 'debug.log');
				}
				return false;
			} 
		}
		$objRequest=null;
		unset($objRequest);
		$arrImageSize = @getimagesize(TL_ROOT . '/' . $tmpImage);
		if ($arrImageSize===false) {
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
        while (trim($chunked)) {
            preg_match("/^([\da-fA-F]+)[^\r\n]*\r\n/sm", $chunked, $m);
            $length = hexdec(trim($m[1]));
            $cut = strlen($m[0]);

            $decBody .= substr($chunked, $cut, $length);
            $chunked = substr($chunked, $cut + $length + 2);
        }
        return $decBody;
    }
	
	private function swc_data($filename) {
		$size = 0;
		$width = 0;
		$height = 0;
	
		$file = @fopen($filename,"rb") ;
		if (!$file) {
			return false;
		}
		if ("CWS" != fread($file,3)) {
			return false;
		} 
		// Version
		fread($file,1) ;
		for ($i=0;$i<4;$i++) {
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
		for ($vals=0;$vals<4;$vals++) {
			$bitcount = 0 ;
			while ($bitcount<$bits) {
				if ($cbyte&128) {
					$cval .= "1" ;
				} else {
					$cval .= "0" ;
					}
				$cbyte<<=1 ;
				$cbyte &= 255 ;
				$cbit-- ;
				$bitcount++ ;
				if ($cbit<0) {
					$cbyte	= ord(substr($buffer,0,1)) ;
					$buffer = substr($buffer,1) ;
					$cbit = 7 ;
					}
			  }
			$c 		= 1 ;
			$val 	= 0 ;
			$tval = strrev($cval) ;
			for ($n=0;$n<strlen($tval);$n++) {
				$atom = substr($tval,$n,1) ;
				if ($atom=="1") $val+=$c ;
				$c*=2 ;
			  }
			// TWIPS to PIXELS
			$val/=20 ;
			switch ($vals) {
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
		if ($res) {
			// width,height
			$arrImageSize = array($res[0], $res[1], 13);
		}
		return $arrImageSize;
	}
	
	/**
	 * Check FE Login
	 * 
	 * @return bool    true=View OK, false=FE User logged in and nothing is allowed to view
	 */
	public function BannerCheckFE()
	{
	    if (FE_USER_LOGGED_IN)
		{
		    $fe_groups = array();
		    $idg = array();
			$this->import('FrontendUser', 'User');
			$fe_groups = $this->User->groups; //array
    
    	    //$firephp = FirePHP::getInstance(true);
    	    //$firephp->log('BannerCheckFE');
    		//$firephp->log($this->arrBannerCategories, 'banner_categories');
    		//$firephp->log($fe_groups, 'User groups');
    		// if protected categories then get banner groups
    		$objBannerGroups = $this->Database->prepare("SELECT id,banner_protected,banner_groups"
    		                                         . " FROM tl_banner_category"
    		                                         . " WHERE id IN (" . implode(',', $this->arrBannerCategories) . ")"
    		                                           )
    		                                  ->execute(); // 1 = protected
    	    while ($objBannerGroups->next()) 
            {
                if ($objBannerGroups->banner_protected == 1 && strlen($objBannerGroups->banner_groups) >1) 
                {
                    $banner_groups = deserialize($objBannerGroups->banner_groups,true); //array
                    //$firephp->log($banner_groups, 'banner_groups>0');
                    if ( count( array_intersect($banner_groups,$fe_groups) ) >0 )
                    {
                        array_push($idg,$objBannerGroups->id);
                        //$firephp->log($idg, 'idg add');
                    }
                } else {
                	array_push($idg,$objBannerGroups->id);
                	//$firephp->log($idg, 'idg direkt');
                }
            }
    		
    		// FE User logged in && banner_category ist protected ?
            //$firephp->log($idg, 'banner_categories gefiltert');
            $this->arrBannerCategories = $idg;
		
            if (count($this->arrBannerCategories) < 1 ) {
                //$firephp->log('Eingeloggter FE Nutzer darf nichts sehen');
    		    return false; //Mitglied darf nichts sehen
    		} else {
    		    //$firephp->log('Eingeloggter FE Nutzer darf Teile sehen');
    		    return true;
    		}
		} else {
			return true;
		}
	}
	
	/**
	 * HTTP_USER_AGENT Special Check
	 */
	protected function CheckUserAgent()
	{
   	    if (isset($this->Environment->httpUserAgent)) { 
	        $UserAgent = trim($this->Environment->httpUserAgent); 
	    } else { 
	        return false; // Ohne Absender keine Suche
	    }
	    $arrUserAgents = explode(",", $this->useragent_filter);
	    if (strlen(trim($arrUserAgents[0])) == 0) {
	    	return false; // keine Angaben im Modul
	    }
	    array_walk($arrUserAgents, array('self','banner_array_trim_value'));  // trim der array values
        // grobe Suche
        $CheckUserAgent=str_replace($arrUserAgents, '#', $UserAgent);
        if ($UserAgent != $CheckUserAgent) { // es wurde ersetzt also was gefunden
        	//log_message('CheckUserAgent Filterung; Treffer!','Banner.log');
            return true;
        }
        return false; 
	} //CheckUserAgent
	
	public static function banner_array_trim_value(&$data) {
        $data = trim($data);
        return ;
    }

}

?>