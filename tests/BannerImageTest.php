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
        		//jpg
        		array( array('0'=>'192','1'=>'54','2'=>'2','3'=>'width="192" height="54"','bits'=>8,'channels'=>3,'mime'=>'image/jpeg')
        		          , 'system/modules/banner/tests/images/bugbuster-phpunit.jpg' , 'banner_image' ),
        		//png
        		array( array('0'=>'192','1'=>'54','2'=>'3','3'=>'width="192" height="54"','bits'=>8,'mime'=>'image/png')
        		          , 'system/modules/banner/tests/images/bugbuster-phpunit.png' , 'banner_image' ),
        		//gif
        		array( array('0'=>'192','1'=>'54','2'=>'1','3'=>'width="192" height="54"','bits'=>8,'channels'=>3,'mime'=>'image/gif')
        		          , 'system/modules/banner/tests/images/bugbuster-phpunit.gif' , 'banner_image' ),
        		//false test
        		array(false, 'system/modules/banner/tests/images/non_existent.jpg'     , 'banner_image' ),

        		//jpg extern
        		array( array('0'=>'192','1'=>'54','2'=>'2','3'=>'width="192" height="54"','bits'=>8,'channels'=>3,'mime'=>'image/jpeg')
        			      , 'http://phpunit.glen-langer.de/banner/bugbuster-phpunit.jpg'  , 'banner_image_extern' ),
        		//png extern
        		array( array('0'=>'192','1'=>'54','2'=>'3','3'=>'width="192" height="54"','bits'=>8,'mime'=>'image/png')
        		          , 'http://phpunit.glen-langer.de/banner/bugbuster-phpunit.png'  , 'banner_image_extern' ),
        		//gif
        		array( array('0'=>'192','1'=>'54','2'=>'1','3'=>'width="192" height="54"','bits'=>8,'channels'=>3,'mime'=>'image/gif')
        		          , 'http://phpunit.glen-langer.de/banner/bugbuster-phpunit.gif'  , 'banner_image_extern' ),
        		
				//false test extern
        		array(false, 'http://phpunit.glen-langer.de/banner/non_existent.jpg'      , 'banner_image_extern' ),
        		
        		//false test with text banner
        		array(false, 'Textbanner kann keine Pixel Groesse haben'      , 'banner_text'),
        		
		        //flash swc (zip-like swf file)
        		array( array('0'=>'160','1'=>'40','2'=>'13','3'=>'width="160" height="40"','mime'=>'application/x-shockwave-flash')
        		        , 'system/modules/banner/tests/flash/bugbuster-phpunit.swc.swf' , 'banner_image' ),
        		//flash swf
        		array( array('0'=>'234','1'=>'60','2'=>'4' ,'3'=>'width="234" height="60"','mime'=>'application/x-shockwave-flash')
        		        , 'system/modules/banner/tests/flash/bugbuster-phpunit.swf.swf' , 'banner_image' )
        		);
    }
    
  
}
?>