<?php 

/**
 * Contao Open Source CMS, Copyright (C) 2005-2013 Leo Feyer
 *
 * Contao Module "Banner" - DCA Helper Class DCA_banner_category
 *
 * @copyright  Glen Langer 2012..2013 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    Banner
 * @license    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/banner
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Banner;


class DCA_banner_category extends \Backend
{
    public function labelCallback($arrRow)
    {
        $label_1 = $arrRow['title'];
        if (version_compare(VERSION, '2.99', '>'))
        {
            $version_warning = '';
        } else {
            $version_warning = '<br /><span style="color:#ff0000;">[ERROR: Banner-Module requires at least Contao 3.0]</span>';
        }

        $bpc = $GLOBALS['TL_LANG']['tl_banner_category']['banner_protected_catagory'];
        if ( !empty($arrRow['banner_protected']) && strlen($arrRow['banner_groups']) )
        {
            $label_2 = '<img height="16" width="14" alt="'.$bpc.'" title="'.$bpc.'" src="system/themes/default/images/protect_.gif" />';
        } else {
            $label_2 = '';
        }
        return $label_1 . ' ' . $label_2 . $version_warning;
    }
}