<?php   
/**
 * Contao Open Source CMS, Copyright (C) 2005-2013 Leo Feyer
 *
 * Modul Banner - /config/runonce.php
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2013
 * @author     Glen Langer
 * @package    Banner
 * @license    LGPL
 */

/**
 * Class BannerRunonceJob
 *
 * @copyright  Glen Langer 2007..2013
 * @author     Glen Langer
 * @package    Banner
 * @license    LGPL
 */
class BannerRunonceJob extends Controller
{
	public function __construct()
	{
	    parent::__construct();
	    //$this->import('Database');
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
	}
}

$objBannerRunonceJob = new BannerRunonceJob();
$objBannerRunonceJob->run();

?>