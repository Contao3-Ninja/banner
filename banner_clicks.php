<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * >>>>>> Copy this script to TL_ROOT, when file not exist!
 * >>>>>> (only for manual installation)
 *
 * Supported GET parameters:
 * - bid:   Banner ID
 *
 * Usage example:
 * <a href="banner_clicks.php?bid=7">
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer 
 * @package    Banner 
 * @license    GPL 
 * @filesource
 */


/**
 * Initialize the system
 */
define('TL_MODE', 'FE');
require('system/initialize.php');

if (!file_exists(TL_ROOT . '/system/modules/botdetection/ModuleBotDetection.php'))
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
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer
 * @package    Banner
 */
class BannerClicks extends ModuleBotDetection
{
	/**
	 * Banner ID
	 * @var int
	 */
	protected $intBID;
	protected $intDEFBID;


	/**
	 * Set the current file
	 */
	public function __construct()
	{
		parent::__construct();
		$this->intBID    = preg_replace('@\D@', '', $this->Input->get('bid'));    //  only digits
		$this->intDEFBID = preg_replace('@\D@', '', $this->Input->get('defbid')); //  only digits
		$this->import('Database');
	}


	/**
	 * Get URL, Count Click, ReDirect
	 */
	public function run()
	{
		// Input a digit >0 ?
		if ( 0==(int)$this->intBID )
		{
		    if ( 0==(int)$this->intDEFBID ) {
		    	die('Invalid Banner ID');
		    }
		}

		//Banner oder Kategorie Banner (Default Banner)
		if ( 0<(int)$this->intBID ) {
			//normaler Banner
			$banner_not_viewed = false;
    		// Check whether the Banner ID exists
    		$objBanners = $this->Database->prepare("SELECT tb.id, tb.banner_url, tb.banner_jumpTo, tbs.banner_clicks"
    		                                     . " FROM tl_banner tb, tl_banner_stat tbs"
                                                 . " WHERE tb.id=tbs.id AND tb.id=?")
    					                 ->execute($this->intBID);
    		if (!$objBanners->next()) 
    		{
    			$objBanners = $this->Database->prepare("SELECT tb.id, tb.banner_url, tb.banner_jumpTo"
    		                                     . " FROM tl_banner tb"
                                                 . " WHERE tb.id=?")
    					           ->execute($this->intBID);
	    		if (!$objBanners->next()) {
	    			header('HTTP/1.1 501 Not Implemented');
	    			die('Banner ID not found');
	    		} else {
	    			$banner_not_viewed = true;
	    		}
    		}
    		
			$banner_stat_update = false;
		    if ($this->CheckUserAgent() === false 
		     && $this->BannerCheckBot() === false 
		     && $this->BannerReClickBlocker() === false) { 
		    	// keine User Agent Filterung
		    	// kein Bot
		    	// kein ReClick 
		    	$banner_stat_update = true;
		    }
		    
		    if ($banner_stat_update === true) {
                if ($banner_not_viewed === false) {
                	//Update
                	$tstamp = time();
	                $banner_clicks = $objBanners->banner_clicks + 1;
	                $this->Database->prepare("UPDATE tl_banner_stat SET tstamp=?, banner_clicks=? WHERE id=?")
	    						   ->executeUncached($tstamp, $banner_clicks, $this->intBID);
    			} else {
    				//Insert
    				 $arrSet = array
		            (
		                'id'     => $this->intBID,
		                'tstamp' => time(),
		                'banner_clicks' => 1
		            );
				    $this->Database->prepare("INSERT INTO tl_banner_stat %s")->set($arrSet)->execute();
    			}
		    }
    		
    		//Banner Ziel per Page?
            if ($objBanners->banner_jumpTo >0) {
            	//url generieren
            	$objBannerNextPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
                                                    ->limit(1)
                                                    ->execute($objBanners->banner_jumpTo);
            	if ($objBannerNextPage->numRows)
            	{
            		$objBanners->banner_url = $this->generateFrontendUrl($objBannerNextPage->fetchAssoc());
            	} 
            }
            $banner_redirect = $this->BannerRedirectType($this->intBID);
        } else {
            // Default Banner from Category
            // Check whether the Banner ID exists
    		$objBanners = $this->Database->prepare("SELECT id, banner_default_url as banner_url"
    		                                    . " FROM tl_banner_category"
                                                . " WHERE id=?")
    					                 ->execute($this->intDEFBID);
            if (!$objBanners->next()) 
            {
            	die('Default Banner ID not found');
            }
            $banner_redirect = '303';
        }
        /**
         * TL codiert = und # (ev. auch mehr) um in &#61; / &#35;
         * Links mit Parametern werden so ungueltig.
         * Daher wieder decodieren.
         */
		$banner_url = html_entity_decode($objBanners->banner_url, ENT_NOQUOTES, 'UTF-8');
		//header('HTTP/1.1 301 Moved Permanently');
		//header('HTTP/1.1 302 Found');
		//header('HTTP/1.1 303 See Other');
		//header('Location: ' . str_replace('&amp;', '&', $banner_url));
		$this->redirect($banner_url, $banner_redirect); // TL hat 301-303
	}

	/**
	 * Search Banner Redirect Definition
	 *
	 * @return int	301,302
	 * 
	 */
	protected function BannerRedirectType($BID)
	{
		// aus BID die KatID
		// über KatID in tl_module.banner_categories die tl_module.banner_redirect
		// schleife über alle zeilen, falls mehrere
		$objKat = $this->Database->prepare("SELECT pid as KatID FROM `tl_banner` where id=?")
	                   ->execute($BID);
	    if (0 == $objKat->numRows) {
	    	return '301'; // error, but the show must go on
	    }
	    $objKat->next();
	    $objBRT = $this->Database->prepare("SELECT `banner_categories`,`banner_redirect` FROM `tl_module` WHERE type=?")
	                             ->execute('Banner');
		if (0 == $objBRT->numRows) {
	    	return '301'; // error, but the show must go on
	    }
	    $arrBRT = array();
	    while ($objBRT->next()) {
	    	if ( $objKat->KatID == (int)$objBRT->banner_categories ) { //int dient als Schutz vor nicht durchgefuehrten Migrationen
	    		$arrBRT[] = ($objBRT->banner_redirect == 'temporary') ? '302' : '301';
	    	}
	    }
	    if (count($arrBRT) == 1) {
	    	return $arrBRT[0];	// Nur ein Modul importiert, eindeutig
	    } else {
	    	$anz301=$anz302=0;
			foreach ($arrBRT as $type) {	// mindestens 2 mal importiert, zaehlen
				if ($type=='301') {
					$anz301++;
				} else {
					$anz302++;
				}
			}
			if ($anz301 >= $anz302) {		// 301 hat bei Gleichstand Vorrang
				return '301';
			} else {
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
	protected function BannerReClickBlocker()
	{
	    $ClientIP = bin2hex(sha1($this->Environment->remoteAddr,true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    $BannerID = $this->intBID;
	    $BannerBlockTime = time() - 60*5;    // 5 Minuten, 0-5 min wird geblockt
	    $BannerCleanTime = time() - 60*60*1; // 1 Stunde , Einträge >= 1 Stunde werden gelöscht
	    
	    $this->Database->prepare("DELETE FROM tl_banner_blocker WHERE tstamp<? AND type=?")
	                   ->executeUncached($BannerCleanTime, 'c');
	    
	    $objBanners = $this->Database->prepare("SELECT id FROM tl_banner_blocker WHERE bid=? AND tstamp>? AND ip=? AND type=?")
								 	 ->limit(1)
									 ->execute( $BannerID, $BannerBlockTime, $ClientIP, 'c' );
		if (0 == $objBanners->numRows) {
		    // noch kein Eintrag bzw. ausserhalb Blockzeit
		    $arrSet = array
            (
                'bid'    => $BannerID,
                'tstamp' => time(),
                'ip'     => $ClientIP,
                'type'   => 'c'
            );
		    $this->Database->prepare("INSERT INTO tl_banner_blocker %s")->set($arrSet)->execute();
		    return false; // nicht blocken
		} else {
			// Eintrag innerhalb der Blockzeit
			return true; // blocken
		}
	}
	
	/**
	 * Spider Bot Check
	 * @return true = found, false = not found
	 */
	protected function BannerCheckBot()
	{
	    if (isset($GLOBALS['TL_CONFIG']['mod_banner_bot_check']) && intval($GLOBALS['TL_CONFIG']['mod_banner_bot_check'])==0) {
	        //log_message('BannerCheckBot abgeschaltet','Banner.log');
	        return false; //Bot Suche abgeschaltet ueber localconfig.php
	    }
	    if ($this->BD_CheckBotAgent() || $this->BD_CheckBotIP()) {
	    	//log_message('BannerCheckBot True','Banner.log');
	    	return true;
	    }
	    //log_message('BannerCheckBot False','Banner.log');
	    return false;
	} //BannerCheckBot
	
	/**
	 * HTTP_USER_AGENT Special Check
	 */
	protected function CheckUserAgent()
	{
   	    if (isset($this->Environment->httpUserAgent)) { 
	        $UserAgent = trim($this->Environment->httpUserAgent); 
	    } else { 
	        return false; // Ohne Absender keine Suche
	    }
	    
	    $objUserAgent = $this->Database->prepare("SELECT `banner_useragent` FROM `tl_module` WHERE `banner_useragent` !=?")
	                                   ->limit(1)
	                                   ->execute('');
	    if (!$objUserAgent->next()) {
	    	return false; // keine Angaben im Modul
	    }   
	    $arrUserAgents = explode(",", $objUserAgent->banner_useragent);
	    if (strlen(trim($arrUserAgents[0])) == 0) {
	    	return false; // keine Angaben im Modul
	    }
	    array_walk($arrUserAgents, array('BannerClicks','bannerclick_array_trim_value'));  // trim der array values
        // grobe Suche
        $CheckUserAgent=str_replace($arrUserAgents, '#', $UserAgent);
        if ($UserAgent != $CheckUserAgent) { // es wurde ersetzt also was gefunden
        	//log_message('CheckUserAgent Click Filterung: Treffer!','Banner.log');
            return true;
        }
        return false; 
	} //CheckUserAgent
	
	static function bannerclick_array_trim_value(&$data) {
        $data = trim($data);
        return ;
    }
}

/**
 * Instantiate controller
 */
$objBannerClicks = new BannerClicks();
$objBannerClicks->run();

?>