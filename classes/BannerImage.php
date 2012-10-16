<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *
 * Modul Banner - FE Class BannerImage
 *
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer
 * @package    Banner
 * @license    GPL
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Banner;

/** 
 * @author Data
 * 
 * 
 */
class BannerImage extends \Frontend
{
	/**
	 * Current version of the class.
	 */
	const BANNER_IMAGE_VERSION = '3.0.0';
	
	const BANNER_TYPE_INTERN = 'banner_image';
	const BANNER_TYPE_EXTERN = 'banner_image_extern';
	const BANNER_TYPE_TEXT   = 'banner_text';
	
	/**
	 * public constructor for phpunit
	 */
	public function __construct() 
	{
	    parent::__construct();
	}
	
	/**
	 * Returns the version number
	 *
	 * @return string
	 * @access public
	 */
	public function getVersion()
	{
	    return self::BANNER_IMAGE_VERSION;
	}
	
	public function getBannerImageSize($BannerImage,$BannerType)
	{
		switch ($BannerType)
		{
			case self::BANNER_TYPE_INTERN :
				return $this->getImageSizeInternal($BannerImage);
				break;
			case self::BANNER_TYPE_EXTERN :
				return $this->getImageSizeExternal($BannerImage);
				break;
			case self::BANNER_TYPE_TEXT :
				return false;
				break;
			default :
				return false;
			    break;
		}
	}
	
	/**
	 * Get the size of an internal image
	 * 
	 * @param	string	$BannerImage	Image path
	 * @return	mixed	$array / false
	 */
	protected function getImageSizeInternal($BannerImage)
	{
		$arrImageSize = @getimagesize(TL_ROOT . '/' . $BannerImage);
		if ($arrImageSize === false)
		{
		    //Workaround fuer PHP ohne zlib bei SWC Files
		    $arrImageSize = $this->getImageSizeCompressed($BannerImage);
		}
		return $arrImageSize;
	}
	
	/**
	 * Get the size of an external image
	 * 
	 * @param string $BannerImage	Image link
	 * @return	mixed	$array / false
	 */
	protected function getImageSizeExternal($BannerImage)
	{
		$token = md5(uniqid(rand(), true));
		$tmpImage = 'system/tmp/mod_banner_fe_'.$token.'.tmp';
		$objRequest = new \Request();
		$objRequest->send(html_entity_decode($BannerImage, ENT_NOQUOTES, 'UTF-8'));
		//TODO: Test auf chunked
		try
		{
		    $objFile = new \File($tmpImage);
		    $objFile->write($objRequest->response);
		    $objFile->close();
		}
		// Temp directory not writeable
		catch (\Exception $e)
		{
		    if ($e->getCode() == 0)
		    {
		        log_message('[getImageSizeExternal] tmpFile Problem: notWriteable', 'debug.log');
		    } else {
		        log_message('[getImageSizeExternal] tmpFile Problem: error', 'debug.log');
		    }
		    return false;
		} 
		$objRequest=null;
		unset($objRequest);
		$arrImageSize = $this->getImageSizeInternal($tmpImage);
		
		if ($arrImageSize === false) //Workaround fuer PHP ohne zlib bei SWC Files 
		{		    
		    $arrImageSize = $this->getImageSizeCompressed($tmpImage);
		}
		$objFile->delete();
		$objFile=null;
		unset($objFile);

		return $arrImageSize;
	}
	
	/**
	 * getimagesize without zlib doesn't work
	 * workaround for this
	 * 
	 * @param	string	$BannerImage	Image link
	 * @return	mixed	$array / false
	 */
	protected function getImageSizeCompressed($BannerImage)
	{
		//TODO: coding
		return false;
	}
}

