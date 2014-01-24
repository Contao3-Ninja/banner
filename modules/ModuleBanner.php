<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2013 Leo Feyer
 *
 * Modul Banner - Frontend 
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
 * Class ModuleBanner
 *
 * @copyright  Glen Langer 2007..2013 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    Banner
 * @license    LGPL
 */
class ModuleBanner extends \BugBuster\Banner\BannerHelper
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_banner_list_all';
	
		
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
	    if (TL_MODE == 'BE')
	    {
	        $objTemplate = new \BackendTemplate('be_wildcard');
	        $objTemplate->wildcard = '### BANNER MODUL ###';
	        $objTemplate->title = $this->headline;
	        $objTemplate->id = $this->id;
	        $objTemplate->link = $this->name;
	        $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
	        
	        return $objTemplate->parse();
	    }
	    return parent::generate();
	}
	
	
	protected function compile()
	{
		if ($this->BannerHelperInit() === false)
		{
			return ;
		}

		if ($this->statusBannerFrontendGroupView === false)
		{
			// Eingeloggter FE Nutzer darf nichts sehen, falsche Gruppe
			// auf Leer umschalten
			$this->strTemplate='mod_banner_empty';
			$this->Template = new \FrontendTemplate($this->strTemplate);
			return ;
		}
		
		if ($this->statusAllBannersBasic === false)
		{
			//keine Banner vorhanden in der Kategorie
			//default Banner holen
			//kein default Banner, ausblenden wenn leer?
			$this->getDefaultBanner();
			return ;			
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
		        return ;
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
		        return ;
		    }
		    else 
		    {
    		    //single banner
		        $this->getSingleBanner();
		        return ;
		    }
		}
		else
		{
		    //multi banner
		    $this->getMultiBanner();
		    return ;
		}
		
	}
	

	public function __toString()
	{
		return "\n<br>
		Category: <pre>".print_r($this->arrCategoryValues,true)."</pre>\n<br>
		FrontendGroupView: ".print_r((int)$this->statusBannerFrontendGroupView,true)."\n<br>
		AllBannersBasic: <pre>".print_r($this->arrAllBannersBasic,true)."</pre>\n<br>
		CountBannerSeen: ".count(self::$arrBannerSeen)."\n<br>
		EinzelBanner 0:single,1:multi: ".print_r((int)$this->arrCategoryValues['banner_numbers'],true)."\n<br>
		<hr>";
	}
}
