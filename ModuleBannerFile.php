<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Banner File - Backend
 * 
 * PHP version 5
 * @copyright  Glen Langer 2007..2010
 * @author     Glen Langer
 * @package    Banner
 * @license    GPL
 * @filesource
 */


/**
 * Class ModuleBannerFile
 *
 * @copyright  Glen Langer 2007..2010
 * @author     Glen Langer 
 * @package    Banner
 */
class ModuleBannerFile
{
    /**
     * Get DIRECTORY_SEPARATOR
     *
     * @var string
     */
    public static $dirsep    = DIRECTORY_SEPARATOR;
    
    /**
     * Set Windows Directory Separator '\', masked
     *
     * @var string
     */
    public static $dirsepwin = '\\';
    
	/**
	 * Get Path/file of the icon file
	 *
	 * @param string $file
	 * @return string
	 */
	public static function BannerIcon($file)
	{
   	    $ModuleBannerDirPath = realpath(dirname(__FILE__));
	    $ModuleBannerRelPath = substr($ModuleBannerDirPath, strlen(TL_ROOT)+1);
	    if (self::$dirsep == self::$dirsepwin) { //Windows is here...
            $ModuleBannerRelPath = str_replace(self::$dirsepwin, '/', $ModuleBannerRelPath);
        }
	    return $ModuleBannerRelPath.'/'.$file;
	}
	
	/**
	 * Get Path/file of the css file
	 *
	 * @param string $file
	 * @return string
	 */
	public static function BannerCss($file)
	{
   	    $ModuleBannerDirPath = realpath(dirname(__FILE__));
	    $ModuleBannerRelPath = substr($ModuleBannerDirPath, strlen(TL_ROOT)+1);
	    if (self::$dirsep == self::$dirsepwin) { //Windows is here...
            $ModuleBannerRelPath = str_replace(self::$dirsepwin, '/', $ModuleBannerRelPath);
        }
	    return $ModuleBannerRelPath.'/'.$file;
	}
}

?>