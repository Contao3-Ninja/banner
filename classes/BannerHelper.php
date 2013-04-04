<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2013 Leo Feyer
 *
 * Modul Banner - FE Helper Class BannerHelper
 *
 * @copyright  Glen Langer 2007..2013 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    Banner
 * @license    LGPL
 * @filesource
 * @see        https://github.com/BugBuster1701/banner
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Banner;

/**
 * Class BannerHelper
 *
 * @copyright  Glen Langer 2007..2013 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    Banner
 * @license    LGPL
 */
class BannerHelper extends \Module
{
	/**
	 * Banner intern
	 * @var string
	 */
	const BANNER_TYPE_INTERN = 'banner_image';
	
	/**
	 * Banner extern
	 * @var string
	 */
	const BANNER_TYPE_EXTERN = 'banner_image_extern';
	
	/**
	 * Banner text
	 * @var string
	 */
	const BANNER_TYPE_TEXT   = 'banner_text';
	
	
	/**
	 * Banner Seen
	 */
	public static $arrBannerSeen = array();
	
	/**
	 * Banner Random Blocker
	 */
	protected $statusRandomBlocker = false;
	
	/**
	 * Banner First View Status
	 */
	protected $statusBannerFirstView = false;
	
	/**
	 * Banner Frontend Group View
	 * @var bool	true  = View OK
	 * 				false = FE User logged in and nothing is allowed to view (wrong group)
	 */
	protected $statusBannerFrontendGroupView = true;
	
	/**
	 * Banner basic status
	 * @var bool    true = $arrAllBannersBasic filled | false = error
	 */
	protected $statusAllBannersBasic = true;
	
	/**
	 * Category values 
	 * @var mixed	array|false, false if category not exists
	 */
	protected $arrCategoryValues = array();
	
	/**
	 * All banner basic data (id,weighting) from a category
	 * @var array
	 */
	protected $arrAllBannersBasic = array();
	
	
	/**
	 * Page Output Format
	 * @var string
	 */
	protected $strFormat = 'xhtml';
	
	
	/**
	 * parent call of generate()
	 */
	public function generate()
	{
		return parent::generate();
	}
	
	protected function compile()
	{
		
	}
	/*
	 * constructor of Module generates
	 * $this->arrData = $objModule->row(); use getter / setter for this
	 * $this->space = deserialize($objModule->space);
	 * $this->cssID = deserialize($objModule->cssID, true); 
	 */
	 
	/**
	 * INIT
	 * 
	 * @return	false, if anything is wrong
	 */
	protected function BannerHelperInit()
	{
		/* over getter use:
		 * banner_hideempty
		 * banner_firstview		- old: $this->selectBannerFirstView
		 * banner_categories
		 * banner_template
		 * banner_redirect
		 * banner_useragent		- old: $this->useragent_filter
		 */
		
		//set $arrCategoryValues over tl_banner_category
		if ($this->getSetCategoryValues()===false) { return false; }
		
		//check for protected user groups
		//set $statusBannerFrontendGroupView
		$this->checkSetUserFrontendLogin();
		
		//get basic banner infos (id,weighting) in $this->arrAllBannersBasic
		if ($this->getSetAllBannerForCategory() === false) 
		{
			$this->statusAllBannersBasic = false;
		}
		//TODO wenn das false ist, dann default banner falls gewollt
		//sonst weiter im Programm
		
		
		global $objPage;
		if ($objPage->outputFormat == 'html5')
		{
		    $this->strFormat = 'html5';
		}
	}
	
	/**
	 * Set Category Values in $this->arrCategoryValues over tl_banner_category
	 * 
	 * @return boolean    true = OK | false = we have a problem
	 */
	protected function getSetCategoryValues()
	{
		//$this->banner_categories
		if ( !isset($this->banner_categories) || !is_numeric($this->banner_categories) ) 
		{
			$this->log($GLOBALS['TL_LANG']['tl_banner']['banner_cat_not_found'], 'ModulBanner Compile', 'ERROR');
			$this->arrCategoryValues = false;
			return false;
		}
		$objBannerCategory = $this->Database->prepare("SELECT * FROM  tl_banner_category WHERE id=?")
											->execute($this->banner_categories); 
		if ($objBannerCategory->numRows == 0) 
		{
			$this->log($GLOBALS['TL_LANG']['tl_banner']['banner_cat_not_found'], 'ModulBanner Compile', 'ERROR');
			$this->arrCategoryValues = false;
			return false;
		}
		$arrGroup = deserialize($objBannerCategory->banner_groups);
		$objFile = \FilesModel::findByPk($objBannerCategory->banner_default_image);
		$this->arrCategoryValues = array(
				'id'                    => $objBannerCategory->id,
				'banner_default'		=> $objBannerCategory->banner_default,
				'banner_default_name'	=> $objBannerCategory->banner_default_name,
				'banner_default_image'	=> $objFile->path,
				'banner_default_url'	=> $objBannerCategory->banner_default_url,
				'banner_default_target'	=> $objBannerCategory->banner_default_target,
				'banner_numbers'		=> $objBannerCategory->banner_numbers, //0:single,1:multi,see banner_limit
				'banner_random'			=> $objBannerCategory->banner_random,
				'banner_limit'			=> $objBannerCategory->banner_limit, // 0:all, others = max 
				'banner_protected'		=> $objBannerCategory->banner_protected,
				'banner_group'			=> $arrGroup[0]
				);
		return true;
	}
	
	/**
	 * Check if FE User loggen in and banner category is protected
	 * 
	 * @return boolean    true = View allowed | false = View not allowed
	 */
	protected function checkSetUserFrontendLogin()
	{
		if (FE_USER_LOGGED_IN)
		{
		    $this->import('FrontendUser', 'User');
		    
		    if ( $this->arrCategoryValues['banner_protected'] == 1 
		      && $this->arrCategoryValues['banner_group']      > 0 ) 
		    {
		    	if ( $this->User->isMemberOf($this->arrCategoryValues['banner_group']) === false ) 
		    	{
		    		$this->statusBannerFrontendGroupView = false;
		    		return false;
		    	}
		    }
		}
		return true;
	}
	
	/**
	 * Get all Banner basics (id,weighting) for category, in $arrAllBannersBasic
	 * 
	 * @return boolean    true = $arrAllBannersBasic is filled | false = empty $arrAllBannersBasic
	 */
	protected function getSetAllBannerForCategory()
	{
		//wenn mit der definierte Kategorie ID keine Daten gefunden wurden
		//macht Suche nach Banner kein Sinn
		if ($this->arrCategoryValues === false) 
		{
			return false;
		}
		//Domain Name ermitteln
		$http_host = \Environment::get('host');
		//aktueller Zeitstempel
		$intTime = time();
		
		//alle gültigen aktiven Banner,
		//ohne Beachtung der Gewichtung,
		//mit Beachtung der Domain
		//sortiert nach "sorting"
		//nur Basic Felder `id`, `banner_weighting` 
		$objBanners = $this->Database->prepare("SELECT TLB.`id`, TLB.`banner_weighting`"
				. " FROM tl_banner AS TLB "
		        . " LEFT JOIN tl_banner_category ON (tl_banner_category.id=TLB.pid)"
		        . " LEFT OUTER JOIN tl_banner_stat AS TLS ON TLB.id=TLS.id"
		        . " WHERE pid=?"
		        . " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_views_until>TLS.banner_views)   OR (TLB.banner_until=1 AND TLB.banner_views_until=?)  OR (TLB.banner_until=1 AND TLS.banner_views is NULL))"
		        . " AND ((TLB.banner_until=?) OR (TLB.banner_until=1 AND TLB.banner_clicks_until>TLS.banner_clicks) OR (TLB.banner_until=1 AND TLB.banner_clicks_until=?) OR (TLB.banner_until=1 AND TLS.banner_clicks is NULL))"
		        . " AND TLB.banner_published =?"
		        . " AND (TLB.banner_start=? OR TLB.banner_start<=?) AND (TLB.banner_stop=? OR TLB.banner_stop>=?)"
		        . " AND (TLB.banner_domain=? OR RIGHT(?, CHAR_LENGTH(TLB.banner_domain)) = TLB.banner_domain)"
				. " GROUP BY TLB.`sorting`"
				)
				->execute($this->banner_categories
							, '', ''
							, '', ''
							, 1
							, '', $intTime, '', $intTime
							, '', $http_host);
		while ($objBanners->next())
		{
			$this->arrAllBannersBasic[$objBanners->id] = $objBanners->banner_weighting;
		}
		return (bool)$this->arrAllBannersBasic; //false bei leerem array, sonst true
	}
	
	/**
	 * Get default banner or empty banner in $this->Template->banners
	 * 
	 * @return boolean    true
	 */
	protected function getDefaultBanner()
	{
		$arrImageSize = array();
		
		//BannerDefault gewünscht und vorhanden?
		if ( $this->arrCategoryValues['banner_default'] == '1' && strlen($this->arrCategoryValues['banner_default_image']) > 2 ) 
		{
			//Template setzen
			if ( ($this->banner_template != $this->strTemplate) && ($this->banner_template != '') ) 
			{
			    $this->strTemplate = $this->banner_template;
			    $this->Template = new \FrontendTemplate($this->strTemplate);
			}
			//Link je nach Ausgabeformat
			if ($this->strFormat == 'xhtml') 
			{
			    $banner_default_target = ($this->arrCategoryValues['banner_default_target'] == '1') ? LINK_BLUR : LINK_NEW_WINDOW;
			} 
			else 
			{
			    $banner_default_target = ($this->arrCategoryValues['banner_default_target'] == '1') ? '' : ' target="_blank"';
			}
			//BannerImage Class
			$this->import('\Banner\BannerImage', 'BannerImage');
			
			//Banner Art bestimmen
			$arrImageSize = $this->BannerImage->getBannerImageSize($this->arrCategoryValues['banner_default_image'], self::BANNER_TYPE_INTERN);
			
			switch ($arrImageSize[2]) 
			{
			    case 1:
			    case 2:
			    case 3:
			        $arrBanners[] = array
							        (
							        'banner_key'     => 'defbid=',
							        'banner_id'      => $this->arrCategoryValues['id'],
							        'banner_name'    => specialchars(ampersand($this->arrCategoryValues['banner_default_name'])),
							        'banner_url'     => $this->arrCategoryValues['banner_default_url'],
							        'banner_target'  => $banner_default_target,
							        'banner_comment' => specialchars(ampersand($this->arrCategoryValues['banner_default_name'])),
							        'src'            => $this->urlEncode(      $this->arrCategoryValues['banner_default_image']),
							        'alt'            => specialchars(ampersand($this->arrCategoryValues['banner_default_name'])),
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
					                'banner_id'      => $this->arrCategoryValues['id'],
					                'banner_name'    => specialchars(ampersand($this->arrCategoryValues['banner_default_name'])),
					                'banner_url'     => $this->arrCategoryValues['banner_default_url'],
					                'banner_target'  => $banner_default_target,
					                'banner_comment' => specialchars(ampersand($this->arrCategoryValues['banner_default_name'])),
					                'swf_src'        => $this->arrCategoryValues['banner_default_image'],
					                'swf_width'      => $arrImageSize[0],
					                'swf_height'     => $arrImageSize[1],
					                'swf_id'         => round((float)$usec*100000,0).'_'.$this->arrCategoryValues['id'],
					                'alt'            => specialchars(ampersand($this->arrCategoryValues['banner_default_name'])),
					                'banner_pic'     => false,
					                'banner_flash'   => true,
					                'banner_text'    => false,
					                'banner_empty'   => false	// issues 733
							        );
			        break;
			}
			$arrResults[] = $arrBanners[0];
			$this->Template->banners = $arrResults;
			return true;
		}
		//Kein BannerDefault
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
		//Ausblenden wenn leer?
		if ($this->banner_hideempty == 1)
		{
		    // auf Leer umschalten
		    $this->strTemplate='mod_banner_empty';
		    $this->Template = new \FrontendTemplate($this->strTemplate);
		}
		$this->Template->banners = $arrResults;
		
		return true;
	}
	
	/**
	 * Get weighting for single banner
	 * parameter over $this->arrAllBannersBasic [id,weighting]
	 * 
	 * @return integer    0|1|2|3    0 on error
	 */
	protected function getSingleWeighting()
	{
	    $arrPrio = array();
	    $arrPrioW = array();
	    
	    //welche Wichtungen gibt es?
	    if (array_key_exists(1, $this->arrAllBannersBasic)) { $arrPrioW[] = 1; };
	    if (array_key_exists(2, $this->arrAllBannersBasic)) { $arrPrioW[] = 2; };
	    if (array_key_exists(3, $this->arrAllBannersBasic)) { $arrPrioW[] = 3; };
	    
	    $arrPrio[0] = array('start'=>0,  'stop'=>0);
	    $arrPrio[1] = array('start'=>1,  'stop'=>90);
	    $arrPrio[2] = array('start'=>91, 'stop'=>150);
	    $arrPrio[3] = array('start'=>151,'stop'=>180);
	    if ( !array_key_exists(2,$arrPrioW) )
	    {
	        // no prio 2 banner
	        $arrPrio[2] = array('start'=>0,  'stop'=>0);
	        $arrPrio[3] = array('start'=>91, 'stop'=>120);
	    }
	    $intPrio1 = (count($arrPrioW)) ? min($arrPrioW) : 0 ;
	    $intPrio2 = (count($arrPrioW)) ? max($arrPrioW) : 0 ;

	    //wenn Wichtung vorhanden, dann per Zufall eine auswählen
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
	    return $intWeighting;
	}
	
	/**
	 * Random Blocker, Set Banner-ID
	 * 
	 * @param integer    $BannerID
	 */
	protected function setRandomBlockerId($BannerID=0)
	{
	    if ($BannerID==0) { return; }// kein Banner, nichts zu tun

	    $ClientIP = bin2hex(sha1($this->banner_categories . \Environment::get('remoteAddr'),true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    //log_message('setRandomBlockerId BannerID:'.$BannerID,'Banner.log');
	    
	    // Eigene IP oder aeltere Eintraege loeschen
	    $this->Database->prepare("DELETE FROM tl_banner_random_blocker WHERE ip=? OR tstamp <?")
	                   ->execute($ClientIP, time() -(24*60*60));
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
	 * @return integer    Banner-ID
	 */
	protected function getRandomBlockerId()
	{
	    $ClientIP = bin2hex(sha1($this->banner_categories . \Environment::get('remoteAddr'),true)); // sha1 20 Zeichen, bin2hex 40 zeichen
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
	
	/**
	 * Get FirstViewBanner status and set cat id as blocker
	 * 
	 * @return boolean    true = if requested and not blocked | false = if requested but blocked
	 */
	protected function getSetFirstView()
	{
	    //FirstViewBanner gewünscht?
	    if ($this->banner_firstview !=1) { return false; }
	    
	    $ClientIP = bin2hex(sha1($this->banner_categories . \Environment::get('remoteAddr'),true)); // sha1 20 Zeichen, bin2hex 40 zeichen	    
	    $BannerFirstViewBlockTime = time() - 60*10; // 10 Minuten, Einträge >= 10 Minuten werden gelöscht

	    $this->import('\Banner\BannerReferrer','BannerReferrer');
	    $this->BannerReferrer->checkReferrer();
	    $ReferrerDNS = $this->BannerReferrer->getReferrerDNS();
	    // o own , w wrong
	    if ($ReferrerDNS === 'o')
	    {
	        // eigener Referrer, Begrenzung auf First View nicht nötig.
	        $this->statusBannerFirstView = false;
	        return false;
	    }
	    
	    $this->Database->prepare("DELETE FROM tl_banner_blocker WHERE bid =? AND tstamp<? AND type=?")
	                   ->execute($this->banner_categories, $BannerFirstViewBlockTime, 'f');
	    $objBanners = $this->Database->prepare("SELECT id FROM tl_banner_blocker WHERE bid =? AND tstamp>? AND ip=? AND type=?")
                        	         ->limit(1)
                        	         ->executeUncached($this->banner_categories, $BannerFirstViewBlockTime, $ClientIP, 'f' );
	    if (0 == $objBanners->numRows)
	    {
	        // noch kein Eintrag bzw. ausserhalb Blockzeit
	        $arrSet = array
	        (
	                'bid'    => $this->banner_categories,
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
	
	protected function getSingleBannerFirst()
	{
	    //Domain Name ermitteln
	    $http_host = \Environment::get('host');
	    //aktueller Zeitstempel
	    $intTime = time();
	    $arrBanners = array();
	    $arrResults = array();
	    
	    //first aktiv banner in category
	    $objBanners = $this->Database->prepare("SELECT TLB.* 
                                                FROM tl_banner AS TLB 
                                                LEFT JOIN tl_banner_category ON (tl_banner_category.id=TLB.pid)
                                                WHERE pid=?
                                                AND TLB.banner_published =1
                                                AND (TLB.banner_start=? OR TLB.banner_start<=?) 
	                                            AND (TLB.banner_stop=?  OR TLB.banner_stop>=?)
                                                AND (TLB.banner_domain=? OR RIGHT(?, CHAR_LENGTH(TLB.banner_domain)) = TLB.banner_domain)
                                                ORDER BY sorting"
	                                          )
	                       ->limit(1)
	                       ->execute($this->banner_categories,'', $intTime, '', $intTime, '', $http_host);
        $intRows = $objBanners->numRows;
        //Banner vorhanden?
        if($intRows > 0)
        {
            $objBanners->next();
            //Pfad+Dateiname holen ueber ID
            $objFile = \FilesModel::findByPk($objBanners->banner_image);
            
            //BannerImage Class
            $this->import('\Banner\BannerImage', 'BannerImage');
            //Banner Art und Größe bestimmen
            $arrImageSize = $this->BannerImage->getBannerImageSize($objFile->path, self::BANNER_TYPE_INTERN);

            if ($arrImageSize !== false)
            {
                if ($this->strFormat == 'xhtml')
                {
                    $banner_target = ($objBanners->banner_target == '1') ? LINK_BLUR : LINK_NEW_WINDOW;
                } 
                else 
                {
                    $banner_target = ($objBanners->banner_target == '1') ? '' : ' target="_blank"';
                }
                
                if ( strlen($objBanners->banner_comment) > 1 )
                {
                    $banner_comment_pos = strpos($objBanners->banner_comment,"\n",1);
                    if ($banner_comment_pos !== false)
                    {
                        $objBanners->banner_comment = substr($objBanners->banner_comment,0,$banner_comment_pos);
                    }
                }
                //$arrImageSize[0]  eigene Breite 
                //$arrImageSize[1]  eigene Höhe
                //$arrImageSize[3]  Breite und Höhe in der Form height="yyy" width="xxx"
                //$arrImageSize[2]
                // 1 = GIF, 2 = JPG, 3 = PNG
                // 4 = SWF, 13 = SWC (zip-like swf file)
                // 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order)
                // 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF
                switch ($arrImageSize[2])
                {
                    case 1:
                    case 2:
                    case 3:
                        $arrBanners[] = array
                        (
                                'banner_key'     => 'bid=',
                                'banner_id'      => $objBanners->id,
                                'banner_name'    => specialchars(ampersand($objBanners->banner_name)),
                                'banner_url'     => $objBanners->banner_url,
                                'banner_target'  => $banner_target,
                                'banner_comment' => specialchars(ampersand($objBanners->banner_comment)),
                                'src'            => specialchars(ampersand($this->urlEncode($objFile->path))),
                                'alt'            => specialchars(ampersand($objBanners->banner_name)),
                                'size'           => $arrImageSize[3],
                                'banner_pic'     => true,
                                'banner_flash'   => false,
                                'banner_text'    => false,
                                'banner_empty'   => false
                        );
                        break;
                    case 4:  // Flash swf
                    case 13: // Flash swc
                        list($usec, ) = explode(" ", microtime());
                        
                        //Check for Fallback Image, only for local flash files (Path,Breite,Höhe)
                        $src_fallback = $this->BannerImage->getCheckBannerImageFallback($objFile->path,$arrImageSize[0],$arrImageSize[1]);
                        if ($src_fallback !== false)
                        {
                            //Fallback gefunden
                            if ($this->strFormat == 'xhtml') 
                            {
                                $fallback_content = '<img src="' . $src_fallback . '" alt="'.specialchars(ampersand($objBanners->banner_comment)).'" height="'.$arrImageSize[1].'" width="'.$arrImageSize[0].'" />';
                            } 
                            else 
                            {
                                $fallback_content = '<img src="' . $src_fallback . '" alt="'.specialchars(ampersand($objBanners->banner_comment)).'" height="'.$arrImageSize[1].'" width="'.$arrImageSize[0].'">';
                            }
                        }
                        else
                        {
                            //kein Fallback
                            if ($this->strFormat == 'xhtml')
                            {
                                $fallback_content = $objBanners->banner_image ."<br />". specialchars(ampersand($objBanners->banner_comment)) ."<br />". specialchars(ampersand($objBanners->banner_name));
                            } 
                            else 
                            {
                                $fallback_content = $objBanners->banner_image ."<br>". specialchars(ampersand($objBanners->banner_comment)) ."<br>". specialchars(ampersand($objBanners->banner_name));
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
                            'swf_width'      => $arrImageSize[0],
                            'swf_height'     => $arrImageSize[1],
                            'swf_id'         => round((float)$usec*100000,0).'_'.$objBanners->id,
                            'alt'            => specialchars(ampersand($objBanners->banner_name)),
                            'fallback_content'=> $fallback_content,
                            'banner_pic'     => false,
                            'banner_flash'   => true,
                            'banner_text'    => false,
                            'banner_empty'   => false
                        );
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
                }//switch
                
                //anderes Template?
                if (($this->banner_template != $this->strTemplate) && ($this->banner_template != ''))
                {
                    $this->strTemplate = $this->banner_template;
                    $this->Template = new \FrontendTemplate($this->strTemplate);
                }
                //TODO $this->arrBannerData = $arrBanners; wird von BannerStatViewUpdate genutzt
                //TODO $this->BannerStatViewUpdate();
                $this->Template->banners = $arrBanners;
                return true;
                
            }//$arrImageSize !== false
        }//Banner vorhanden
        //falls $arrImageSize = false  
        $this->Template->banners = $arrBanners; // leeres array
	}
	
	protected function getSingleBanner()
	{
	    
	}
} // class
































