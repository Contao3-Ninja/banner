<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *
 * Modul Banner - FE Helper Class BannerHelper
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
 * Class BannerHelper
 *
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer
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
	
	protected $statusAllBannersBasic = true;
	
	/**
	 * Category values 
	 * @var mixed	array|false, false if category not exists
	 */
	protected $arrCategoryValues = array();
	
	/**
	 * All Banners from a category, Basic Data
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
		
		//set $arrCategory over tl_banner_category
		if ($this->getSetCategoryValues()===false) { return false; }
		
		//check for protected user groups
		//set $statusBannerFrontendGroupView
		$this->checkSetUserFrontendLogin();
		
		//get basic banner infos
		if ($this->getSetAllBannerForCategory() === false) 
		{
			$this->statusAllBannersBasic = false;
		}
		//TODO wenn das false ist, dann default banner falls gewollt
		//sonst weiter im Programm
		//Single Banner? ja, dann Gewichtung nach vorhandenen Wichtungen
		
		global $objPage;
		if ($objPage->outputFormat == 'html5')
		{
		    $this->strFormat = 'html5';
		}
	}
	
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
				'banner_numbers'		=> $objBannerCategory->banner_numbers,
				'banner_random'			=> $objBannerCategory->banner_random,
				'banner_limit'			=> $objBannerCategory->banner_limit, // 0:all, others = max 
				'banner_protected'		=> $objBannerCategory->banner_protected,
				'banner_group'			=> $arrGroup[0]
				);
		return true;
	}
	
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
	 * Get all Banner for category, set $arrAllBannersBasic
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
	
} // class
































