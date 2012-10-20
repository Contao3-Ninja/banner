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
 * @license    LGPL
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Banner;

/** 
 * Class BannerImage
 *
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer 
 * @package    Banner
 * @license    LGPL
 */
class BannerImage extends \Frontend
{
	/**
	 * Current version of the class.
	 * @var string
	 */
	const BANNER_IMAGE_VERSION = '3.0.0';
	
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
	
	/**
	 * Get the size of an image
	 *
	 * @param	string	$BannerImage	Image path/link
	 * @param	string	$BannerType		intern,extern,text
	 * @return	mixed	$array / false
	 */
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
		    //Workaround for PHP without zlib on SWC files
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
		//old: Test auf chunked, nicht noetig solange Contao bei HTTP/1.0 bleibt
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
		    } 
		    else 
		    {
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
		$objFile = null;
		unset($objFile);

		return $arrImageSize;
	}
	
	/**
	 * getimagesize without zlib doesn't work
	 * workaround for this
	 * 
	 * @param	string	$BannerImage	Image 
	 * @return	mixed	$array / false
	 */
	protected function getImageSizeCompressed($BannerImage)
	{
		$arrImageSize = false;
		$res = $this->swc_data($BannerImage);
		if ($res) 
		{
			// width,height
			$arrImageSize = array($res[0], $res[1], 13); // 13 = SWC
		}
		return $arrImageSize; 
	}
	
	/**
	 * Uncompress swc files (zip-like swf)
	 * 
	 * @param string $filename
	 * @return boolean|array	false|$width,$height
	 */
	private function swc_data($filename) 
	{
	    $size   = 0;
	    $width  = 0;
	    $height = 0;
	
	    $file = @fopen(TL_ROOT . '/' . $filename,"rb");
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
	    $buffer = gzuncompress(gzread($file,$size),$size);
	    $buffer = substr($buffer,0,20); // first 20 Byte enough
	
	    $b = ord(substr($buffer,0,1));
	    $buffer = substr($buffer,1);
	    $cbyte 	= $b;
	    $bits 	= $b>>3;
	
	    $cval 	= "";
	    $cbyte &= 7;
	    $cbyte<<= 5;
	    $cbit 	= 2;
	    // RECT
	    for ($vals=0;$vals<4;$vals++) 
	    {
	        $bitcount = 0;
	        while ($bitcount<$bits) 
	        {
	            if ($cbyte&128) 
	            {
	                $cval .= "1";
	            } 
	            else 
	            {
	                $cval .= "0";
	            }
	            $cbyte<<=1;
	            $cbyte &= 255;
	            $cbit-- ;
	            $bitcount++ ;
	            if ($cbit<0) 
	            {
	                $cbyte	= ord(substr($buffer,0,1));
	                $buffer = substr($buffer,1);
	                $cbit   = 7;
	            }
	        }
	        $c 	  = 1;
	        $val  = 0;
	        $tval = strrev($cval);
	        for ($n=0;$n<strlen($tval);$n++) 
	        {
	            $atom = substr($tval,$n,1);
	            if ($atom=="1") $val+=$c;
	            $c*=2;
	        }
	        // TWIPS to PIXELS
	        $val/=20 ;
	        switch ($vals) 
	        {
	            case 0:
	                // tmp value
	                $width = $val;
	                break;
	            case 1:
	                $width = $val - $width;
	                break;
	            case 2:
	                // tmp value
	                $height = $val;
	                break;
	            case 3:
	                $height = $val - $height;
	                break ;
	        }
	        $cval = "";
	    }
	    fclose($file);
	    $buffer ='';
	    return array($width,$height);
	}//swc_data
	
	/**
	 * Calculate the new size for witdh and height
	 * 
	 * @param int 		$oldWidth	,mandatory
	 * @param int 		$oldHeight	,mandatory
	 * @param int 		$newWidth	,optional
	 * @param int 		$newHeight	,optional
	 * @return array	$Width,$Height,$oriSize
	 */
	public function getBannerImageSizeNew($oldWidth,$oldHeight,$newWidth=0,$newHeight=0)
	{
		$Width   = $oldWidth;  //Default, and flash require this
		$Height  = $oldHeight; //Default, and flash require this
		$oriSize = true;       //Attribute for images without conversion
		
		if ($newWidth > 0 && $newHeight > 0) 
		{
			$Width   = $newWidth;
			$Height  = $newHeight;
			$oriSize = false;
		}
		elseif ($newWidth > 0)
		{
			$Width   = $newWidth;
			$Height  = ceil($newWidth * $oldHeight / $oldWidth);
			$oriSize = false;
		}
		elseif ($newHeight > 0)
		{
			$Width   = ceil($newHeight * $oldWidth / $oldHeight);
			$Height  = $newHeight;
			$oriSize = false;
		}
		return array($Width,$Height,$oriSize);
	}
}

