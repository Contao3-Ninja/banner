<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 *
 * Modul Banner Tag - Frontend 
 *
 * @copyright  Glen Langer 2007..2014 <http://www.contao.glen-langer.de>
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
 * Class ModuleBannerTag
 *
 * @copyright  Glen Langer 2007..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    Banner
 * @license    LGPL
 */
class ModuleBannerTag extends \BugBuster\Banner\BannerHelper
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_banner_list_all';
	
	protected $typePrefix = ''; // wie wurde das Modul eingebaut
                                // ce_  als Artikelelement
                                // mod_ als Modul direkt im Layout
	
	//backward compatibility over getModuleData()
	protected $banner_hideempty  = '';
	protected $banner_firstview  = '';
	protected $banner_categories = '';
	protected $banner_template   = '';
	protected $banner_redirect   = '';
	protected $banner_useragent  = '';
	protected $Template          = '';
	protected $cssID             = ''; //serialisiertes Array
	protected $space             = ''; //serialisiertes Array
	protected $headline          = ''; //serialisiertes Array, unit und value
	protected $article_class     = ''; // mod_banner artikelcssclass
	protected $article_cssID     = ''; // id="artikelcssid"
	protected $article_style     = ''; // margin-top:55px; margin-bottom:66px;
	
	
	/**
	 * 
	 * @param unknown $strTag
	 * @return boolean|void
	 */
	public function ReplaceInsertTagsBanner($strTag)
	{
	    $arrTag = trimsplit('::', $strTag);
	    if ($arrTag[0] != 'banner_module')
	    {
	        if ($arrTag[0] != 'cache_banner_module')
	        {
	            return false; // nicht für uns
	        }
	    }
	    //DEBUG
	    //log_message('--------------------------------'.$arrTag[1],'Banner.log');
	    if (isset($arrTag[1]))
	    {
	        $retModuleData = $this->getModuleData($arrTag[1]);
	        if (false === $retModuleData) 
	        {
	        	//kein Banner Modul mit dieser ID 
	        	$this->log('No banner module with this id "'.$arrTag[1].'"', 'ModuleBannerTag ReplaceInsertTagsBanner', TL_ERROR);
	           return false;
	        }
	    }
	    else 
	    {
	        //keine Banner Modul ID
	        $this->log('Missing parameter (1): banner module id', 'ModuleBannerTag ReplaceInsertTagsBanner', TL_ERROR);
	        return false;
	    }
	    if (isset($arrTag[2])) { $this->typePrefix    = $arrTag[2]; } //ce_ / mod_
	    if (isset($arrTag[3])) { $this->article_class = $arrTag[3]; }
	    if (isset($arrTag[4])) { $this->article_cssID = $arrTag[4]; }
	    if (isset($arrTag[5])) { $this->article_style = $arrTag[5]; }
	    
	    return $this->generateBanner();
	}
	
	/**
	 * Wrapper for backward compatibility
	 * 
	 * @param integer $moduleId
	 * @return boolean
	 */
	protected function getModuleData($moduleId)
	{
	    //DEBUG
	    //log_message('getModuleData Banner Modul ID:'.$moduleId,'Banner.log');
	    $objBannerModule = \Database::getInstance()->prepare("SELECT 
                                                                    banner_hideempty,
                                                        	        banner_firstview,
                                                        	        banner_categories,
                                                        	        banner_template,
                                                        	        banner_redirect,
                                                        	        banner_useragent,
                                                                    cssID,
                                                                    space,
                                                                    headline 
                                                                FROM  
                                                                    tl_module 
                                                                WHERE 
                                                                    id=?
                                                                AND
                                                                    type=?")
											         ->execute($moduleId, 'banner'); 
        if ($objBannerModule->numRows == 0)
        {
            return false;
        }
        $this->banner_hideempty  = $objBannerModule->banner_hideempty;
        $this->banner_firstview  = $objBannerModule->banner_firstview;
        $this->banner_categories = $objBannerModule->banner_categories;
        $this->banner_template   = $objBannerModule->banner_template;
        $this->banner_redirect   = $objBannerModule->banner_redirect;
        $this->banner_useragent  = $objBannerModule->banner_useragent;
        $this->cssID             = $objBannerModule->cssID;
        $this->space             = $objBannerModule->space;
        $this->headline          = $objBannerModule->headline;
        return true;         
	}
	
	protected function generateBanner()
	{
	    //DEBUG
	    //log_message('generateBanner banner_categories:'.$this->banner_categories,'Banner.log');
		if ($this->BannerHelperInit() === false)
		{
			$this->log('Problem in BannerHelperInit', 'ModuleBannerTag generateBanner', TL_ERROR);
	        return false;
		}

		if ($this->statusBannerFrontendGroupView === false)
		{
			// Eingeloggter FE Nutzer darf nichts sehen, falsche Gruppe
			// auf Leer umschalten
			$this->strTemplate='mod_banner_empty';
			$this->Template = new \FrontendTemplate($this->strTemplate);
	        return $this->Template->parse();
		}
		$this->Template = new \FrontendTemplate($this->strTemplate);
		
		if ($this->statusAllBannersBasic === false)
		{
			//keine Banner vorhanden in der Kategorie
			//default Banner holen
			//kein default Banner, ausblenden wenn leer?
			$this->getDefaultBanner();
			//Css generieren
			$this->setCssClassIdStyle();
			//Template parsen und Ergebnis zurückgeben
			return $this->Template->parse();
		}
		
		//OK, Banner vorhanden, dann weiter
		//BannerSeen vorhanden? Dann beachten.
		if ( count(self::$arrBannerSeen) ) 
		{
		    //$arrAllBannersBasic dezimieren um die bereits angezeigten
		    foreach (self::$arrBannerSeen as $BannerSeenID) 
		    {
		        if (array_key_exists($BannerSeenID,$this->arrAllBannersBasic)) 
		        {
		            unset($this->arrAllBannersBasic[$BannerSeenID]);
		        };
		    }
		    //noch Banner übrig?
		    if ( count($this->arrAllBannersBasic) == 0 )
		    {
		        //default Banner holen
		        //kein default Banner, ausblenden wenn leer?
		        $this->getDefaultBanner();
		        //Css generieren
		        $this->setCssClassIdStyle();
		        return $this->Template->parse();
		    }
		}
		
		//OK, noch Banner übrig, weiter gehts	
		//Single Banner? 
		if ($this->arrCategoryValues['banner_numbers'] != 1) 
		{
		    //FirstViewBanner?
		    if ($this->getSetFirstView() === true) 
		    {
		        $this->getSingleBannerFirst();
		        //Css generieren
		        $this->setCssClassIdStyle();
		        return $this->Template->parse();
		    }
		    else 
		    {
    		    //single banner
		        $this->getSingleBanner();
		        //Css generieren
		        $this->setCssClassIdStyle();
		        return $this->Template->parse();
		    }
		}
		else
		{
		    //multi banner
		    $this->getMultiBanner();
		    //Css generieren
		    $this->setCssClassIdStyle();
		    return $this->Template->parse();
		}
		
	}
	
	protected function setCssClassIdStyle()
	{
	    //Modul direkt im Layout
	    if ('mod_' == $this->typePrefix) 
	    {
	        //CSS-ID/Klasse
	        $_cssID = deserialize($this->cssID);
	    	$this->Template->cssID = 'id="'.$_cssID[0].'"';
	    	$this->Template->class = 'mod_banner '.$_cssID[1];
            //Abstand davor und dahinter
	    	$_style = deserialize($this->space);
	    	if ("" != $_style[0]) 
	    	{
	    		$this->Template->style .= 'margin-top:'.$_style[0].'px; ';
	    	}
	    	if ("" != $_style[1])
	    	{
	    	    $this->Template->style .= 'margin-bottom:'.$_style[1].'px;';
	    	}
	    }
	    //Modul als Artikelelement
	    if ('ce_' == $this->typePrefix) 
	    {
	    	$this->Template->cssID = $this->article_cssID;
	    	$this->Template->class = $this->article_class;
	    	$this->Template->style = $this->article_style;
	    }
	    //headline
	    $_headline = deserialize($this->headline);
	    if ("" != $_headline['value'])
	    {
	        $this->Template->hl       = $_headline['unit'];
	        $this->Template->headline = $_headline['value'];
	    }
	}

}
