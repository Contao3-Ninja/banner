<?php 

/**
 * Contao Open Source CMS, Copyright (C) 2005-2013 Leo Feyer
 *
 * Contao Module "Banner" - DCA Helper Class DCA_module_banner
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

/**
 * DCA Helper Class DCA_module_banner
 *
 * @copyright  Glen Langer 2012..2013 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    Banner
 *
 */
class DCA_module_banner extends \Backend
{
    public function getBannerTemplates($dc)
    {
        return $this->getTemplateGroup('mod_banner_list_', $dc->activeRecord->pid);
    }
}