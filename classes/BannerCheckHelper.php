<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * 
 * Modul Banner - Check Helper 
 * 
 * PHP version 5
 * @copyright  Glen Langer 2007..2015
 * @author     Glen Langer
 * @package    Banner
 * @license    LGPL
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Banner; 

/**
 * Class BannerCheckHelper
 *
 * @copyright  Glen Langer 2015
 * @author     Glen Langer
 * @package    Banner
 */
class BannerCheckHelper extends \System
{
   /**
    * Current object instance
    * @var object
    */
    protected static $instance = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Return the current object instance (Singleton)
     * @return BannerCheckHelper
     */
    public static function getInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new BannerCheckHelper();
        }
    
        return self::$instance;
    }

    /**
     * Hook: Check the required extensions and files for Banner
     *
     * @param string $strContent
     * @param string $strTemplate
     * @return string
     */
    public function checkExtensions($strContent, $strTemplate)
    {
        if ($strTemplate == 'be_main')
        {
            if (!is_array($_SESSION["TL_INFO"]))
            {
                $_SESSION["TL_INFO"] = array();
            }
    
            // required extensions
            $arrRequiredExtensions = array(
            		'Bot Detection' => 'botdetection'/*,
            		'xls_export'    => 'xls_export'*/
            );
    
            // check for required extensions
            foreach ($arrRequiredExtensions as $key => $val)
            {
                if (!in_array($val, $this->Config->getActiveModules()))
                {
                    $_SESSION["TL_INFO"] = array_merge($_SESSION["TL_INFO"], array($val => 'Please install the required extension <strong>' . $key . '</strong>'));
                }
                else
                {
                    if (is_array($_SESSION["TL_INFO"]) && key_exists($val, $_SESSION["TL_INFO"]))
                    {
                        unset($_SESSION["TL_INFO"][$val]);
                    }
                }
            }
    
        }
    
        return $strContent;
    } // checkExtension
    
} // class
