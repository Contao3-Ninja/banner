<?php   
/**
 * Contao Open Source CMS, Copyright (C) 2005-2013 Leo Feyer
 *
 * Modul Banner - /config/runonce.php
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2015
 * @author     Glen Langer
 * @package    Banner
 * @license    LGPL
 */

/**
 * Class BannerRunonceJob
 *
 * @copyright  Glen Langer 2007..2015
 * @author     Glen Langer
 * @package    Banner
 * @license    LGPL
 */
class BannerRunonceJob extends Controller
{
	public function __construct()
	{
	    parent::__construct();
	    $this->import('Database');
	}
	public function run()
	{
		// delete old database.sql (on update of banner 2 to banner 3)
		if (is_file(TL_ROOT . '/system/modules/banner/config/database.sql'))
		{
		    $objFile = new File('system/modules/banner/config/database.sql');
		    $objFile->delete();
		    $objFile->close();
		    $objFile=null;
		    unset($objFile);
		}
		
		//Check for update to C3.2
		if ($this->Database->tableExists('tl_banner'))
		{
		    $arrFields = $this->Database->listFields('tl_banner');
		    $blnDone = false;
		    
		    //check for one table and field
		    foreach ($arrFields as $arrField)
		    {
		        if ($arrField['name'] == 'banner_image' && $arrField['type'] != 'varchar')
		        {
		            $blnDone = true;
		        }
		    }
		    // Run the version 3.2 update in two tables
		    if ($blnDone == false)
		    {
		        Database\Updater::convertSingleField('tl_banner', 'banner_image');
		        Database\Updater::convertSingleField('tl_banner_category', 'banner_default_image');
		    }
		    
		}
		
	}
}

$objBannerRunonceJob = new BannerRunonceJob();
$objBannerRunonceJob->run();

?>