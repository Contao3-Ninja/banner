<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2013 Leo Feyer
 *
 *
 * Supported GET parameters:
 * - bid:   Banner ID
 *
 * Usage example:
 * <a href="system/modules/banner/public/conban_clicks.php?bid=7">
 *
 * @copyright	Glen Langer 2007..2013 <http://www.contao.glen-langer.de>
 * @author      Glen Langer (BugBuster)
 * @package     Banner
 * @license     LGPL
 * @filesource
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\BotDetection;

/**
 * Initialize the system
 */
define('TL_MODE', 'FE');
require('../../../initialize.php');

if (!file_exists(TL_ROOT . '/system/modules/botdetection/modules/ModuleBotDetection.php'))
{
	//botdetection Modul fehlt, Abbruch
	echo "<html><title>BotDetection extension required!</title>"
	   . "<body>"
	   . "<h1>BotDetection extension required!</h1>"
	   . "<h1>BotDetection Erweiterung fehlt!</h1>"
	   . "</body>"
	   . "</html>";
	exit();
}

/**
 * Class BannerClicks
 *
 * Banner ReDirect class
 * @copyright  Glen Langer 2007..2013
 * @author     Glen Langer
 * @package    Banner
 */
class BannerClicks extends \BugBuster\BotDetection\ModuleBotDetection
{
	/**
	 * Banner ID
	 * @var int
	 */
	protected $intBID;
	protected $intDEFBID;
	
	/**
	 * Session
	 *
	 * @var string
	 * @access private
	 */
	private $_session   = array();


	/**
	 * Set the current file
	 */
	public function __construct()
	{
		parent::__construct();
		$this->intBID    = \Input::get('bid');   
		$this->intDEFBID = \Input::get('defbid');
		//$this->import('Database');
	}


	/**
	 * Get URL, Count Click, ReDirect
	 */
	public function run()
	{
		// Input a digit >0 ?
		if ( 0 == (int)$this->intBID )
		{
		    if ( 0 == (int)$this->intDEFBID ) 
		    {
		        header('HTTP/1.1 501 Not Implemented');
		    	die('Invalid Banner ID (' . \Input::get('bid') . ')');
		    }
		}

		//Banner oder Kategorie Banner (Default Banner)
		if ( 0 < (int)$this->intBID ) 
		{
			//normaler Banner
			$banner_not_viewed = false;
    		// Check whether the Banner ID exists
    		$objBanners = \Database::getInstance()
                                ->prepare("SELECT 
                                                tb.id
                                              , tb.banner_url
                                              , tb.banner_jumpTo
                                              , tbs.banner_clicks
                                           FROM 
                                                tl_banner tb
                                              , tl_banner_stat tbs
                                           WHERE 
                                                tb.id=tbs.id 
                                           AND 
                                                tb.id=?")
                                ->execute($this->intBID);
    		
    		if (!$objBanners->next()) 
    		{
    			$objBanners = \Database::getInstance()
                                    ->prepare("SELECT 
                                                    tb.id
                                                  , tb.banner_url
                                                  , tb.banner_jumpTo
                                               FROM 
                                                    tl_banner tb
                                               WHERE 
                                                    tb.id=?")
                                    ->execute($this->intBID);
	    		
	    		if (!$objBanners->next()) 
	    		{
	    			header('HTTP/1.1 501 Not Implemented');
	    			die('Banner ID not found');
	    		} 
	    		else 
	    		{
	    			$banner_not_viewed = true;
	    		}
    		}
    		
			$banner_stat_update = false;
		    if ($this->checkUserAgent()       === false 
		     && $this->checkBot()             === false 
		     && $this->getSetReClickBlocker() === false
		       ) 
		    { 
		    	// keine User Agent Filterung
		    	// kein Bot
		    	// kein ReClick 
		    	$banner_stat_update = true;
		    }
		    
		    if ($banner_stat_update === true) 
		    {
                if ($banner_not_viewed === false) 
                {
                	//Update
                	$tstamp = time();
	                $banner_clicks = $objBanners->banner_clicks + 1;
	                \Database::getInstance()->prepare("UPDATE 
                                                            tl_banner_stat 
                                                       SET 
                                                            tstamp=?
                                                          , banner_clicks=? 
                                                       WHERE 
                                                            id=?")
                                            ->executeUncached($tstamp, $banner_clicks, $this->intBID);
    			} 
    			else 
    			{
    				//Insert
    				 $arrSet = array
		            (
		                'id'     => $this->intBID,
		                'tstamp' => time(),
		                'banner_clicks' => 1
		            );
				    \Database::getInstance()->prepare("INSERT IGNORE INTO tl_banner_stat %s")
                                            ->set($arrSet)
                                            ->execute();
    			}
		    }
    		
    		//Banner Ziel per Page?
            if ($objBanners->banner_jumpTo >0) 
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
                                            ->execute($objBanners->banner_jumpTo);
            	
            	if ($objBannerNextPage->numRows)
            	{
            		$objBanners->banner_url = $this->generateFrontendUrl($objBannerNextPage->fetchAssoc());
            	} 
            }
            $banner_redirect = $this->getRedirectType($this->intBID);
        } 
        else 
        {
            // Default Banner from Category
            // Check whether the Banner ID exists
    		$objBanners = \Database::getInstance()
                                ->prepare("SELECT 
                                                id
                                              , banner_default_url AS banner_url
                                           FROM 
                                                tl_banner_category
                                           WHERE 
                                                id=?")
                                ->execute($this->intDEFBID);
    		
            if (!$objBanners->next()) 
            {
                header('HTTP/1.1 501 Not Implemented');
            	die('Default Banner ID not found');
            }
            $banner_redirect = '303';
        }
        /**
         * Contao codiert = und # (ev. auch mehr) um in &#61; / &#35;
         * Links mit Parametern werden so ungueltig.
         * Daher wieder decodieren.
         */
		$banner_url = html_entity_decode($objBanners->banner_url, ENT_NOQUOTES, 'UTF-8');
		//header('HTTP/1.1 301 Moved Permanently');
		//header('HTTP/1.1 302 Found');
		//header('HTTP/1.1 303 See Other');
		//                 307 Temporary Redirect (ab Contao 3.1)
		//header('Location: ' . str_replace('&amp;', '&', $banner_url));
		$this->redirect($banner_url, $banner_redirect); 
	}

	/**
	 * Search Banner Redirect Definition
	 *
	 * @return int	301,302
	 * 
	 */
	protected function getRedirectType($BID)
	{
		// aus BID die CatID
		// über CatID in tl_module.banner_categories die tl_module.banner_redirect
		// Schleife über alle zeilen, falls mehrere
		$objCat = \Database::getInstance()->prepare("SELECT 
                                                        pid as CatID 
                                                     FROM 
                                                        `tl_banner` 
                                                     WHERE 
                                                        id=?")
                                          ->execute($BID);
		
	    if (0 == $objCat->numRows) 
	    {
	    	return '301'; // error, but the show must go on
	    }
	    $objCat->next();
	    $objBRT = \Database::getInstance()->prepare("SELECT 
                                                        `banner_categories`
                                                       ,`banner_redirect` 
                                                     FROM 
                                                        `tl_module` 
                                                     WHERE 
                                                        type=?
                                                     AND 
                                                        banner_categories=?")
                                          ->execute('banner', $objCat->CatID);
		if (0 == $objBRT->numRows) 
		{
	    	return '301'; // error, but the show must go on
	    }
	    $arrBRT = array();
	    while ($objBRT->next()) 
	    {
    		$arrBRT[] = ($objBRT->banner_redirect == 'temporary') ? '302' : '301';
	    }
	    if (count($arrBRT) == 1) 
	    {
	    	return $arrBRT[0];	// Nur ein Modul importiert, eindeutig
	    } 
	    else 
	    {
	        // mindestens 2 FE Module mit derselben Kategorie, zaehlen
	    	$anz301=$anz302=0;
			foreach ($arrBRT as $type) 
			{	
				if ($type=='301') 
				{
					$anz301++;
				} 
				else 
				{
					$anz302++;
				}
			}
			if ($anz301 >= $anz302) 
			{		// 301 hat bei Gleichstand Vorrang
				return '301';
			} 
			else 
			{
				return '302';
			}
	    }
	}
	
	/**
	 * ReClick Blocker
	 * 
	 * @return bool    false/true  =   no ban / ban
	 * 
	 */
	protected function getSetReClickBlocker()
	{
	    $ClientIP = bin2hex(sha1(\Environment::get('remoteAddr'),true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    $BannerID = $this->intBID;
	    $BannerBlockTime = time() - 60*5;    // 5 Minuten, 0-5 min wird geblockt
	    $BannerCleanTime = time() - 60*60*1; // 1 Stunde , Einträge >= 1 Stunde werden gelöscht
	    //TODO: #37: OK
	    /*
	    \Database::getInstance()->prepare("DELETE FROM 
                                                tl_banner_stat_blocker 
                                           WHERE 
                                                tstamp<? 
                                           AND 
                                                type=?")
                                ->executeUncached($BannerCleanTime, 'c');
	    
	    $objBanners = \Database::getInstance()->prepare("SELECT 
                                                            id 
                                                         FROM 
                                                            tl_banner_stat_blocker 
                                                         WHERE 
                                                            bid=? 
                                                         AND 
                                                            tstamp>? 
                                                         AND 
                                                            ip=? 
                                                         AND 
                                                            type=?")
                                            ->limit(1)
                                            ->execute( $BannerID, $BannerBlockTime, $ClientIP, 'c' );
		if (0 == $objBanners->numRows) 
		{
		    // noch kein Eintrag bzw. ausserhalb Blockzeit
		    $arrSet = array
            (
                'bid'    => $BannerID,
                'tstamp' => time(),
                'ip'     => $ClientIP,
                'type'   => 'c'
            );
		    \Database::getInstance()->prepare("INSERT INTO tl_banner_stat_blocker %s")
                                    ->set($arrSet)
                                    ->execute();
		    return false; // nicht blocken
		} 
		else 
		{
			// Eintrag innerhalb der Blockzeit
			return true; // blocken
		}
		*/
	    if ( $this->getReClickBlockerId($BannerID) === false )
	    {
	        // nichts geblockt, also blocken fürs den nächsten Aufruf
	        $this->setReClickBlockerId($BannerID);
	         
	        // kein ReClickBlocker block gefunden, Zaehlung erlaubt, nicht blocken
	        return false;
	    }
	    else
	    {
	        // Eintrag innerhalb der Blockzeit, blocken
	        return true;
	    }
	}
	
	/**
	 * Spider Bot Check
	 * @return true = found, false = not found
	 */
	protected function checkBot()
	{
	    if (isset($GLOBALS['TL_CONFIG']['mod_banner_bot_check']) 
	      && (int)$GLOBALS['TL_CONFIG']['mod_banner_bot_check'] == 0
	       ) 
	    {
	        //log_message('BannerCheckBot abgeschaltet','Banner.log');
	        return false; //Bot Suche abgeschaltet ueber localconfig.php
	    }
	    if ($this->BD_CheckBotAgent() || $this->BD_CheckBotIP()) 
	    {
	    	//log_message('BannerCheckBot True','Banner.log');
	    	return true;
	    }
	    //log_message('BannerCheckBot False','Banner.log');
	    return false;
	} //BannerCheckBot
	
	/**
	 * HTTP_USER_AGENT Special Check
	 */
	protected function checkUserAgent()
	{
   	    if ( \Environment::get('httpUserAgent') ) 
   	    { 
	        $UserAgent = trim( \Environment::get('httpUserAgent') ); 
	    } 
	    else 
	    { 
	        return false; // Ohne Absender keine Suche
	    }
	    
	    $objUserAgent = \Database::getInstance()->prepare("SELECT 
                                                                `banner_useragent` 
                                                           FROM 
                                                                `tl_module` 
                                                           WHERE 
                                                                `banner_useragent` !=?")
                                                ->limit(1)
                                                ->execute('');
	    if (!$objUserAgent->next()) 
	    {
	    	return false; // keine Angaben im Modul
	    }   
	    $arrUserAgents = explode(",", $objUserAgent->banner_useragent);
	    if (strlen(trim($arrUserAgents[0])) == 0) 
	    {
	    	return false; // keine Angaben im Modul
	    }
	    array_walk($arrUserAgents, array('self','bannerclick_array_trim_value'));  // trim der array values
        // grobe Suche
        $CheckUserAgent=str_replace($arrUserAgents, '#', $UserAgent);
        if ($UserAgent != $CheckUserAgent) 
        {   // es wurde ersetzt also was gefunden
        	//log_message('CheckUserAgent Click Filterung: Treffer!','Banner.log');
            return true;
        }
        return false; 
	} //CheckUserAgent
	public static function bannerclick_array_trim_value(&$data) 
	{
        $data = trim($data);
        return ;
    }
    

/*    _____               _             
     / ____|             (_)            
    | (___   ___  ___ ___ _  ___  _ __  
     \___ \ / _ \/ __/ __| |/ _ \| '_ \ 
     ____) |  __/\__ \__ \ | (_) | | | |
    |_____/ \___||___/___/_|\___/|_| |_|s
 */
    
    /**
     * Set ReClick Blocker, Set Banner ID and timestamp
     *
     * @param integer    $banner_id
     */
    protected function setReClickBlockerId($banner_id=0)
    {
        if ($banner_id==0) { return; }// keine Banner ID, nichts zu tun
        //das können mehrere sein!, mergen!
        $this->setSession('ReClickBlocker', array( $banner_id => time() ), true );
        return ;
    }
    
    
    /**
     * Get ReClick Blocker, Get Banner ID if the timestamp ....
     *
     * @param boolean    true if blocked | false
     */
    protected function getReClickBlockerId($banner_id=0)
    {
        $this->getSession('ReClickBlocker');
        if ( count($this->_session) )
        {
            reset($this->_session);
            while ( list($key, $val) = each($this->_session) )
            {
                if ( $key == $banner_id &&
                        $this->removeReClickBlockerId($key, $val) == true )
                {
                    // Key ist noch gültig und es muss daher geblockt werden
                    //log_message('getReClickBlockerId Banner ID:'.$key,'Banner.log');
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * ReClick Blocker, Remove old Banner ID
     *
     * @param  integer    $banner_id
     * @return boolean    true = Key is valid, it must be blocked | false = key is invalid
     */
    protected function removeReClickBlockerId($banner_id, $tstmap)
    {
        $BannerBlockTime = time() - 60*5;  // 5 Minuten, 0-5 min wird geblockt
        if ( isset($GLOBALS['TL_CONFIG']['mod_banner_block_time'] )
         && intval($GLOBALS['TL_CONFIG']['mod_banner_block_time'])>0
        )
        {
            $BannerBlockTime = time() - 60*1*intval($GLOBALS['TL_CONFIG']['mod_banner_block_time']);
        }
    
        if ( $tstmap >  $BannerBlockTime )
        {
            return true;
        }
        else
        {
            //wenn mehrere dann nur den Teil, nicht die ganze Session
            unset($this->_session[$banner_id]);
            //wenn Anzahl Banner in Session nun 0 dann Session loeschen
            if ( count($this->_session) == 0 )
            {
                //komplett löschen
                \Session::getInstance()->remove('ReClickBlocker');
            }
            else //sonst neu setzen
            {
                //gekuerzte Session neu setzen
                $this->setSession('ReClickBlocker', $this->_session , false );
            }
        }
        return false;
    }
    
    
    
    /**
     * Get session
     *
     * @param string   $session_name   e.g.: 'ReClickBlocker'
     * @return void
     * @access protected
     */
    protected function getSession( $session_name )
    {
        $this->_session = (array)\Session::getInstance()->get( $session_name );
    }
    
    /**
     * Set session
     *
     * @param string   $session_name   e.g.: 'ReClickBlocker'
     * @param array    $arrData        array('key' => array(Value1,Value2,...))
     * @return void
     * @access protected
     */
    protected function setSession( $session_name, $arrData, $merge = false )
    {
        if ($merge)
        {
            $this->_session = \Session::getInstance()->get( $session_name );
             
            // numerische Schlüssel werden neu numeriert, daher
            // geht nicht: array_merge($this->_session, $arrData)
            $merge_array = (array)$this->_session + $arrData;
            \Session::getInstance()->set( $session_name, $merge_array );
        }
        else
        {
            \Session::getInstance()->set( $session_name, $arrData );
        }
         
    }
    
}

/**
 * Instantiate controller
 */
$objBannerClicks = new \BugBuster\BotDetection\BannerClicks();
$objBannerClicks->run();

