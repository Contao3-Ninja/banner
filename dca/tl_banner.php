<?php 
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * 
 * Modul Banner - Backend DCA tl_banner
 * 
 * This is the data container array for table tl_banner.
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer
 * @package    Banner
 * @license    LGPL
 */

/**
 * Class tl_banner
 *
 * Methods that are used by the DCA
 */
class tl_banner extends Backend
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
     * Import the back end user object
     * and the BannerImage object
     */
    public function __construct()
    {
            parent::__construct();
            $this->import('BackendUser', 'User');
            $this->import('\Banner\BannerImage', 'BannerImage');
    }
    
	/**
	 * List banner record
	 * 
	 * @param object $row
	 */
	public function listBanner($row)
	{
		switch ($row['banner_type'])
		{
		    case self::BANNER_TYPE_INTERN :
		        return $this->listBannerInternal($row);
		        break;
		    case self::BANNER_TYPE_EXTERN :
		        return $this->listBannerExternal($row);
		        break;
		    case self::BANNER_TYPE_TEXT :
		        return $this->listBannerText($row);
		        break;
		    default :
		        return false;
		    break;
		}
	}
	
	/**
	 * List internal banner record
	 * 
	 * @param object $row
	 * @return string	record as html
	 */
	protected function listBannerInternal($row)
	{
		if ($row['banner_image'] == '') 
		{
			return '<p class="error">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_read_error'].'</p>';
		}
		// Check for version 3 format
		if (!is_numeric($row['banner_image']))
		{
		    return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
		}
		
		//convert DB file ID into file path ($objFile->path)
		$objFile = \FilesModel::findByPk($row['banner_image']);
		
		//get image size
		$arrImageSize = $this->BannerImage->getBannerImageSize($objFile->path, self::BANNER_TYPE_INTERN);
		
		//resize if necessary
		$arrImageSizeNew = array();
		switch ($arrImageSize[2]) 
		{
		    case 1:
		    case 2:
		    case 3:
		    	$arrImageSizeNew = $this->BannerImage->getCheckBannerImageSize($arrImageSize,250,40);
		    	$intWidth  = $arrImageSizeNew[0];
		    	$intHeight = $arrImageSizeNew[1];
		    	$oriSize   = $arrImageSizeNew[2];
		    	if ($oriSize) 
		    	{
		    	    $banner_image = $this->urlEncode($objFile->path);
		    	} 
		    	else 
		    	{
		    	    $banner_image = Image::get($this->urlEncode($objFile->path), $intWidth, $intHeight);
		    	}
		    	break;
		    case 4:  // Flash swf
		    case 13: // Flash swc
		    	$arrImageSizeNew = $this->BannerImage->getCheckBannerImageSize($arrImageSize,250,40);
		    	$intWidth  = $arrImageSizeNew[0];
		    	$intHeight = $arrImageSizeNew[1];
		    	$oriSize   = $arrImageSizeNew[2];
		    	$banner_image = $objFile->path;
		    	
		    	$fallback_content = '<br><span style="font-weight: normal;">'.$GLOBALS['TL_LANG']['tl_banner']['source_fallback_no'].'</span>';
		    	$src_fallback = $this->BannerImage->getCheckBannerImageFallback($objFile->path, $intWidth, $intHeight);
		    	if ( $src_fallback !== false) 
		    	{
		    		$fallback_content = '<br><img src="' . $src_fallback . '" alt="'.specialchars(ampersand($row['banner_name'])).'" height="'.$intHeight.'" width="'.$intWidth.'"><br><span style="font-weight: normal;">'.$GLOBALS['TL_LANG']['tl_banner']['source_fallback'].'</span>';
		    	}
		    	break;
		    default:
		        break;
		}
		
		//Banner Ziel per Page?
		if ($row['banner_jumpTo'] >0) 
		{
		    //url generieren
		    $objBannerNextPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
		    									->limit(1)
		    									->execute($row['banner_jumpTo']);
		    if ($objBannerNextPage->numRows)
		    {
		        $row['banner_url'] = $this->generateFrontendUrl($objBannerNextPage->fetchAssoc());
		    }
		}
		$banner_url = html_entity_decode($row['banner_url'], ENT_NOQUOTES, 'UTF-8');
		if (strlen($banner_url)>0) 
		{
		    $banner_url_text = $GLOBALS['TL_LANG']['tl_banner']['banner_url'][0].': ';
		} 
		else 
		{
		    $banner_url_text = '';
		}
		
		//Output
		switch ($arrImageSize[2]) 
		{
		    case 1:
		    case 2:
		    case 3:
		      $output = '<div class="mod_banner_be">' .
							'<div class="name"><img alt="'.specialchars(ampersand($row['banner_name'])).'" src="'. $banner_image .'" height="'.$intHeight.'" width="'.$intWidth.'" /></div>' .
							'<div class="right">' .
								'<div class="left">'.
									'<div class="published_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_published'][0].'</div>'.
									'<div class="published_data">'.($row['banner_published'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_no'] : $GLOBALS['TL_LANG']['tl_banner']['tl_be_yes']).' </div>'.
								'</div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_type'][0].'</div>' .
									'<div class="date_data">'.$GLOBALS['TL_LANG']['tl_banner']['source_intern'] .'</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_start'].'</div>' .
									'<div class="date_data">' . ($row['banner_start']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_start'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_start'])) . '</div>' .
								'</div>' .
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_stop'].'</div>' .
									'<div class="date_data">' . ($row['banner_stop'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_stop'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_stop'])) . '</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_views'].'</div>' .
									'<div class="date_data">' . ($row['banner_views_until']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_views_until']) . '</div>' .
								'</div>' .
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_clicks'].'</div>' .
									'<div class="date_data">' . ($row['banner_clicks_until'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_clicks_until']) . '</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
							'</div>' .
						'<div class="url">'.$banner_url_text . (strlen($banner_url)<80 ? $banner_url : substr($banner_url, 0, 36)."[...]".substr($banner_url,-36,36) ).'</div>' .
						'</div>';
		        break;
		    case 4:  // Flash swf
		    case 13: // Flash swc
		      $output = '<div class="mod_banner_be">' .
							'<div class="name"><div id="flash_'.$row['id'].'">'.specialchars(ampersand($row['banner_name'])).'</div>'.$fallback_content.'</div>' .
							'<div class="right">' .
								'<div class="left">'.
									'<div class="published_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_published'][0].'</div>'.
									'<div class="published_data">'.($row['banner_published'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_no'] : $GLOBALS['TL_LANG']['tl_banner']['tl_be_yes']).' </div>'.
								'</div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_type'][0].'</div>' .
									'<div class="date_data">'.$GLOBALS['TL_LANG']['tl_banner']['source_intern'] .'</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_start'].'</div>' .
									'<div class="date_data">' . ($row['banner_start']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_start'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_start'])) . '</div>' .
								'</div>' .
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_stop'].'</div>' .
									'<div class="date_data">' . ($row['banner_stop'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_stop'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_stop'])) . '</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_views'].'</div>' .
									'<div class="date_data">' . ($row['banner_views_until']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_views_until']) . '</div>' .
								'</div>' .
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_clicks'].'</div>' .
									'<div class="date_data">' . ($row['banner_clicks_until'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_clicks_until']) . '</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
							'</div>' .
							'<div class="url">'.$banner_url_text . (strlen($banner_url)<80 ? $banner_url : substr($banner_url, 0, 36)."[...]".substr($banner_url,-36,36) ). '</div>' .
						'</div>' .
	'<script type="text/javascript">
	/* <![CDATA[ */
	new Swiff("'.$banner_image.'", {
	  id: "flash_'.$row['id'].'",
	  width: '.$intWidth.',
	  height: '.$intHeight.',
	  params : {
	  allowfullscreen: "true",
	  wMode: "transparent",
	  flashvars: ""
	  }
	}).replaces($("flash_'.$row['id'].'"));
	/* ]]> */
	</script>';
		        break;
		    default:
		        break;
		}//switch
		
		if ($arrImageSize === false)
		{
		    //Interne Banner Grafik
		    $output = '<div class="mod_banner_be">' .
		            	'<div class="name"><span style="color:red;">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_read_error'].'</span><br />'.$this->urlEncode($row['banner_image']).'</div>' .
		              '</div>';
		}
		
		$key = $row['banner_published'] ? 'published' : 'unpublished';
		$style = 'style="font-size:11px;margin-bottom:10px;"';
		$output_h = '<div class="cte_type ' . $key . '" ' . $style . '><strong>' . specialchars(ampersand($row['banner_name'])) . '</strong></div>';
		return $output_h . $output;
	}
	
	/**
	 * List external banner record
	 *
	 * @param object $row
	 * @return string	record as html
	 */
	protected function listBannerExternal($row)
	{
		$fallback_content = '';
		$arrImageSize = $this->BannerImage->getBannerImageSize($row['banner_image_extern'], self::BANNER_TYPE_EXTERN);
		
		//resize if necessary
		$arrImageSizeNew = array();
		switch ($arrImageSize[2])
		{
		    case 1:
		    case 2:
		    case 3:
		        $arrImageSizeNew = $this->BannerImage->getCheckBannerImageSize($arrImageSize,250,40);
		        $intWidth  = $arrImageSizeNew[0];
		        $intHeight = $arrImageSizeNew[1];
		        $oriSize   = $arrImageSizeNew[2];
		        break;
		    case 4:  // Flash swf
		    case 13: // Flash swc
		        $arrImageSizeNew = $this->BannerImage->getCheckBannerImageSize($arrImageSize,250,40);
		        $intWidth  = $arrImageSizeNew[0];
		        $intHeight = $arrImageSizeNew[1];
		        $oriSize   = $arrImageSizeNew[2];
		        break;
		    default:
		        break;
		}
		$banner_image = $row['banner_image_extern'];
		
		//Banner Ziel per Page?
		if ($row['banner_jumpTo'] >0) 
		{
		    //url generieren
		    $objBannerNextPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
		    									->limit(1)
		    									->execute($row['banner_jumpTo']);
		    if ($objBannerNextPage->numRows)
		    {
		        $row['banner_url'] = $this->generateFrontendUrl($objBannerNextPage->fetchAssoc());
		    }
		}
		$banner_url = html_entity_decode($row['banner_url'], ENT_NOQUOTES, 'UTF-8');
		if (strlen($banner_url)>0) 
		{
		    $banner_url_text = $GLOBALS['TL_LANG']['tl_banner']['banner_url'][0].': ';
		} 
		else 
		{
		    $banner_url_text = '';
		}
		
		//Output
		switch ($arrImageSize[2]) 
		{
		    case 1:
		    case 2:
		    case 3:
		      $output = '<div class="mod_banner_be">' .
							'<div class="name"><img alt="'.specialchars(ampersand($row['banner_name'])).'" src="'. $banner_image .'" height="'.$intHeight.'" width="'.$intWidth.'" /></div>' .
							'<div class="right">' .
								'<div class="left">'.
									'<div class="published_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_published'][0].'</div>'.
									'<div class="published_data">'.($row['banner_published'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_no'] : $GLOBALS['TL_LANG']['tl_banner']['tl_be_yes']).' </div>'.
								'</div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_type'][0].'</div>' .
									'<div class="date_data">'.$GLOBALS['TL_LANG']['tl_banner']['source_extern'] .'</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_start'].'</div>' .
									'<div class="date_data">' . ($row['banner_start']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_start'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_start'])) . '</div>' .
								'</div>' .
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_stop'].'</div>' .
									'<div class="date_data">' . ($row['banner_stop'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_stop'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_stop'])) . '</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_views'].'</div>' .
									'<div class="date_data">' . ($row['banner_views_until']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_views_until']) . '</div>' .
								'</div>' .
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_clicks'].'</div>' .
									'<div class="date_data">' . ($row['banner_clicks_until'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_clicks_until']) . '</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
							'</div>' .
						'<div class="url">'.$banner_url_text . (strlen($banner_url)<80 ? $banner_url : substr($banner_url, 0, 36)."[...]".substr($banner_url,-36,36) ).'</div>' .
						'</div>';
		        break;
		    case 4:  // Flash swf
		    case 13: // Flash swc
		      $output = '<div class="mod_banner_be">' .
							'<div class="name"><div id="flash_'.$row['id'].'">'.specialchars(ampersand($row['banner_name'])).'</div>'.$fallback_content.'</div>' .
							'<div class="right">' .
								'<div class="left">'.
									'<div class="published_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_published'][0].'</div>'.
									'<div class="published_data">'.($row['banner_published'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_no'] : $GLOBALS['TL_LANG']['tl_banner']['tl_be_yes']).' </div>'.
								'</div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_type'][0].'</div>' .
									'<div class="date_data">'.$GLOBALS['TL_LANG']['tl_banner']['source_extern'] .'</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_start'].'</div>' .
									'<div class="date_data">' . ($row['banner_start']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_start'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_start'])) . '</div>' .
								'</div>' .
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_stop'].'</div>' .
									'<div class="date_data">' . ($row['banner_stop'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_stop'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_stop'])) . '</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_views'].'</div>' .
									'<div class="date_data">' . ($row['banner_views_until']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_views_until']) . '</div>' .
								'</div>' .
								'<div class="left">' .
									'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_clicks'].'</div>' .
									'<div class="date_data">' . ($row['banner_clicks_until'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_clicks_until']) . '</div>' .
								'</div>' .
								'<div style="clear:both;"></div>'.
							'</div>' .
							'<div class="url">'.$banner_url_text . (strlen($banner_url)<80 ? $banner_url : substr($banner_url, 0, 36)."[...]".substr($banner_url,-36,36) ). '</div>' .
						'</div>' .
	'<script type="text/javascript">
	/* <![CDATA[ */
	new Swiff("'.$banner_image.'", {
	  id: "flash_'.$row['id'].'",
	  width: '.$intWidth.',
	  height: '.$intHeight.',
	  params : {
	  allowfullscreen: "true",
	  wMode: "transparent",
	  flashvars: ""
	  }
	}).replaces($("flash_'.$row['id'].'"));
	/* ]]> */
	</script>';
		        break;
		    default:
		        break;
		}//switch
		
		if ($arrImageSize === false) 
		{
	        //Externe Banner Grafik
	        $output = '<div class="mod_banner_be">' .
						'<div class="name"><span style="color:red;">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_read_error'].'</span><br />'.$row['banner_image_extern'].'</div>' .
		              '</div>';
		}
		
		$key = $row['banner_published'] ? 'published' : 'unpublished';
		$style = 'style="font-size:11px;margin-bottom:10px;"';
		$output_h = '<div class="cte_type ' . $key . '" ' . $style . '><strong>' . specialchars(ampersand($row['banner_name'])) . '</strong></div>';
		return $output_h . $output;
	}
	
	/**
	 * List text banner record
	 *
	 * @param object $row
	 * @return string	record as html
	 */
	protected function listBannerText($row)
	{
		$banner_url = html_entity_decode($row['banner_url'], ENT_NOQUOTES, 'UTF-8');
		if (strlen($banner_url)>0)
		{
		    $banner_url_text = $GLOBALS['TL_LANG']['tl_banner']['banner_url'][0].': ';
		}
		else
		{
		    $banner_url_text = '';
		}
		//Output
		$output = '<div class="mod_banner_be">' .
			        '<div class="name"><br />'.$row['banner_name'].'<br /><span style="font-weight:normal;">'.nl2br($row['banner_comment']).'<br />'.(strlen($banner_url)<60 ? $banner_url : substr($banner_url, 0, 31)."[...]".substr($banner_url,-21,21) ).'</span></div>' .
			        '<div class="right">' .
				        '<div class="left">'.
					        '<div class="published_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_published'][0].'</div>'.
					        '<div class="published_data">'.($row['banner_published'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_no'] : $GLOBALS['TL_LANG']['tl_banner']['tl_be_yes']).' </div>'.
			        	'</div>'.
			        	'<div class="left">' .
			        		'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_type'][0].'</div>' .
			        		'<div class="date_data">'.$GLOBALS['TL_LANG']['tl_banner_type']['banner_text'].'</div>' .
			        	'</div>' .
			        	'<div style="clear:both;"></div>'.
			        	'<div class="left">' .
			        		'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_start'].'</div>' .
			        		'<div class="date_data">' . ($row['banner_start']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_start'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_start'])) . '</div>' .
			        	'</div>' .
			        	'<div class="left">' .
			        		'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_stop'].'</div>' .
			        		'<div class="date_data">' . ($row['banner_stop'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_stop'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_stop'])) . '</div>' .
			        	'</div>' .
			        	'<div style="clear:both;"></div>'.
			        	'<div class="left">' .
			        		'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_views'].'</div>' .
			        		'<div class="date_data">' . ($row['banner_views_until']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_views_until']) . '</div>' .
			        	'</div>' .
			        	'<div class="left">' .
			        		'<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_clicks'].'</div>' .
			        		'<div class="date_data">' . ($row['banner_clicks_until'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_clicks_until']) . '</div>' .
			        	'</div>' .
			        	'<div style="clear:both;"></div>'.
			        '</div>' .
		        '</div>';
		
		$key = $row['banner_published'] ? 'published' : 'unpublished';
		$style = 'style="font-size:11px;margin-bottom:10px;"';
		$output_h = '<div class="cte_type ' . $key . '" ' . $style . '><strong>' . specialchars(ampersand($row['banner_name'])) . '</strong></div>';
		return $output_h . $output;
	}
	
	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen(Input::get('tid')))
		{
			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
			$this->redirect($this->getReferer());
		}
		
		// Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_banner::banner_published', 'alexf'))
        {
                return '';
        }

		$href .= '&amp;tid='.$row['id'].'&amp;state='. ($row['banner_published'] ? '' : 1);

		if (!$row['banner_published'])
		{
			$icon = 'invisible.gif';
		}		

		return '<a href="'.$this->addToUrl($href.'&amp;id='.Input::get('id')).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}

	/**
	 * Disable/enable banner
	 * @param integer
	 * @param boolean
	 */
	public function toggleVisibility($intId, $blnVisible)
	{
		// Check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_banner::banner_published', 'alexf'))
        {
			$this->log('Not enough permissions to publish/unpublish Banner ID "'.$intId.'"', 'tl_banner toggleVisibility', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
        }
        
		// Update database
		$this->Database->prepare("UPDATE tl_banner SET banner_published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
					   ->execute($intId);
	}
}

/**
 * Table tl_banner
 */
$GLOBALS['TL_DCA']['tl_banner'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_banner_category',
		'enableVersioning'            => true
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'filter'                  => true,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'search,filter,limit',
			'headerFields'            => array('title', 'banner_protected', 'tstamp'),
			'child_record_callback'   => array('tl_banner', 'listBanner')
		),		
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
				'button_callback'     => array('tl_banner', 'toggleIcon')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
	      '__selector__'                => array('banner_type','banner_until'),
		  'default'                     => 'banner_type',
		  'banner_image'                => 'banner_type;{title_legend},banner_name,banner_weighting;{destination_legend},banner_url,banner_jumpTo,banner_target;{image_legend},banner_image,banner_imgSize;{comment_legend},banner_comment;{publish_legend},banner_published,banner_start,banner_stop,banner_until;{filter_legend:hide},banner_domain',
		  'banner_image_extern'         => 'banner_type;{title_legend},banner_name,banner_weighting;{destination_legend},banner_url,banner_target;{image_legend},banner_image_extern,banner_imgSize;{comment_legend},banner_comment;{publish_legend},banner_published,banner_start,banner_stop,banner_until;{filter_legend:hide},banner_domain',
		  'banner_text'                 => 'banner_type;{title_legend},banner_name,banner_weighting;{destination_legend},banner_url,banner_jumpTo,banner_target;{comment_legend},banner_comment;{publish_legend},banner_published,banner_start,banner_stop,banner_until;{filter_legend:hide},banner_domain'
	),
    // Subpalettes
	'subpalettes' => array
	(
		'banner_until'                => 'banner_views_until,banner_clicks_until'
	),


	// Fields
	'fields' => array
	(
	    'banner_type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_type'],
			'default'                 => 'default',
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => array('default','banner_image', 'banner_image_extern', 'banner_text'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_banner_type'],
			'eval'                    => array('helpwizard'=>false, 'submitOnChange'=>true)
		),
		'banner_name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_name'],
			'inputType'               => 'text',
			'search'                  => true,
			'explanation'	          => 'banner_help',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'helpwizard'=>true, 'tl_class'=>'w50')
		),
		'banner_weighting' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_weighting'],
			'default'                 => '2',
			'inputType'               => 'select',
			'options'                 => array('1', '2', '3'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_banner'],
			'explanation'	          => 'banner_help',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>1, 'rgxp'=>'prcnt', 'helpwizard'=>true, 'tl_class'=>'w50')
		),
		'banner_url' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_url'],
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'helpwizard'=>true)
		),
		'banner_jumpTo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_jumpTo'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'eval'                    => array('fieldType'=>'radio', 'helpwizard'=>true),
			'explanation'             => 'banner_help'
		), 
		'banner_target' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_target'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		),
		'banner_image' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_image'],
			'explanation'	          => 'banner_help',
			'inputType'               => 'fileTree',
			'eval'                    => array('files'=>true, 'filesOnly'=>true, 'fieldType'=>'radio', 'extensions'=>'jpg,jpe,gif,png,swf', 'maxlength'=>255, 'helpwizard'=>true)
		),
		'banner_image_extern' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_image_extern'],
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'helpwizard'=>true)
		),
        'banner_imgSize' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_imgSize'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'rgxp'=>'digit', 'nospace'=>true)
		),
        'banner_comment' => array
        (
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_comment'],
			'inputType'               => 'textarea',
			'explanation'             => 'banner_help',
			'eval'                    => array('mandatory'=>false, 'preserveTags'=>true, 'helpwizard'=>true)
        ),
		'banner_published' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_published'],
			'filter'                  => true,
			'inputType'               => 'checkbox'
		),
		'banner_start' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_start'],
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('maxlength'=>20, 'rgxp'=>'datim', 'datepicker'=>true, 'helpwizard'=>true, 'tl_class'=>'w50 wizard')
		),
		'banner_stop' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_stop'],
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('maxlength'=>20, 'rgxp'=>'datim', 'datepicker'=>true, 'helpwizard'=>true, 'tl_class'=>'w50 wizard')
		),
		'banner_until'  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_until'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr')
		),
		'banner_views_until' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_views_until'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('nospace'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'helpwizard'=>true, 'tl_class'=>'w50')
		),
		'banner_clicks_until' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_clicks_until'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('nospace'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'helpwizard'=>true, 'tl_class'=>'w50')
		),
		'banner_domain' => array
		(
	        'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_domain'],
	        'inputType'               => 'text',
	        'explanation'	          => 'banner_help',
	        'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'helpwizard'=>true)
		)
	)
);


