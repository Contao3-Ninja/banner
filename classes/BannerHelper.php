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
	 * Banner Data, for BannerStatViewUpdate
	 */
	protected $arrBannerData = array();
	
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
		 * banner_categories    - is now an ID, but the name is backward compatible 
		 * banner_template
		 * banner_redirect
		 * banner_useragent		- old: $this->useragent_filter
		 * banner_random
		 * banner_limit         - 0 all, other:max
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
		//$this->banner_categories is now an ID, but the name is backward compatible 
		if ( !isset($this->banner_categories) || !is_numeric($this->banner_categories) ) 
		{
			$this->log($GLOBALS['TL_LANG']['tl_banner']['banner_cat_not_found'], 'ModulBanner Compile', 'ERROR');
			$this->arrCategoryValues = false;
			return false;
		}
		$objBannerCategory = \Database::getInstance()->prepare("SELECT 
                                                                    * 
                                                                FROM  
                                                                    tl_banner_category 
                                                                WHERE 
                                                                    id=?")
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
		$objBanners = \Database::getInstance()
		                ->prepare("SELECT 
                                        TLB.`id`, TLB.`banner_weighting`
                                   FROM 
                                        tl_banner AS TLB 
                                   LEFT JOIN 
                                        tl_banner_category ON tl_banner_category.id=TLB.pid
                                   LEFT OUTER JOIN 
                                        tl_banner_stat AS TLS ON TLB.id=TLS.id
                                   WHERE 
                                        pid=?
                                   AND (
                                           (TLB.banner_until=?) 
		                                OR (TLB.banner_until=1 AND TLB.banner_views_until>TLS.banner_views)   
                                        OR (TLB.banner_until=1 AND TLB.banner_views_until=?)  
                                        OR (TLB.banner_until=1 AND TLS.banner_views is NULL)
                                       )
                                   AND (
                                           (TLB.banner_until=?) 
                                        OR (TLB.banner_until=1 AND TLB.banner_clicks_until>TLS.banner_clicks) 
                                        OR (TLB.banner_until=1 AND TLB.banner_clicks_until=?) 
                                        OR (TLB.banner_until=1 AND TLS.banner_clicks is NULL)
                                       )
                                   AND 
                                        TLB.banner_published =?
                                   AND 
                                       (TLB.banner_start=? OR TLB.banner_start<=?) 
                                   AND 
                                       (TLB.banner_stop=? OR TLB.banner_stop>=?)
                                   AND 
                                       (TLB.banner_domain=? OR RIGHT(?, CHAR_LENGTH(TLB.banner_domain)) = TLB.banner_domain)
                                   ORDER BY TLB.`sorting`"
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
			if ( ($this->arrCategoryValues['banner_template'] != $this->strTemplate) 
			  && ($this->arrCategoryValues['banner_template'] != '') ) 
			{
			    $this->strTemplate = $this->arrCategoryValues['banner_template'];
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
	    \Database::getInstance()->prepare("DELETE FROM 
                                                tl_banner_random_blocker 
                                           WHERE 
                                                ip=? 
                                           OR 
                                                tstamp <?")
                                ->execute($ClientIP, time() -(24*60*60));
	    $arrSet = array
	    (
	            'bid'    => $BannerID,
	            'tstamp' => time(),
	            'ip'     => $ClientIP,
	    );
	    \Database::getInstance()->prepare("INSERT INTO tl_banner_random_blocker %s")
                                ->set($arrSet)
                                ->execute();
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
	    $objBanners = \Database::getInstance()->prepare("SELECT 
                                                            * 
                                                         FROM 
                                                            tl_banner_random_blocker 
                                                         WHERE 
                                                            ip=?")
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
	    //return true; // for Test TODO kill
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
	    //TODO: #37
	    \Database::getInstance()->prepare("DELETE FROM 
                                                tl_banner_stat_blocker 
                                           WHERE 
                                                bid =? 
                                           AND 
                                                tstamp<? 
                                           AND 
                                                type=?")
	                            ->execute($this->banner_categories, $BannerFirstViewBlockTime, 'f');
	    
	    $objBanners = \Database::getInstance()
                                ->prepare("SELECT 
                                                id 
                                           FROM 
                                                tl_banner_stat_blocker 
                                           WHERE 
                                                bid =? 
                                           AND 
                                                tstamp>? 
                                           AND 
                                                ip=? 
                                           AND 
                                                type=?")
                                ->limit(1)
                        	    ->executeUncached($this->banner_categories, $BannerFirstViewBlockTime, $ClientIP, 'f');
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
	        \Database::getInstance()->prepare("INSERT INTO tl_banner_stat_blocker %s")
                                    ->set($arrSet)
                                    ->executeUncached();
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
	    // TODO kill $http_host = \Environment::get('host');
	    //aktueller Zeitstempel
	    // TODO kill $intTime = time();
	    $arrBanners = array();
	    $arrResults = array();
	    $FileSrc = '';
	    
	    //first aktiv banner in category
	    //$this->arrAllBannersBasic den ersten Datensatz über die ID nutzen
	    //seltsamerweise kommt reference Fehler bei Kombination in einer Anweisung, daher getrennt
	    $banner_keys = array_keys($this->arrAllBannersBasic); 
	    $banner_id   = array_shift($banner_keys);
	    $objBanners  = \Database::getInstance()
                    	    ->prepare("SELECT
                            	            TLB.*
                                       FROM
                            	            tl_banner AS TLB
                                       WHERE 
                                            TLB.`id`=?"
                    	            )
            	            ->limit(1)
            	            ->execute( $banner_id );
        $intRows = $objBanners->numRows;
        //Banner vorhanden?
        if($intRows > 0)
        {
            $objBanners->next();
            //echo "getSingleBannerFirst Banneranzahl: ".$intRows."\n<br>"; // TODO kill
            //echo "getSingleBannerFirst BannerType: ".$objBanners->banner_type."\n<br>"; //TODO kill
            switch ($objBanners->banner_type)
            {
                case self::BANNER_TYPE_INTERN :
                    //Pfad+Dateiname holen ueber ID
                    $objFile = \FilesModel::findByPk($objBanners->banner_image);
                    //BannerImage Class
                    $this->import('\Banner\BannerImage', 'BannerImage');
                    //Banner Art und Größe bestimmen
                    $arrImageSize = $this->BannerImage->getBannerImageSize($objFile->path, self::BANNER_TYPE_INTERN);
                    //Banner Neue Größe 0:$Width 1:$Height
                    $arrNewSizeValues = deserialize($objBanners->banner_imgSize);
                    //Banner Neue Größe ermitteln, return array $Width,$Height,$oriSize
                    $arrImageSizenNew = $this->BannerImage->getBannerImageSizeNew($arrImageSize[0],$arrImageSize[1],$arrNewSizeValues[0],$arrNewSizeValues[1]);
                    
                    //wenn oriSize = true, oder bei GIF/SWF/SWC = original Pfad nehmen
                    if ($arrImageSizenNew[2] === true 
                         || $arrImageSize[2] == 1  // GIF
                         || $arrImageSize[2] == 4  // SWF
                         || $arrImageSize[2] == 13 // SWC
                         ) 
                    {
                        $FileSrc = $objFile->path;
                        $arrImageSize[0] = $arrImageSizenNew[0];
                        $arrImageSize[1] = $arrImageSizenNew[1];
                        $arrImageSize[3] = ' height="'.$arrImageSizenNew[1].'" width="'.$arrImageSizenNew[0].'"';
                    }
                    else
                    {
                        $FileSrc = \Image::get($this->urlEncode($objFile->path), $arrImageSizenNew[0], $arrImageSizenNew[1],'proportional');
                        $arrImageSize[0] = $arrImageSizenNew[0];
                        $arrImageSize[1] = $arrImageSizenNew[1];
                        $arrImageSize[3] = ' height="'.$arrImageSizenNew[1].'" width="'.$arrImageSizenNew[0].'"';
                    }
                    break;
                case self::BANNER_TYPE_EXTERN :
                    //BannerImage Class
                    $this->import('\Banner\BannerImage', 'BannerImage');
                    //Banner Art und Größe bestimmen
                    $arrImageSize = $this->BannerImage->getBannerImageSize($objBanners->banner_image_extern, self::BANNER_TYPE_EXTERN);
                    //Banner Neue Größe 0:$Width 1:$Height
                    $arrNewSizeValues = deserialize($objBanners->banner_imgSize);
                    //Banner Neue Größe ermitteln, return array $Width,$Height,$oriSize
                    $arrImageSizenNew = $this->BannerImage->getBannerImageSizeNew($arrImageSize[0],$arrImageSize[1],$arrNewSizeValues[0],$arrNewSizeValues[1]);
                    //Umwandlung bei Parametern
                    $FileSrc = html_entity_decode($objBanners->banner_image_extern, ENT_NOQUOTES, 'UTF-8');
                    //$src = $objBanners->banner_image_extern;
                    $arrImageSize[0] = $arrImageSizenNew[0];
                    $arrImageSize[1] = $arrImageSizenNew[1];
                    $arrImageSize[3] = ' height="'.$arrImageSizenNew[1].'" width="'.$arrImageSizenNew[0].'"';
                    break;
                case self::BANNER_TYPE_TEXT :
                    $arrImageSize = false;
                    break;
            }
            //TODO kill
            //echo "getSingleBannerFirst arrImageSize: <pre>".print_r($arrImageSize,true)."</pre>\n<br>"; // TODO kill
            //echo "getSingleBannerFirst FileSrc: $FileSrc";
            if ($arrImageSize !== false) //Bilder extern/intern
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
                
                // Banner Seite als Ziel?
                if ($objBanners->banner_jumpTo > 0)
                {
                    $domain = \Environment::get('base');
                    $objParent = $this->getPageDetails($objBanners->banner_jumpTo);
                    if ($objParent->domain != '')
                    {
                        $domain = (\Environment::get('ssl') ? 'https://' : 'http://') . $objParent->domain . TL_PATH . '/';
                    }
                    $objBanners->banner_url = $domain . $this->generateFrontendUrl($objParent->row(), '', $objParent->language);
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
                                'src'            => specialchars(ampersand($FileSrc)),//specialchars(ampersand($this->urlEncode($FileSrc))),
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
                        $src_fallback = $this->BannerImage->getCheckBannerImageFallback($FileSrc,$arrImageSize[0],$arrImageSize[1]);
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
                                $fallback_content = $FileSrc ."<br />". specialchars(ampersand($objBanners->banner_comment)) ."<br />". specialchars(ampersand($objBanners->banner_name));
                            } 
                            else 
                            {
                                $fallback_content = $FileSrc ."<br>". specialchars(ampersand($objBanners->banner_comment)) ."<br>". specialchars(ampersand($objBanners->banner_name));
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
                            'swf_src'        => specialchars(ampersand($FileSrc)),
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
                if (($this->arrCategoryValues['banner_template'] != $this->strTemplate) 
                 && ($this->arrCategoryValues['banner_template'] != ''))
                {
                    $this->strTemplate = $this->arrCategoryValues['banner_template'];
                    $this->Template = new \FrontendTemplate($this->strTemplate);
                }
                $this->arrBannerData = $arrBanners; //wird von BannerStatViewUpdate genutzt
                $this->setStatViewUpdate();
                $this->Template->banners = $arrBanners;
                return true;
                
            }//$arrImageSize !== false
            
            // Text Banner
            if ($objBanners->banner_type == 'banner_text') 
            {
                if ($this->strFormat == 'xhtml')
                {
                    $banner_target = ($objBanners->banner_target == '1') ? LINK_BLUR : LINK_NEW_WINDOW;
                } 
                else 
                {
                    $banner_target = ($objBanners->banner_target == '1') ? '' : ' target="_blank"';
                }

                // Banner Seite als Ziel?
                if ($objBanners->banner_jumpTo > 0) 
                {
                    $domain = \Environment::get('base');
                    $objParent = $this->getPageDetails($objBanners->banner_jumpTo);
                    if ($objParent->domain != '')
                    {
                        $domain = (\Environment::get('ssl') ? 'https://' : 'http://') . $objParent->domain . TL_PATH . '/';
                    }
                    $objBanners->banner_url = $domain . $this->generateFrontendUrl($objParent->row(), '', $objParent->language);
                }
                
                // Kurz URL (nur Domain)
                $treffer = parse_url($objBanners->banner_url);
                $banner_url_kurz = $treffer['host'];
                if (isset($treffer['port'])) 
                {
                    $banner_url_kurz .= ':'.$treffer['port'];
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
                if (($this->arrCategoryValues['banner_template'] != $this->strTemplate) 
                 && ($this->arrCategoryValues['banner_template'] != '')) 
                {
                    $this->strTemplate = $this->arrCategoryValues['banner_template'];
                    $this->Template = new \FrontendTemplate($this->strTemplate);
                }
                $arrResults[] = $arrBanners[0];
                $this->Template->banners = $arrResults;
                 
                $this->arrBannerData = $arrResults;
                $this->setStatViewUpdate();
                return true;
            }
        }//Banner vorhanden
        //falls $arrImageSize = false  und kein Text Banner
        $this->Template->banners = $arrBanners; // leeres array
	}
	
	protected function getSingleBanner()
	{
	    //Gewichtung nach vorhandenen Wichtungen
	    $SingleBannerWeighting = $this->getSingleWeighting();
	    //alle Basic Daten durchgehen und die löschen die nicht der Wichtung entsprechen
	    while ( list($key, $val) = each($this->arrAllBannersBasic) ) 
	    {
	        if ($val != $SingleBannerWeighting) 
	        {
	            unset($this->arrAllBannersBasic[$key]);
	        }
	    }
	    reset($this->arrAllBannersBasic); //sicher ist sicher
	    
	    //RandomBlocker entfernen falls möglich und nötig
	    if ( count($this->arrAllBannersBasic) >1 ) // einer muss ja übrig bleiben
	    {
	        $intRandomBlockerID = $this->getRandomBlockerId();
	        //TODO kill echo "geblockte Banner ID: $intRandomBlockerID \n<br>";
	        if (isset($this->arrAllBannersBasic[$intRandomBlockerID])) 
	        {
	            unset($this->arrAllBannersBasic[$intRandomBlockerID]);
	        } 
	    }
	    
	    //Zufallszahl
	    //array_shuffle und array_rand zu "ungenau"
	    $intShowBanner =  mt_rand(1,count($this->arrAllBannersBasic)); 
	    $banner_keys = array_keys($this->arrAllBannersBasic);
	    for ($xx=1;$xx<=$intShowBanner;$xx++)
	    {
	        $banner_id   = array_shift($banner_keys);
	    }
	    
	    //Random Blocker setzen
	    $this->setRandomBlockerId($banner_id);
	    
	    $objBanners  = \Database::getInstance()
                            ->prepare("SELECT
                            	            TLB.*
                                       FROM
                            	            tl_banner AS TLB
                                       WHERE
                                            TLB.`id`=?"
                                     )
                            ->limit(1)
                            ->execute( $banner_id );
	    $intRows = $objBanners->numRows;
	    //Banner vorhanden?
	    if($intRows > 0)
	    {
	        $objBanners->next();
	        //echo "getSingleBannerFirst Banneranzahl: ".$intRows."\n<br>"; // TODO kill
	        //echo "getSingleBannerFirst BannerType: ".$objBanners->banner_type."\n<br>"; //TODO kill
	        switch ($objBanners->banner_type)
	        {
	            case self::BANNER_TYPE_INTERN :
	                //Pfad+Dateiname holen ueber ID
	                $objFile = \FilesModel::findByPk($objBanners->banner_image);
	                //BannerImage Class
	                $this->import('\Banner\BannerImage', 'BannerImage');
	                //Banner Art und Größe bestimmen
	                $arrImageSize = $this->BannerImage->getBannerImageSize($objFile->path, self::BANNER_TYPE_INTERN);
	                //Banner Neue Größe 0:$Width 1:$Height
	                $arrNewSizeValues = deserialize($objBanners->banner_imgSize);
	                //Banner Neue Größe ermitteln, return array $Width,$Height,$oriSize
	                $arrImageSizenNew = $this->BannerImage->getBannerImageSizeNew($arrImageSize[0],$arrImageSize[1],$arrNewSizeValues[0],$arrNewSizeValues[1]);
	    
	                //wenn oriSize = true, oder bei GIF/SWF/SWC = original Pfad nehmen
	                if ($arrImageSizenNew[2] === true
	                        || $arrImageSize[2] == 1  // GIF
	                        || $arrImageSize[2] == 4  // SWF
	                        || $arrImageSize[2] == 13 // SWC
	                )
	                {
	                    $FileSrc = $objFile->path;
	                    $arrImageSize[0] = $arrImageSizenNew[0];
	                    $arrImageSize[1] = $arrImageSizenNew[1];
	                    $arrImageSize[3] = ' height="'.$arrImageSizenNew[1].'" width="'.$arrImageSizenNew[0].'"';
	                }
	                else
	                {
	                    $FileSrc = \Image::get($this->urlEncode($objFile->path), $arrImageSizenNew[0], $arrImageSizenNew[1],'proportional');
	                    $arrImageSize[0] = $arrImageSizenNew[0];
	                    $arrImageSize[1] = $arrImageSizenNew[1];
	                    $arrImageSize[3] = ' height="'.$arrImageSizenNew[1].'" width="'.$arrImageSizenNew[0].'"';
	                }
	                break;
	            case self::BANNER_TYPE_EXTERN :
	                //BannerImage Class
	                $this->import('\Banner\BannerImage', 'BannerImage');
	                //Banner Art und Größe bestimmen
	                $arrImageSize = $this->BannerImage->getBannerImageSize($objBanners->banner_image_extern, self::BANNER_TYPE_EXTERN);
	                //Banner Neue Größe 0:$Width 1:$Height
	                $arrNewSizeValues = deserialize($objBanners->banner_imgSize);
	                //Banner Neue Größe ermitteln, return array $Width,$Height,$oriSize
	                $arrImageSizenNew = $this->BannerImage->getBannerImageSizeNew($arrImageSize[0],$arrImageSize[1],$arrNewSizeValues[0],$arrNewSizeValues[1]);
	                //Umwandlung bei Parametern
	                $FileSrc = html_entity_decode($objBanners->banner_image_extern, ENT_NOQUOTES, 'UTF-8');
	                //$src = $objBanners->banner_image_extern;
	                $arrImageSize[0] = $arrImageSizenNew[0];
	                $arrImageSize[1] = $arrImageSizenNew[1];
	                $arrImageSize[3] = ' height="'.$arrImageSizenNew[1].'" width="'.$arrImageSizenNew[0].'"';
	                break;
	            case self::BANNER_TYPE_TEXT :
	                $arrImageSize = false;
	                break;
	        }
	        //TODO kill
	        //echo "getSingleBannerFirst arrImageSize: <pre>".print_r($arrImageSize,true)."</pre>\n<br>"; // TODO kill
	        //echo "getSingleBannerFirst FileSrc: $FileSrc";
	        if ($arrImageSize !== false) //Bilder extern/intern
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
	    
	            // Banner Seite als Ziel?
	            if ($objBanners->banner_jumpTo > 0)
	            {
	                $domain = \Environment::get('base');
	                $objParent = $this->getPageDetails($objBanners->banner_jumpTo);
	                if ($objParent->domain != '')
	                {
	                    $domain = (\Environment::get('ssl') ? 'https://' : 'http://') . $objParent->domain . TL_PATH . '/';
	                }
	                $objBanners->banner_url = $domain . $this->generateFrontendUrl($objParent->row(), '', $objParent->language);
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
	                    'src'            => specialchars(ampersand($FileSrc)),//specialchars(ampersand($this->urlEncode($FileSrc))),
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
	                    $src_fallback = $this->BannerImage->getCheckBannerImageFallback($FileSrc,$arrImageSize[0],$arrImageSize[1]);
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
	                            $fallback_content = $FileSrc ."<br />". specialchars(ampersand($objBanners->banner_comment)) ."<br />". specialchars(ampersand($objBanners->banner_name));
	                        }
	                        else
	                        {
	                            $fallback_content = $FileSrc ."<br>". specialchars(ampersand($objBanners->banner_comment)) ."<br>". specialchars(ampersand($objBanners->banner_name));
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
	                            'swf_src'        => specialchars(ampersand($FileSrc)),
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
	            if (($this->arrCategoryValues['banner_template'] != $this->strTemplate) 
	             && ($this->arrCategoryValues['banner_template'] != ''))
	            {
	                $this->strTemplate = $this->arrCategoryValues['banner_template'];
	                $this->Template = new \FrontendTemplate($this->strTemplate);
	            }
	            $this->arrBannerData = $arrBanners; //wird von BannerStatViewUpdate genutzt
	            $this->setStatViewUpdate();
	            $this->Template->banners = $arrBanners;
	            return true;
	    
	        }//$arrImageSize !== false
	    
	        // Text Banner
	        if ($objBanners->banner_type == 'banner_text')
	        {
	            if ($this->strFormat == 'xhtml')
	            {
	                $banner_target = ($objBanners->banner_target == '1') ? LINK_BLUR : LINK_NEW_WINDOW;
	            }
	            else
	            {
	                $banner_target = ($objBanners->banner_target == '1') ? '' : ' target="_blank"';
	            }
	    
	            // Banner Seite als Ziel?
	            if ($objBanners->banner_jumpTo > 0)
	            {
	                $domain = \Environment::get('base');
	                $objParent = $this->getPageDetails($objBanners->banner_jumpTo);
	                if ($objParent->domain != '')
	                {
	                    $domain = (\Environment::get('ssl') ? 'https://' : 'http://') . $objParent->domain . TL_PATH . '/';
	                }
	                $objBanners->banner_url = $domain . $this->generateFrontendUrl($objParent->row(), '', $objParent->language);
	            }
	    
	            // Kurz URL (nur Domain)
	            $treffer = parse_url($objBanners->banner_url);
	            $banner_url_kurz = $treffer['host'];
	            if (isset($treffer['port']))
	            {
	                $banner_url_kurz .= ':'.$treffer['port'];
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
	            if (($this->arrCategoryValues['banner_template'] != $this->strTemplate) 
	             && ($this->arrCategoryValues['banner_template'] != '')) 
	            {
	                $this->strTemplate = $this->arrCategoryValues['banner_template'];
	                $this->Template = new \FrontendTemplate($this->strTemplate);
	            }
	            $arrResults[] = $arrBanners[0];
	            $this->Template->banners = $arrResults;
	             
	            $this->arrBannerData = $arrResults;
	            $this->setStatViewUpdate();
	            return true;
	        }
	    }//Banner vorhanden
	    //falls $arrImageSize = false  und kein Text Banner
	    $this->Template->banners = $arrBanners; // leeres array
	}
	
	protected function getMultiBanner()
	{
	    /* $this->arrCategoryValues[...]
	     * banner_random
		 * banner_limit         - 0 all, other:max
	     */
	    
	    reset($this->arrAllBannersBasic); //sicher ist sicher
	     
	    //RandomBlocker entfernen falls möglich und nötig
	    if ( count($this->arrAllBannersBasic) >1 ) // einer muss ja übrig bleiben
	    {
	        $intRandomBlockerID = $this->getRandomBlockerId();
	        //TODO kill echo "geblockte Banner ID: $intRandomBlockerID \n<br>";
	        if (isset($this->arrAllBannersBasic[$intRandomBlockerID]))
	        {
	            unset($this->arrAllBannersBasic[$intRandomBlockerID]);
	        }
	    }
	    
	    if ( $this->arrCategoryValues['banner_random'] == 1 ) 
	    {
	        $this->shuffle_assoc($this->arrAllBannersBasic);
	    }
	    
	    //wenn limit gesetzt, array arrAllBannersBasic dezimieren
	    if ( $this->arrCategoryValues['banner_limit'] >0 ) 
	    {
	        $del = count($this->arrAllBannersBasic) - $this->arrCategoryValues['banner_limit'];
	        for ($i = 0; $i < $del; $i++) 
	        {
	            array_pop($this->arrAllBannersBasic);
	        }
	    }
	    
	    //Rest soll nun angezeigt werden.
	    //Schleife
	    while ( list($banner_id, $banner_weigth) = each($this->arrAllBannersBasic) )
	    {
	        $objBanners  = \Database::getInstance()
                                ->prepare("SELECT
                                                TLB.*
                                           FROM
                                	            tl_banner AS TLB
                                           WHERE
                                                TLB.`id`=?"
                                         )
                                ->limit(1)
                                ->execute( $banner_id );
	        $intRows = $objBanners->numRows;
	        //Banner vorhanden?
	        if($intRows > 0)
	        {
	            $objBanners->next();
	            
	            switch ($objBanners->banner_type)
	            {
	                case self::BANNER_TYPE_INTERN :
	                    //Pfad+Dateiname holen ueber ID
	                    $objFile = \FilesModel::findByPk($objBanners->banner_image);
	                    //BannerImage Class
	                    $this->import('\Banner\BannerImage', 'BannerImage');
	                    //Banner Art und Größe bestimmen
	                    $arrImageSize = $this->BannerImage->getBannerImageSize($objFile->path, self::BANNER_TYPE_INTERN);
	                    //Banner Neue Größe 0:$Width 1:$Height
	                    $arrNewSizeValues = deserialize($objBanners->banner_imgSize);
	                    //Banner Neue Größe ermitteln, return array $Width,$Height,$oriSize
	                    $arrImageSizenNew = $this->BannerImage->getBannerImageSizeNew($arrImageSize[0],$arrImageSize[1],$arrNewSizeValues[0],$arrNewSizeValues[1]);
	                     
	                    //wenn oriSize = true, oder bei GIF/SWF/SWC = original Pfad nehmen
	                    if ($arrImageSizenNew[2] === true
	                            || $arrImageSize[2] == 1  // GIF
	                            || $arrImageSize[2] == 4  // SWF
	                            || $arrImageSize[2] == 13 // SWC
	                    )
	                    {
	                        $FileSrc = $objFile->path;
	                        $arrImageSize[0] = $arrImageSizenNew[0];
	                        $arrImageSize[1] = $arrImageSizenNew[1];
	                        $arrImageSize[3] = ' height="'.$arrImageSizenNew[1].'" width="'.$arrImageSizenNew[0].'"';
	                    }
	                    else
	                    {
	                        $FileSrc = \Image::get($this->urlEncode($objFile->path), $arrImageSizenNew[0], $arrImageSizenNew[1],'proportional');
	                        $arrImageSize[0] = $arrImageSizenNew[0];
	                        $arrImageSize[1] = $arrImageSizenNew[1];
	                        $arrImageSize[3] = ' height="'.$arrImageSizenNew[1].'" width="'.$arrImageSizenNew[0].'"';
	                    }
	                    break;
	                case self::BANNER_TYPE_EXTERN :
	                    //BannerImage Class
	                    $this->import('\Banner\BannerImage', 'BannerImage');
	                    //Banner Art und Größe bestimmen
	                    $arrImageSize = $this->BannerImage->getBannerImageSize($objBanners->banner_image_extern, self::BANNER_TYPE_EXTERN);
	                    //Banner Neue Größe 0:$Width 1:$Height
	                    $arrNewSizeValues = deserialize($objBanners->banner_imgSize);
	                    //Banner Neue Größe ermitteln, return array $Width,$Height,$oriSize
	                    $arrImageSizenNew = $this->BannerImage->getBannerImageSizeNew($arrImageSize[0],$arrImageSize[1],$arrNewSizeValues[0],$arrNewSizeValues[1]);
	                    //Umwandlung bei Parametern
	                    $FileSrc = html_entity_decode($objBanners->banner_image_extern, ENT_NOQUOTES, 'UTF-8');
	                    //$src = $objBanners->banner_image_extern;
	                    $arrImageSize[0] = $arrImageSizenNew[0];
	                    $arrImageSize[1] = $arrImageSizenNew[1];
	                    $arrImageSize[3] = ' height="'.$arrImageSizenNew[1].'" width="'.$arrImageSizenNew[0].'"';
	                    break;
	                case self::BANNER_TYPE_TEXT :
	                    $arrImageSize = false;
	                    break;
	            }
	            //TODO kill
	            //echo "getSingleBannerFirst arrImageSize: <pre>".print_r($arrImageSize,true)."</pre>\n<br>"; // TODO kill
	            //echo "getSingleBannerFirst FileSrc: $FileSrc";
	            if ($arrImageSize !== false) //Bilder extern/intern
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
	                 
	                // Banner Seite als Ziel?
	                if ($objBanners->banner_jumpTo > 0)
	                {
	                    $domain = \Environment::get('base');
	                    $objParent = $this->getPageDetails($objBanners->banner_jumpTo);
	                    if ($objParent->domain != '')
	                    {
	                        $domain = (\Environment::get('ssl') ? 'https://' : 'http://') . $objParent->domain . TL_PATH . '/';
	                    }
	                    $objBanners->banner_url = $domain . $this->generateFrontendUrl($objParent->row(), '', $objParent->language);
	                }
	                
	                $arrBanners = array();
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
	                        'src'            => specialchars(ampersand($FileSrc)),//specialchars(ampersand($this->urlEncode($FileSrc))),
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
	                        $src_fallback = $this->BannerImage->getCheckBannerImageFallback($FileSrc,$arrImageSize[0],$arrImageSize[1]);
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
	                                $fallback_content = $FileSrc ."<br />". specialchars(ampersand($objBanners->banner_comment)) ."<br />". specialchars(ampersand($objBanners->banner_name));
	                            }
	                            else
	                            {
	                                $fallback_content = $FileSrc ."<br>". specialchars(ampersand($objBanners->banner_comment)) ."<br>". specialchars(ampersand($objBanners->banner_name));
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
	                                'swf_src'        => specialchars(ampersand($FileSrc)),
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
	                $arrResults[] = $arrBanners[0];
	                
	                $this->arrBannerData = $arrBanners[0]; //wird von setStatViewUpdate genutzt
	                $this->setStatViewUpdate();
	                //$this->Template->banners = $arrBanners;
	                //return true;
	                 
	            }//$arrImageSize !== false
	             
	            // Text Banner
	            if ($objBanners->banner_type == 'banner_text')
	            {
	                if ($this->strFormat == 'xhtml')
	                {
	                    $banner_target = ($objBanners->banner_target == '1') ? LINK_BLUR : LINK_NEW_WINDOW;
	                }
	                else
	                {
	                    $banner_target = ($objBanners->banner_target == '1') ? '' : ' target="_blank"';
	                }
	                 
	                // Banner Seite als Ziel?
	                if ($objBanners->banner_jumpTo > 0)
	                {
	                    $domain = \Environment::get('base');
	                    $objParent = $this->getPageDetails($objBanners->banner_jumpTo);
	                    if ($objParent->domain != '')
	                    {
	                        $domain = (\Environment::get('ssl') ? 'https://' : 'http://') . $objParent->domain . TL_PATH . '/';
	                    }
	                    $objBanners->banner_url = $domain . $this->generateFrontendUrl($objParent->row(), '', $objParent->language);
	                }
	                 
	                // Kurz URL (nur Domain)
	                $treffer = parse_url($objBanners->banner_url);
	                $banner_url_kurz = $treffer['host'];
	                if (isset($treffer['port']))
	                {
	                    $banner_url_kurz .= ':'.$treffer['port'];
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
	                
	                $arrResults[] = $arrBanners[0];
	                //$this->Template->banners = $arrResults;
	            
	                $this->arrBannerData = $arrBanners[0]; //wird von setStatViewUpdate genutzt
	                $this->setStatViewUpdate();
	                
	            }//text banner
	            
	            
	            
	            
	            
	        }//Banner vorhanden
	    } // while each($this->arrAllBannersBasic)
	    
	    //anderes Template?
	    if (($this->arrCategoryValues['banner_template'] != $this->strTemplate) 
	     && ($this->arrCategoryValues['banner_template'] != ''))
	    {
	        $this->strTemplate = $this->arrCategoryValues['banner_template'];
	        $this->Template = new \FrontendTemplate($this->strTemplate);
	    }
	    
	    //falls $arrImageSize = false  und kein Text Banner ist es ein leeres array
	    $this->Template->banners = $arrResults;
	}
	
	/**
     * shuffle for associative arrays, preserves key=>value pairs.
     * http://www.php.net/manual/de/function.shuffle.php
     */
    protected function shuffle_assoc(&$array) 
    {
        $keys = array_keys($array);
        shuffle($keys);
        shuffle($keys);
    
        foreach($keys as $key) 
        {
            $new[$key] = $array[$key];
            unset($array[$key]); /* save memory */
        }
        $array = $new;
        
        return true;
    }
    

/*
   _____                  _   _                      __         _                     
  / ____|                | | (_)                    / _|       (_)                    
 | |     ___  _   _ _ __ | |_ _ _ __   __ _    ___ | |_  __   ___  _____      _____   
 | |    / _ \| | | | '_ \| __| | '_ \ / _` |  / _ \|  _| \ \ / / |/ _ \ \ /\ / / __|  
 | |___| (_) | |_| | | | | |_| | | | | (_| | | (_) | |    \ V /| |  __/\ V  V /\__ \_ 
  \_____\___/ \__,_|_| |_|\__|_|_| |_|\__, |  \___/|_|     \_/ |_|\___| \_/\_/ |___(_)
                                       __/ |                                          
   & Blocking                         |___/       
*/	
	
	/**
	 * Insert/Update Banner View Stat
	 */
	protected function setStatViewUpdate()
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
	    
	    $intCatID = ($this->banner_categories >0) ? $this->banner_categories : 42 ; // Answer to the Ultimate Question of Life, the Universe, and Everything
	    //log_message('BannerStatViewUpdate $intCatID:'.$intCatID,'Banner.log');
	    $ClientIP = bin2hex(sha1($intCatID . \Environment::get('remoteAddr'),true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    $lastBanner = array_pop($this->arrBannerData);
	    $BannerID = $lastBanner['banner_id'];
	    if ($BannerID==0)
	    { // kein Banner, nichts zu tun
	        return;
	    }
	    $BannerBlockTime = time() - 60*10;   // 10 Minuten, 0-10 min wird geblockt
	    $BannerCleanTime = time() - 60*10*3; // 30 Minuten, Einträge >= 30 Minuten werden gelöscht
	    if ( isset($GLOBALS['TL_CONFIG']['mod_banner_block_time'] ) 
	     && intval($GLOBALS['TL_CONFIG']['mod_banner_block_time'])>0
	       )
	    {
	        $BannerBlockTime = time() - 60*intval($GLOBALS['TL_CONFIG']['mod_banner_block_time']);
	        $BannerCleanTime = time() - 60*3*intval($GLOBALS['TL_CONFIG']['mod_banner_block_time']);
	    }
	    
	    \Database::getInstance()->prepare("DELETE FROM 
                                                tl_banner_stat_blocker 
                                           WHERE 
                                                tstamp<? 
                                           AND 
                                                type=?")
                                ->execute($BannerCleanTime, 'v');
	    
	    $objBanners = \Database::getInstance()->prepare("SELECT 
                                                            id 
                                                         FROM 
                                                            tl_banner_stat_blocker 
                                                         WHERE 
                                                            bid=? 
                                                         AND 
                                                            tstamp>? 
                                                         AND 
                                                            ip=? 
                                                         AND 
                                                            type=?")
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
	        \Database::getInstance()->prepare("INSERT INTO tl_banner_stat_blocker %s")
                                    ->set($arrSet)
                                    ->execute();
	        // nicht blocken
	    }
	    else
	    {
	        // Eintrag innerhalb der Blockzeit
	        return; // blocken, nicht zählen
	    }
	    
	    //Zählung
	    //TODO Optimierung durch INSERT IGNORE
	    //alte Daten lesen
	    $objBanners = \Database::getInstance()->prepare("SELECT 
                                                            * 
                                                         FROM 
                                                            tl_banner_stat 
                                                         WHERE 
                                                            id=?")
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
	        \Database::getInstance()->prepare("INSERT INTO tl_banner_stat %s")
                                    ->set($arrSet)
                                    ->execute();
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
	        \Database::getInstance()->prepare("UPDATE 
                                                    tl_banner_stat 
                                               SET 
                                                    tstamp=?
                                                   ,banner_views=? 
                                               WHERE 
                                                    id=?")
                                    ->execute($arrSet['tstamp'], $arrSet['banner_views'], $arrSet['id']);
	    }
	    
	}//BannerStatViewUpdate()
	
	/**
	 * Spider Bot Check
	 */
	protected function BannerCheckBot()
	{
	    if (isset($GLOBALS['TL_CONFIG']['mod_banner_bot_check']) 
	      && (int)$GLOBALS['TL_CONFIG']['mod_banner_bot_check'] == 0
	       )
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
	    $this->import('\BotDetection\ModuleBotDetection','ModuleBotDetection');
	    if ($this->ModuleBotDetection->BD_CheckBotAgent() || $this->ModuleBotDetection->BD_CheckBotIP())
	    {
	        //log_message('BannerCheckBot True','Banner.log');
	        return true;
	    }
	    //log_message('BannerCheckBot False','Banner.log');
	    return false;
	} //BannerCheckBot
	
	/**
	 * HTTP_USER_AGENT Special Check
	 */
	protected function CheckUserAgent()
	{
	    if ( \Environment::get('httpUserAgent') )  
	    {
	        $UserAgent = trim(\Environment::get('httpUserAgent'));
	    } 
	    else 
	    {
	        return false; // Ohne Absender keine Suche
	    }
	    $arrUserAgents = explode(",", $this->banner_useragent);
	    if (strlen(trim($arrUserAgents[0])) == 0) 
	    {
	        return false; // keine Angaben im Modul
	    }
	    array_walk($arrUserAgents, array('self','banner_array_trim_value'));  // trim der array values
	    // grobe Suche
	    $CheckUserAgent = str_replace($arrUserAgents, '#', $UserAgent);
	    if ($UserAgent != $CheckUserAgent) 
	    {   // es wurde ersetzt also was gefunden
	        // log_message('CheckUserAgent Filterung; Treffer!','Banner.log');
	        return true;
	    }
	    return false;
	} //CheckUserAgent
	public static function banner_array_trim_value(&$data) 
	{
	    $data = trim($data);
	    return ;
	}
	
	
	
} // class
































