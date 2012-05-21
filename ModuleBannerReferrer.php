<?php
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Banner Referrer - Frontend
 *
 * PHP version 5
 * @copyright  Glen Langer 2009..2012
 * @author     Glen Langer 
 * @package    Banner 
 * @license    LGPL 
 * @filesource
 */


/**
 * Class ModuleBannerReferrer
 *
 * @copyright  Glen Langer 2012
 * @author     Glen Langer 
 * @package    Banner
 * @license    LGPL 
 */
class ModuleBannerReferrer	//extends Frontend
{
	/**
	 * Current version of the class.
	 */
	const VERSION           = '0.2';
	
    private $_http_referrer = '';
    
    private $_parse_result  = '';
    
    private $_referrer_DNS  = '';
    
    private $_vhost         = '';
    
    const REFERRER_UNKNOWN  = '-';
    
    const REFERRER_OWN      = 'o';
    
    const REFERRER_WRONG    = 'w';
    
    /**
	* Reset all properties
	*/
	protected function reset() 
	{
	    //NEVER TRUST USER INPUT
	    if (function_exists('filter_var'))	// Adjustment for hoster without the filter extension
	    {
	    	$this->_http_referrer  = isset($_SERVER['HTTP_REFERER']) ? filter_var($_SERVER['HTTP_REFERER'],  FILTER_SANITIZE_URL) : self::REFERRER_UNKNOWN ;
	    } 
	    else 
	    {
	    	$this->_http_referrer  = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : self::REFERRER_UNKNOWN ;
	    }
	    $this->_referrer_DNS = self::REFERRER_UNKNOWN;
	    if ($this->_http_referrer == '' || 
	        $this->_http_referrer == '-') 
	    {
	    	//ungueltiger Referrer
	    	$this->_referrer_DNS = self::REFERRER_WRONG;
	    }
	}
	
	public function checkReferrer($referrer='') 
	{
		$this->reset();
		if( $referrer != "" ) 
		{
			//NEVER TRUST USER INPUT
			if (function_exists('filter_var'))	// Adjustment for hoster without the filter extension
	    	{
				$this->_http_referrer = filter_var($referrer,  FILTER_SANITIZE_URL);
	    	} 
	    	else 
	    	{
	    		$this->_http_referrer = $referrer;
	    	}
		}
		if ($this->_http_referrer !== self::REFERRER_UNKNOWN && 
		    $this->_referrer_DNS  !== self::REFERRER_WRONG) 
		{ 
			$this->detect();
		}
	}
	
	protected function detect()
	{
	    $this->_referrer_DNS = parse_url( $this->_http_referrer, PHP_URL_HOST );
	    if ($this->_referrer_DNS === NULL) 
	    {
	    	//try this...
	    	$this->_referrer_DNS = @parse_url( 'http://'.$this->_http_referrer, PHP_URL_HOST );
	    	if ($this->_referrer_DNS === NULL || 
	    	    $this->_referrer_DNS === false) 
	    	{
	    		//wtf...
	    		$this->_referrer_DNS = self::REFERRER_WRONG; 
	    	}
	    }
	    $this->_vhost = parse_url( 'http://'.$this->vhost(), PHP_URL_HOST );
	    //ReferrerDNS = HostDomain ?
	    if ( $this->_referrer_DNS == $this->_vhost ) 
	    {
	    	$this->_referrer_DNS = self::REFERRER_OWN; 
	    }
	}
	
	/**
	 * Return the current URL without path or query string or protocol
	 * @return string
	 */
	protected function vhost()
	{
		$host = rtrim($_SERVER['HTTP_HOST']);
		if (empty($host))
		{
			$host = $_SERVER['SERVER_NAME'];
		}
		$host  = preg_replace('/[^A-Za-z0-9\[\]\.:-]/', '', $host);
		
		if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) 
		{
			$xhost = preg_replace('/[^A-Za-z0-9\[\]\.:-]/', '', rtrim($_SERVER['HTTP_X_FORWARDED_HOST'],'/'));
		} 
		else 
		{
			$xhost = '';
		}
		
		return (!empty($xhost) ? $xhost : $host) ;
	}
	
	/**
	 * Return the request URI 
	 * @return string
	 */
	protected function requestURI()
	{
		if (!empty($_SERVER['REQUEST_URI']))
		{
			return htmlspecialchars($_SERVER['REQUEST_URI']); 
		}
		else
		{
			return '';
		}
	}
    
	public function getReferrerDNS()  { return $this->_referrer_DNS;  }
	
	public function getReferrerFull() { return $this->_http_referrer; }
	
	public function getHost()  { return $this->_vhost; }
	
	public function __toString() 
	{
	    return "Referrer DNS : {$this->getReferrerDNS()}\n<br>" .
			   "Referrer full: {$this->getReferrerFull()}\n<br>".
			   "Server Host : {$this->getHost()}\n<br>";
	}
	
}


?>