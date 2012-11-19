<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *
 * Modul Banner - Frontend 
 *
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer
 * @package    Banner
 * @license    LGPL
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Banner;

/**
 * Class ModuleBanner
 *
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer
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
	        if (version_compare(VERSION, '2.9', '>'))
	        {
	            // Code für Versionen ab 3.0
	            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
	        }
	        else
	        {
	            // Code für Versionen < 3.0
	            $objTemplate->wildcard = '### BANNER MODULE ONLY FOR CONTAO 3.0 AND ABOVE ###';
	        }
	        return $objTemplate->parse();
	    }
	    return parent::generate();
	}
	
	
	protected function compile()
	{
		if ($this->BannerHelperInit() === false)
		{
			echo "Init false! ".$this; //TODO: kill
			return ;
		}
		echo $this; //TODO: kill
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
		
		
	}
	

	public function __toString()
	{
		return "\n<br>
		Category: <pre>".print_r($this->arrCategoryValues,true)."</pre>\n<br>
		FrontendGroupView: ".print_r((int)$this->statusBannerFrontendGroupView,true)."\n<br>
		AllBannersBasic: <pre>".print_r($this->arrAllBannersBasic,true)."</pre>\n<br>
		";
	}
}
