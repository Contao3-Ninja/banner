<?php 

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *
 * Modul Banner - PHPUnit Class BannerImageTest
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer
 * @package    PHPUnitBanner
 * @license    LGPL
 * @example    phpunit BannerImageTest
 * 
 *             PHPUnit 3.4.13 by Sebastian Bergmann.
 *             ......
 *             Time: 0 seconds, Memory: 6.25Mb
 *             OK (6 tests, 6 assertions)
 */

namespace BugBuster\Banner;

/**
 * Initialize the system
 */
define('TL_MODE', 'FE');
require(dirname(dirname(dirname(dirname(__FILE__)))).'/initialize.php');

/**
 * PHPUnit Framework
 */
require_once 'PHPUnit/Framework.php';

/**
 * Class for testing
 */
require_once TL_ROOT . '/system/modules/banner/classes/BannerImage.php';

/**
 * Test class for BannerImage.
 */
class BannerImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BannerImage
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BannerImage;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * Test for getVersion()
     */
    public function testGetVersion()
    {
        $this->assertEquals('3.0.0', $this->object->getVersion());
    }
    
    /**
     * Test for getBannerImageSize()
     * 
     * @dataProvider providerbannerimage 
     */
    public function testGetBannerImageSize($result,$BannerImage,$BannerType)
    {
		if ($result !== false) 
		{
			//Result must be equal
			$this->assertEquals($result,$this->object->getBannerImageSize($BannerImage,$BannerType));
		}
		else 
		{
			//Result must be false
			$this->assertFalse($this->object->getBannerImageSize($BannerImage,$BannerType));
		}
    }    
    public function providerbannerimage()
    {
        return array(
                array( array('0'=>'500','1'=>'350','2'=>'2','3'=>'width="500" height="350"','bits'=>8,'channels'=>3,'mime'=>'image/jpeg') 
                 	       , 'files/music_academy/campus/campus_building.jpg' , 'banner_image' ),
        		array(false, 'files/music_academy/campus/non_existent.jpg'    , 'banner_image' ),
        		array( array('0'=>'80','1'=>'15','2'=>'3','3'=>'width="80" height="15"','bits'=>8,'mime'=>'image/png')
        			       , 'http://www.glen-langer.de/tl_files/hacker.png'  , 'banner_image_extern' ),
        		array(false, 'http://www.glen-langer.de/non_existent.jpg'     , 'banner_image_extern' ),
        		array(false, 'Textbanner kann keine Pixel Groesse haben'      , 'banner_text')
        		//TODO: test with flash files
        		);
    }
    
  
}
?>