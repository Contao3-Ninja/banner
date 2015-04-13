<?php
/**
 * Extension for Contao Open Source CMS, Copyright (C) 2005-2015 Leo Feyer
 *
 * Modul Banner Log - Frontend
 *
 * @copyright  Glen Langer 2015 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @licence    LGPL
 * @filesource
 * @package    Banner
 * @see	       https://github.com/BugBuster1701/banner
 */

namespace BugBuster\Banner;

/**
 * Class ModuleBannerLog
 *
 * @copyright  Glen Langer 2015 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Banner
 * @license    LGPL
 */
class ModuleBannerLog
{
    /**
     * Write in log file, if debug is enabled
     *
     * @param string    $method
     * @param integer   $line
     */
    public static function Writer($method,$line,$value)
    {
        if ($method == '## START ##') 
        {
            if (!isset($GLOBALS['banner']['debug']['first'])) 
            {
                if ((bool)$GLOBALS['banner']['debug']['tag']         ||
                    (bool)$GLOBALS['banner']['debug']['helper']      ||
                    (bool)$GLOBALS['banner']['debug']['image']       ||
                    (bool)$GLOBALS['banner']['debug']['referrer']     
                   )
                {
                    $arrUniqid = trimsplit('.', uniqid('c0n7a0',true) );
                    $GLOBALS['banner']['debug']['first'] = $arrUniqid[1];
                    log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['banner']['debug']['first'],$method,$line,$value),'banner_debug.log');
                    return ;
                }
                return ;
            }
            else
            {
                return ;
            }
        }
                
        $arrNamespace = trimsplit('::', $method);
        $arrClass =  trimsplit('\\', $arrNamespace[0]);
        $vclass = $arrClass[2]; // class that will write the log
        
        if (is_array($value))
        {
            $value = print_r($value,true);
        }
        
        switch ($vclass)
        {
            case "ModuleBannerTag":
                if ($GLOBALS['banner']['debug']['tag'])
                {
                    log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['banner']['debug']['first'],$vclass.'::'.$arrNamespace[1],$line,$value),'banner_debug.log');
                }
                break;
            case "BannerHelper":
                if ($GLOBALS['banner']['debug']['helper'])
                {
                    log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['banner']['debug']['first'],$vclass.'::'.$arrNamespace[1],$line,$value),'banner_debug.log');
                }
                break;
            case "BannerImage":
                if ($GLOBALS['banner']['debug']['image'])
                {
                    log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['banner']['debug']['first'],$vclass.'::'.$arrNamespace[1],$line,$value),'banner_debug.log');
                }
                break;
            case "BannerReferrer":
                if ($GLOBALS['banner']['debug']['referrer'])
                {
                    log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['banner']['debug']['first'],$vclass.'::'.$arrNamespace[1],$line,$value),'banner_debug.log');
                }
                break;
            default:
                log_message(sprintf('[%s] [%s] [%s] %s',$GLOBALS['banner']['debug']['first'],$method,$line,'('.$vclass.')'.$value),'banner_debug.log');
                break;
        }
        return ;
    }
}
