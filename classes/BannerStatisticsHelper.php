<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2012 Leo Feyer
*
* Module BannerStatistics 
* Helper class
*
* @copyright  Glen Langer 2013 <http://www.contao.glen-langer.de>
* @author     Glen Langer (BugBuster)
* @package    BannerStatistics
* @license    LGPL
* @filesource
* @see        https://github.com/BugBuster1701/banner
*/

/**
 * Run in a custom namespace, so the class can be replaced
*/
namespace BugBuster\BannerStatistics;

/**
 * Class BotStatisticsHelper
 *
 * @copyright  Glen Langer 2012 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    BannerStatisticsHelper
 */
class BannerStatisticsHelper extends \BackendModule
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
     * Current object instance
     * @var object
     */
    protected static $instance = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->import('BackendUser', 'User');
        parent::__construct();
    }


    protected function compile()
    {

    }
    /**
     * Return the current object instance (Singleton)
     * @return BannerStatisticsHelper
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new BannerStatisticsHelper();
        }

        return self::$instance;
    }
    
    /**
     * Get min category id
     * 
     * @return number    CatID    0|min(pid)
     */
    protected function getCatID()
    {
        $objBannerCatID = \Database::getInstance()->prepare("SELECT 
                                                                MIN(pid) AS ID 
                                                             FROM 
                                                                tl_banner")
                                                  ->execute();
        $objBannerCatID->next();
        if ($objBannerCatID->ID === null)
        {
            return 0;
        }
        else
        {
            return $objBannerCatID->ID;
        }
    }
    
    /**
     * Get banners by category id
     * 
     * @param integer   $CatID
     * @return array    $arrBanners
     */
    protected function getBannersByCatID($CatID = 0)
    {
        $arrBanners = array();
        
        if ($CatID == -1)
        { // all Categories
            $objBanners = \Database::getInstance()
                            ->prepare("SELECT 
                                            tb.id
                                          , tb.banner_type
                                          , tb.banner_name
                                          , tb.banner_url
                                          , tb.banner_jumpTo
                                          , tb.banner_image
                                          , tb.banner_image_extern
                                          , tb.banner_weighting
                                          , tb.banner_start
                                          , tb.banner_stop
                                          , tb.banner_published
                                          , tb.banner_until
                                          , tb.banner_comment
                                          , tb.banner_views_until
                                          , tb.banner_clicks_until
                                          , tbs.banner_views
                                          , tbs.banner_clicks
                                       FROM 
                                            tl_banner tb
                                          , tl_banner_stat tbs
                                       WHERE 
                                            tb.id=tbs.id
                                       ORDER BY 
                                            tb.pid
                                          , tb.banner_name")
                            ->execute();
        }
        else
        {
            $objBanners = \Database::getInstance()
                            ->prepare("SELECT
                                            tb.id
                                          , tb.banner_type
                                          , tb.banner_name
                                          , tb.banner_url
                                          , tb.banner_jumpTo
                                          , tb.banner_image
                                          , tb.banner_image_extern
                                          , tb.banner_weighting
                                          , tb.banner_start
                                          , tb.banner_stop
                                          , tb.banner_published
                                          , tb.banner_until
                                          , tb.banner_comment
                                          , tb.banner_views_until
                                          , tb.banner_clicks_until
                                          , tbs.banner_views
                                          , tbs.banner_clicks
                                       FROM
                                            tl_banner tb
                                          , tl_banner_stat tbs
                                       WHERE
                                            tb.id=tbs.id
                                       AND
                                            tb.pid =?
                                       ORDER BY
                                            tb.sorting")
                            ->execute($CatID);
        }
        $intRows = $objBanners->numRows;
        if ($intRows > 0)
        {
            while ($objBanners->next())
            {
                $arrBanners[] = array('id'                  => $objBanners->id
                                    , 'banner_type'         => $objBanners->banner_type
                                    , 'banner_name'         => $objBanners->banner_name
                                    , 'banner_url'          => $objBanners->banner_url
                                    , 'banner_jumpTo'       => $objBanners->banner_jumpTo
                                    , 'banner_image'        => $objBanners->banner_image
                                    , 'banner_image_extern' => $objBanners->banner_image_extern
                                    , 'banner_weighting'    => $objBanners->banner_weighting
                                    , 'banner_start'        => $objBanners->banner_start
                                    , 'banner_stop'         => $objBanners->banner_stop
                                    , 'banner_published'    => $objBanners->banner_published
                                    , 'banner_until'        => $objBanners->banner_until
                                    , 'banner_comment'      => $objBanners->banner_comment
                                    , 'banner_views_until'  => $objBanners->banner_views_until
                                    , 'banner_clicks_until' => $objBanners->banner_clicks_until
                                    , 'banner_views'        => $objBanners->banner_views
                                    , 'banner_clicks'       => $objBanners->banner_clicks                        
                                     );
            } // while
        }
        
        return $arrBanners;
    } // getBannersByCatID
    
    /**
     * Get banner categories
     * 
     * @param     integer    $banner_number
     * @return    array      $arrBannerCats
     */
    protected function getBannerCategories($banner_number)
    {
        // Kat sammeln
        $objBannerCat = \Database::getInstance()
                            ->prepare("SELECT 
                                            id 
                                          , title 
                                       FROM 
                                            tl_banner_category 
                                        WHERE 
                                            id 
                                        IN 
                                            (SELECT 
                                                 pid
                                             FROM
                                                 tl_banner
                                             LEFT JOIN
                                                 tl_banner_category 
                                             ON 
                                                 tl_banner.pid = tl_banner_category.id
                                             GROUP BY 
                                                 tl_banner.pid
                                             )
                                        ORDER BY 
                                            title")
                            ->execute();
        
        if ($objBannerCat->numRows > 0)
        {
            if ($banner_number == 0)
            { // gewählte Kategorie hat keine Banner, es gibt aber weitere Kategorien
                $arrBannerCats[] = array
                (
                    'id'    => '0',
                    'title' => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['select']
                );
                $this->intCatID = 0; // template soll nichts anzeigen
            }
            $arrBannerCats[] = array
            (
                'id'    => '-1',
                'title' => $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['allkat']
            );
            while ($objBannerCat->next())
            {
                $arrBannerCats[] = array
                (
                    'id'    => $objBannerCat->id,
                    'title' => $objBannerCat->title
                );
            }
        }
        else
        { // es gibt keine Kategorie mit Banner
            $arrBannerCats[] = array
            (
                'id'    => '0',
                'title' => '---------'
            );
        }
        return $arrBannerCats;
        
    } // getBannerCategories
    
    /**
     * Set banner_url
     * 
     * @param referenz    $Banner
     */
    protected function setBannerURL( &$Banner )
    {
        //Banner Ziel per Page?
        if ($Banner['banner_jumpTo'] > 0)
        {
            //url generieren
            $objBannerNextPage = \Database::getInstance()
                                    ->prepare("SELECT 
                                                    id
                                                  , alias 
                                               FROM 
                                                    tl_page 
                                               WHERE 
                                                    id=?")
                                    ->limit(1)
                                    ->execute($Banner['banner_jumpTo']);
            if ($objBannerNextPage->numRows)
            {
                $Banner['banner_url'] = $this->generateFrontendUrl($objBannerNextPage->fetchAssoc());
            }
        }
        if ($Banner['banner_url'] == '')
        {
            $Banner['banner_url'] = $GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['NoURL'];
            if ($Banner['banner_clicks'] == 0)
            {
                $Banner['banner_clicks'] = '--';
            }
        }
    }
    
    /**
     * Set banner_published
     *
     * @param referenz    $Banner
     */
    protected function setBannerPublished( &$Banner )
    {
        if ( ($Banner['banner_published'] == 1) 
           &&  ($Banner['banner_start'] == '' || $Banner['banner_start'] <= time() ) 
           &&  ($Banner['banner_stop']  == '' || $Banner['banner_stop']   > time() )
           )
        {
            $Banner['banner_published'] = '<span class="banner_stat_yes">'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_yes'].'</span>';
            
            if ($Banner['banner_until'] == 1 
             && $Banner['banner_views_until'] != '' 
             && $Banner['banner_views'] >= $Banner['banner_views_until']
               )
            {
                //max views erreicht
                $Banner['banner_published'] = '<span class="banner_stat_no">'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_no'].'</span>';
            }
            
            if ($Banner['banner_until'] == 1 
             && $Banner['banner_clicks_until'] !='' 
             && $Banner['banner_clicks'] >= $Banner['banner_clicks_until']
               )
            {
                //max clicks erreicht
                $Banner['banner_published'] = '<span class="banner_stat_no">'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_no'].'</span>';
            }
        }
        else
        {
            $Banner['banner_published'] = '<span class="banner_stat_no">'.$GLOBALS['TL_LANG']['MSC']['tl_banner_stat']['pub_no'].'</span>';
        }
    }
    
    /**
     * Get status of maxviews and maxclicks
     * 
     * @param    array    $Banner
     * @return   array    array(bool $intMaxViews, bool $intMaxClicks)
     */
    protected function getMaxViewsClicksStatus( &$Banner )
    {
        $intMaxViews = false;
        $intMaxClicks= false;
        
        if ($Banner['banner_until'] == 1
         && $Banner['banner_views_until'] != ''
         && $Banner['banner_views'] >= $Banner['banner_views_until']
           )
        {
            //max views erreicht
            $intMaxViews =  true;
        }
        
        if ($Banner['banner_until'] == 1
         && $Banner['banner_clicks_until'] !=''
         && $Banner['banner_clicks'] >= $Banner['banner_clicks_until']
           )
        {
            //max clicks erreicht
            $intMaxClicks = true;
        }
        
        return array($intMaxViews,$intMaxClicks);
    }
    
} // class
