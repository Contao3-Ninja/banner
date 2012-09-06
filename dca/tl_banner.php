<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * 
 * Modul Banner - Backend DCA tl_banner
 * 
 * This is the data container array for table tl_banner.
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer
 * @package    Banner
 * @license    GPL
 */

/**
 * Class tl_banner
 *
 * Methods that are used by the DCA
 */
class tl_banner extends Backend
{
	/**
     * Import the back end user object
     */
    public function __construct()
    {
            parent::__construct();
            $this->import('BackendUser', 'User');
    }
    
	/**
	 * List banner record
	 */
	public function listBanner($row)
	{
	    if ('banner_image' == $row['banner_type']) {
	        //Interne Banner Grafik
	        $BannerSourceIntern = true;
	        $oriSize = false;

	        // Check for version 3 format
	        if (!is_numeric($row['banner_image']))
	        {
	            return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
	        }
	        //convert DB file ID into file path ($objFile->path)
	        $objFile = \FilesModel::findByPk($row['banner_image']);
	        
    	    $arrImageSize = @getimagesize(TL_ROOT . '/' . $objFile->path);
    	    if ($arrImageSize===false) {
    	    	//Workaround fuer PHP ohne zlib bei SWC Files
    	    	$arrImageSize = $this->getimagesizecompressed(TL_ROOT . '/' . $objFile->path);
    	    }
    	    //log_message('Objekt gefunden: '.print_r($arrImageSize,true).'', 'debug.log');
    	    // Groessen umrechnen wenn noetig
    	    switch ($arrImageSize[2]) {
                case 1:
                case 2:
                case 3:
    	        case 4:  // Flash swf
            	case 13: // Flash swc
            	   if ($arrImageSize[0] > $arrImageSize[1]) { // Breite > Hoehe
            		    if ($arrImageSize[0] >250) {
            		    	$intWidth  = 250;
            		    	$intHeight = ceil($intWidth*$arrImageSize[1]/$arrImageSize[0]);
            		    } else {
            		    	$intWidth  = $arrImageSize[0];
            		    	$intHeight = $arrImageSize[1];
            		    	$oriSize = true; // Merkmal fuer Bilder ohne Umrechnung
            		    }
                    } else { // Hoehe >= Breite, ggf. Hoehe verkleinern
        		        if ($arrImageSize[1]>250) {
        		            // pruefen ob bei neuer Hoehe die Breite zu klein wird
        		        	if ((250*$arrImageSize[0]/$arrImageSize[1]) < 40) {
        		        		// Breite statt Hoehe setzen
        		        		$intWidth  = 40;
        		        		$intHeight = ceil($intWidth*$arrImageSize[1]/$arrImageSize[0]);
        		        	} else {
        		        		$intHeight = 250;
        		        		$intWidth  = ceil($intHeight*$arrImageSize[0]/$arrImageSize[1]);
        		        	}
        		        } else {
        		            $intWidth  = $arrImageSize[0];
        		            $intHeight = $arrImageSize[1];
        		            $oriSize = true; // Merkmal fuer Bilder ohne Umrechnung
        		        }
        		    }
        		    //check for fallback image
        		    $fallback_content = '<br><span style="font-weight: normal;">'.$GLOBALS['TL_LANG']['tl_banner']['source_fallback_no'].'</span>';
        		    $path_parts = pathinfo($objFile->path);
        		    if (@getimagesize(TL_ROOT . '/' . $path_parts['dirname'].'/'.$path_parts['filename'].'.jpg') !== false) {
        		        $fallback_ext = '.jpg';
        		        $fallback_content = true;
        		    } elseif (@getimagesize(TL_ROOT . '/' . $path_parts['dirname'].'/'.$path_parts['filename'].'.png') !== false) {
        		        $fallback_ext = '.png';
        		        $fallback_content = true;
        		    } elseif (@getimagesize(TL_ROOT . '/' . $path_parts['dirname'].'/'.$path_parts['filename'].'.gif') !== false) {
        		        $fallback_ext = '.gif';
        		        $fallback_content = true;
        		    }
        		    if ($fallback_content === true) 
        		    {
        		        //Get Image with sizes of flash
        		        $src_fallback = Image::get($this->urlEncode($path_parts['dirname'].'/'.$path_parts['filename'].$fallback_ext), $intWidth, $intHeight,'proportional');
       		            $fallback_content = '<br><img src="' . $src_fallback . '" alt="'.specialchars(ampersand($row['banner_name'])).'" height="'.$intHeight.'" width="'.$intWidth.'"><br><span style="font-weight: normal;">'.$GLOBALS['TL_LANG']['tl_banner']['source_fallback'].'</span>';
        		    }
        		    break;
                default:
                    break;
        	}
    	    // $banner_image je nach Type
    	    switch ($arrImageSize[2]) {
                case 1:
                case 2:
                case 3:
                    if ($oriSize) {
                    	$banner_image = $this->urlEncode($objFile->path); 
                    } else {
                    	$banner_image = Image::get($this->urlEncode($objFile->path), $intWidth, $intHeight);
                    }
            	    break;
            	case 4:  // Flash swf
            	case 13: // Flash swc
        		    $banner_image = $objFile->path;
                    break;
                default:
                    break;
        	}
    	} 
    	if ('banner_image_extern' == $row['banner_type']) {
    	    // Externer Banner Grafik
    	    $BannerSourceIntern=false;
    	    $fallback_content = '';
    	    $arrImageSize = $this->getimagesizeexternal($row['banner_image_extern']);
    	    //log_message('[getimagesizeexternal] Image Details2: '.print_r($arrImageSize,true).'', 'debug.log');
    	    switch ($arrImageSize[2]) {
                case 1:
                case 2:
                case 3:
            	case 4:  // Flash swf
            	case 13: // Flash swc
            	    if ($arrImageSize[0] > $arrImageSize[1]) { // Breite > Hoehe
            		    if ($arrImageSize[0] >250) {
            		    	$intWidth  = 250;
            		    	$intHeight = ceil($intWidth*$arrImageSize[1]/$arrImageSize[0]);
            		    } else {
            		    	$intWidth  = $arrImageSize[0];
            		    	$intHeight = $arrImageSize[1];
            		    }
            	    } else { // Hoehe >= Breite, ggf. Hoehe verkleinern
            	        if ($arrImageSize[1]>250) {
            	            // pruefen ob bei neuer Hoehe die Breite zu klein wird
            	        	if ((250*$arrImageSize[0]/$arrImageSize[1]) < 40) {
            	        		// Breite statt Hoehe setzen
            	        		$intWidth  = 40;
            		    	    $intHeight = ceil($intWidth*$arrImageSize[1]/$arrImageSize[0]);
            	        	} else {
            	        	    $intHeight = 250;
        		        		$intWidth  = ceil($intHeight*$arrImageSize[0]/$arrImageSize[1]);
            	        	}
            	        } else {
        		            $intWidth  = $arrImageSize[0];
        		            $intHeight = $arrImageSize[1];
        		            $oriSize = true; // Merkmal fuer Bilder ohne Umrechnung
        		        }
            	    }
            	    //$banner_image = $this->urlEncode($row['banner_image_extern']);
            	    $banner_image = $row['banner_image_extern'];
            	    break;
                default:
                    break;
        	}
        	//log_message('[banner_image_extern] : '.$row['banner_image_extern'].'', 'debug.log');
        	//log_message('[banner_image]        : '.$banner_image.'', 'debug.log');
    	}
    	//Banner Ziel per Page?
        if ($row['banner_jumpTo'] >0) {
        	//url generieren
        	$objBannerNextPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
                                                ->limit(1)
                                                ->execute($row['banner_jumpTo']);
        	if ($objBannerNextPage->numRows)
        	{
        		$row['banner_url'] = $this->generateFrontendUrl($objBannerNextPage->fetchAssoc());
        	} 
        }
    	$banner_url = html_entity_decode($row['banner_url'], ENT_NOQUOTES, 'UTF-8');
    	if (strlen($banner_url)>0) {
    		$banner_url_text = $GLOBALS['TL_LANG']['tl_banner']['banner_url'][0].': ';
    	} else {
    	    $banner_url_text = '';
    	}
    	if ('banner_text' != $row['banner_type']) {
	    	switch ($arrImageSize[2]) {
	            case 1:
	            case 2:
	            case 3:
	        		$output = '<div class="mod_banner_be">' .
	                            '<div class="name"><img alt="'.specialchars(ampersand($row['banner_name'])).'" src="'. $banner_image .'" height="'.$intHeight.'" width="'.$intWidth.'" /></div>' .
	        					'<div class="right">' .
	                        	   '<div class="left">'.
	                        	     '<div class="published_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_published'][0].'</div>'.
	                                 '<div class="published_data">'.($row['banner_published'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_no'] : $GLOBALS['TL_LANG']['tl_banner']['tl_be_yes']).' </div>'.
	                               '</div>'.
	                               '<div class="left">' .
	        					     '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_type'][0].'</div>' .
	        					     '<div class="date_data">' . ($BannerSourceIntern === true ? $GLOBALS['TL_LANG']['tl_banner']['source_intern'] : $GLOBALS['TL_LANG']['tl_banner']['source_extern']) . '</div>' .
	        					   '</div>' .
	                               '<div style="clear:both;"></div>'.
	                               '<div class="left">' .
	        						 '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_start'].'</div>' .
	                                 '<div class="date_data">' . ($row['banner_start']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_start'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_start'])) . '</div>' .
	        					   '</div>' .
	        					   '<div class="left">' .
	        					     '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_stop'].'</div>' .
	        					     '<div class="date_data">' . ($row['banner_stop'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_stop'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_stop'])) . '</div>' .
	        					   '</div>' .
	                               '<div style="clear:both;"></div>'.
	                               '<div class="left">' .
	        						 '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_views'].'</div>' .
	                                 '<div class="date_data">' . ($row['banner_views_until']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_views_until']) . '</div>' .
	        					   '</div>' .
	        					   '<div class="left">' .
	        					     '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_clicks'].'</div>' .
	        					     '<div class="date_data">' . ($row['banner_clicks_until'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_clicks_until']) . '</div>' .
	        					   '</div>' .
	                               '<div style="clear:both;"></div>'.
	        				    '</div>' .
	                            '<div class="url">'.$banner_url_text . (strlen($banner_url)<80 ? $banner_url : substr($banner_url, 0, 36)."[...]".substr($banner_url,-36,36) ).'</div>' .
	        				  '</div>';
	                break;
	            case 4:  // Flash swf
	            case 13: // Flash swc
	        		$output = '<div class="mod_banner_be">' .
	                            '<div class="name"><div id="flash_'.$row['id'].'">'.specialchars(ampersand($row['banner_name'])).'</div>'.$fallback_content.'</div>' .
	        					'<div class="right">' .
	                        	   '<div class="left">'.
	                        	     '<div class="published_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_published'][0].'</div>'.
	                                 '<div class="published_data">'.($row['banner_published'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_no'] : $GLOBALS['TL_LANG']['tl_banner']['tl_be_yes']).' </div>'.
	                               '</div>'.
	                               '<div class="left">' .
	        					     '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_type'][0].'</div>' .
	        					     '<div class="date_data">' . ($BannerSourceIntern === true ? $GLOBALS['TL_LANG']['tl_banner']['source_intern'] : $GLOBALS['TL_LANG']['tl_banner']['source_extern']) . '</div>' .
	        					   '</div>' .
	                               '<div style="clear:both;"></div>'.
	                               '<div class="left">' .
	        						 '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_start'].'</div>' .
	                                 '<div class="date_data">' . ($row['banner_start']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_start'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_start'])) . '</div>' .
	        					   '</div>' .
	        					   '<div class="left">' .
	        					     '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_stop'].'</div>' .
	        					     '<div class="date_data">' . ($row['banner_stop'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_stop'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_stop'])) . '</div>' .
	        					   '</div>' .
	                               '<div style="clear:both;"></div>'.
	                               '<div class="left">' .
	        						 '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_views'].'</div>' .
	                                 '<div class="date_data">' . ($row['banner_views_until']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_views_until']) . '</div>' .
	        					   '</div>' .
	        					   '<div class="left">' .
	        					     '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_clicks'].'</div>' .
	        					     '<div class="date_data">' . ($row['banner_clicks_until'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_clicks_until']) . '</div>' .
	        					   '</div>' .
	                               '<div style="clear:both;"></div>'.
	        				    '</div>' .
	                            '<div class="url">'.$banner_url_text . (strlen($banner_url)<80 ? $banner_url : substr($banner_url, 0, 36)."[...]".substr($banner_url,-36,36) ). '</div>' .
	        				  '</div>'.
	        				   '<script type="text/javascript">
								/* <![CDATA[ */
								new Swiff("'.$banner_image.'", {
								  id: "flash_'.$row['id'].'",
								  width: '.$intWidth.',
								  height: '.$intHeight.',
								  params : {
								  allowfullscreen: "true",
								  wMode: "transparent",
								  flashvars: ""
								  }
								}).replaces($("flash_'.$row['id'].'"));
								/* ]]> */
								</script>';
	                break;
	            default:
	                break;
	    	}
	    	if ($arrImageSize === false) {
	    	    if ('banner_image' == $row['banner_type']) {
	    	        //Interne Banner Grafik
	                $output = '<div class="mod_banner_be">' .
	                '<div class="name"><span style="color:red;">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_read_error'].'</span><br />'.$this->urlEncode($row['banner_image']).'</div>' .
	                '</div>';
	    	    } else {
	    	    	//Externe Banner Grafik
	    	    	$output = '<div class="mod_banner_be">' .
	                '<div class="name"><span style="color:red;">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_read_error'].'</span><br />'.$row['banner_image_extern'].'</div>' .
	                '</div>';
	    	    }
	    	}
    	}
    	if ('banner_text' == $row['banner_type']) {
    		$output = '<div class="mod_banner_be">' .
	                    '<div class="name"><br />'.$row['banner_name'].'<br /><span style="font-weight:normal;">'.nl2br($row['banner_comment']).'<br />'.(strlen($banner_url)<60 ? $banner_url : substr($banner_url, 0, 31)."[...]".substr($banner_url,-21,21) ).'</span></div>' .
						'<div class="right">' .
	                	   '<div class="left">'.
	                	     '<div class="published_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_published'][0].'</div>'.
	                         '<div class="published_data">'.($row['banner_published'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_no'] : $GLOBALS['TL_LANG']['tl_banner']['tl_be_yes']).' </div>'.
	                       '</div>'.
	                       '<div class="left">' .
						     '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['banner_type'][0].'</div>' .
						     '<div class="date_data">'.$GLOBALS['TL_LANG']['tl_banner_type']['banner_text'].'</div>' .
						   '</div>' .
	                       '<div style="clear:both;"></div>'.
	                       '<div class="left">' .
							 '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_start'].'</div>' .
	                         '<div class="date_data">' . ($row['banner_start']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_start'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_start'])) . '</div>' .
						   '</div>' .
						   '<div class="left">' .
						     '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_stop'].'</div>' .
						     '<div class="date_data">' . ($row['banner_stop'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_stop'] : date($GLOBALS['TL_CONFIG']['datimFormat'], $row['banner_stop'])) . '</div>' .
						   '</div>' .
	                       '<div style="clear:both;"></div>'.
	                       '<div class="left">' .
							 '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_views'].'</div>' .
	                         '<div class="date_data">' . ($row['banner_views_until']=='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_views_until']) . '</div>' .
						   '</div>' .
						   '<div class="left">' .
						     '<div class="date_head">'.$GLOBALS['TL_LANG']['tl_banner']['tl_be_max_clicks'].'</div>' .
						     '<div class="date_data">' . ($row['banner_clicks_until'] =='' ? $GLOBALS['TL_LANG']['tl_banner']['tl_be_not_defined_max'] : $row['banner_clicks_until']) . '</div>' .
						   '</div>' .
	                       '<div style="clear:both;"></div>'.
					    '</div>' .
					  '</div>';
    	}
    	$key = $row['banner_published'] ? 'published' : 'unpublished';
		// Code für Versionen ab 2.9.0
		$style = 'style="font-size:11px;margin-bottom:10px;"';
    	$output_h = '<div class="cte_type ' . $key . '" ' . $style . '><strong>' . specialchars(ampersand($row['banner_name'])) . '</strong></div>';
		return $output_h . $output;
	}
	
	public function listBannerStat($row)
	{
	    return '<div class="name">'.$row['banner_name'].'</div>';
	}
	
	/**
	 * Get external banner image
	 *
	 * @param string $BannerImageExternal
	 * @return array
	 */
	public function getimagesizeexternal($BannerImageExternal) 
	{
	    //log_message('[getimagesizeexternal] Externe Banner Grafik gefunden', 'debug.log');
	    $token = md5(uniqid(rand(), true));
	    $tmpImage = 'system/tmp/mod_banner_'.$token.'.tmp';
	    $objRequest = new Request();
		$objRequest->send(html_entity_decode($BannerImageExternal, ENT_NOQUOTES, 'UTF-8')); // decode: &#61; nach =
		//log_message('[getimagesizeexternal] Response Header: '.print_r($objRequest->headers,true).'', 'debug.log');
		// Test auf chunked
		if ( array_key_exists('Transfer-Encoding',$objRequest->headers) && $objRequest->headers['Transfer-Encoding'] == 'chunked') {
			try
			{ 
	    		$objFile = new File($tmpImage);
	    		$objFile->write($this->decodeChunked($objRequest->response));
	    		$objFile->close();
			}
			// Temp directory not writeable
			catch (Exception $e)
			{
				if ($e->getCode() == 0)
				{
					log_message('[getimagesizeexternal] tmpFile Problem: notWriteable', 'debug.log');
				} else {
				    log_message('[getimagesizeexternal] tmpFile Problem: error', 'debug.log');
				}
			}
		} else {
			try
			{ 
	    		$objFile = new File($tmpImage);
	    		$objFile->write($objRequest->response);
	    		$objFile->close();
			}
			// Temp directory not writeable
			catch (Exception $e)
			{
				if ($e->getCode() == 0)
				{
					log_message('[getimagesizeexternal] tmpFile Problem: notWriteable', 'debug.log');
				} else {
				    log_message('[getimagesizeexternal] tmpFile Problem: error', 'debug.log');
				}
			} 
		}
		$objRequest=null;
		unset($objRequest);
		$arrImageSize = @getimagesize(TL_ROOT . '/' . $tmpImage);
		//log_message('[getimagesizeexternal] Image Details: '.print_r($arrImageSize,true).'', 'debug.log');
		if ($arrImageSize===false) {
	    	//Workaround fuer PHP ohne zlib bei SWC Files
	    	$arrImageSize = $this->getimagesizecompressed(TL_ROOT . '/' . $tmpImage);
	    }
        //log_message('[getimagesizeexternal] Image Details: '.print_r($arrImageSize,true).'', 'debug.log');
		$objFile->delete();
		$objFile=null;
		unset($objFile);
				
		return $arrImageSize;	
	}
	
    private function decodeChunked($chunked)
    {
        $decBody = '';
        $m = '';
        while (trim($chunked)) {
            preg_match("/^([\da-fA-F]+)[^\r\n]*\r\n/sm", $chunked, $m);
            $length = hexdec(trim($m[1]));
            $cut = strlen($m[0]);

            $decBody .= substr($chunked, $cut, $length);
            $chunked = substr($chunked, $cut + $length + 2);
        }
        return $decBody;
    } 
    
	private function swc_data($filename) {
		$size = 0;
		$width = 0;
		$height = 0;
		
		$file = @fopen($filename,"rb") ;
		if (!$file) {
			return false;
		}
		if ("CWS" != fread($file,3)) {
			fclose($file);
			return false;
		} 
		// Version
		fread($file,1) ;
		for ($i=0;$i<4;$i++) {
			$t = ord(fread($file,1));
			$size += ($t<<(8*$i));
		}
		$buffer = gzuncompress(gzread($file,$size),$size) ;
		$buffer = substr($buffer,0,20) ; // first 20 Byte enough
		fclose($file) ;
		$b = ord(substr($buffer,0,1)) ;
		$buffer = substr($buffer,1) ;
		$cbyte 	= $b ;
		$bits 	= $b>>3 ;
	
		$cval 	= "" ;
		$cbyte &= 7 ;
		$cbyte<<= 5 ;
		$cbit 	= 2 ;
		// RECT
		for ($vals=0;$vals<4;$vals++) {
			$bitcount = 0 ;
			while ($bitcount<$bits) {
				if ($cbyte&128) {
					$cval .= "1" ;
				} else {
					$cval .= "0" ;
					}
				$cbyte<<=1 ;
				$cbyte &= 255 ;
				$cbit-- ;
				$bitcount++ ;
				if ($cbit<0) {
					$cbyte	= ord(substr($buffer,0,1)) ;
					$buffer = substr($buffer,1) ;
					$cbit = 7 ;
					}
			  }
			$c 		= 1 ;
			$val 	= 0 ;
			$tval = strrev($cval) ;
			for ($n=0;$n<strlen($tval);$n++) {
				$atom = substr($tval,$n,1) ;
				if ($atom=="1") $val+=$c ;
				$c*=2 ;
			  }
			// TWIPS to PIXELS
			$val/=20 ;
			switch ($vals) {
				case 0:
					// tmp value
					$width = $val ;
				break ;
				case 1:
					$width = $val - $width ;
				break ;
				case 2:
					// tmp value
					$height = $val ;
				break ;
				case 3:
					$height = $val - $height ;
				break ;
			  }
			$cval = "" ;
		}
		$buffer ='';
		return array($width,$height);
	}
	
	/**
	 * getimagesize without zlib doesn't work
	 * workaround for this
	 *
	 * @param string $BannerImage
	 * @return array
	 */
	public function getimagesizecompressed($BannerImage) 
	{
		$arrImageSize = false;
		$res = $this->swc_data($BannerImage);
		if ($res) {
			// width,height
			$arrImageSize = array($res[0], $res[1], 13);
			//log_message('[getimagesizecompressed] Image Details: '.print_r($arrImageSize,true).'', 'debug.log');
		}
		return $arrImageSize;
	}
	
	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		if (strlen(Input::get('tid')))
		{
			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
			$this->redirect($this->getReferer());
		}
		
		// Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_banner::banner_published', 'alexf'))
        {
                return '';
        }

		$href .= '&amp;tid='.$row['id'].'&amp;state='. ($row['banner_published'] ? '' : 1);

		if (!$row['banner_published'])
		{
			$icon = 'invisible.gif';
		}		

		return '<a href="'.$this->addToUrl($href.'&amp;id='.Input::get('id')).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}

	/**
	 * Disable/enable banner
	 * @param integer
	 * @param boolean
	 */
	public function toggleVisibility($intId, $blnVisible)
	{
		// Check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_banner::banner_published', 'alexf'))
        {
			$this->log('Not enough permissions to publish/unpublish Banner ID "'.$intId.'"', 'tl_banner toggleVisibility', TL_ERROR);
			// Code für Versionen ab 2.9.0
			$this->redirect('contao/main.php?act=error');
        }
        
		// Update database
		$this->Database->prepare("UPDATE tl_banner SET banner_published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
					   ->execute($intId);
	}
}

/**
 * Table tl_banner
 */
$GLOBALS['TL_DCA']['tl_banner'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_banner_category',
		'enableVersioning'            => true
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'filter'                  => true,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'search,filter,limit',
			//'headerFields'            => array('title', 'banner_template', 'banner_protected', 'tstamp'),
			'headerFields'            => array('title', 'banner_protected', 'tstamp'),
			'child_record_callback'   => array('tl_banner', 'listBanner')
		),		
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner']['toggle'],
				'icon'                => 'visible.gif',
				//'attributes'          => 'onclick="Backend.getScrollOffset();"',
				'attributes'          => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
				'button_callback'     => array('tl_banner', 'toggleIcon')
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
	      '__selector__'                => array('banner_type','banner_until'),
		  'default'                     => 'banner_type',
		  'banner_image'                => 'banner_type;{title_legend},banner_name,banner_weighting;{destination_legend},banner_url,banner_jumpTo,banner_target;{image_legend},banner_image,banner_imgSize;{comment_legend},banner_comment;{publish_legend},banner_published,banner_start,banner_stop,banner_until;{filter_legend:hide},banner_domain',
		  'banner_image_extern'         => 'banner_type;{title_legend},banner_name,banner_weighting;{destination_legend},banner_url,banner_target;{image_legend},banner_image_extern,banner_imgSize;{comment_legend},banner_comment;{publish_legend},banner_published,banner_start,banner_stop,banner_until;{filter_legend:hide},banner_domain',
		  'banner_text'                 => 'banner_type;{title_legend},banner_name,banner_weighting;{destination_legend},banner_url,banner_jumpTo,banner_target;{comment_legend},banner_comment;{publish_legend},banner_published,banner_start,banner_stop,banner_until;{filter_legend:hide},banner_domain'
	),
    // Subpalettes
	'subpalettes' => array
	(
		'banner_until'                => 'banner_views_until,banner_clicks_until'
	),


	// Fields
	'fields' => array
	(
	    'banner_type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_type'],
			'default'                 => 'default',
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'                 => array('default','banner_image', 'banner_image_extern', 'banner_text'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_banner_type'],
			'eval'                    => array('helpwizard'=>false, 'submitOnChange'=>true)
		),
		'banner_name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_name'],
			'inputType'               => 'text',
			'search'                  => true,
			'explanation'	          => 'banner_help',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'helpwizard'=>true, 'tl_class'=>'w50')
		),
		'banner_weighting' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_weighting'],
			'default'                 => '2',
			'inputType'               => 'select',
			'options'                 => array('1', '2', '3'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_banner'],
			'explanation'	          => 'banner_help',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>1, 'rgxp'=>'prcnt', 'helpwizard'=>true, 'tl_class'=>'w50')
		),
		'banner_url' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_url'],
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'helpwizard'=>true)
		),
		'banner_jumpTo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_jumpTo'],
			'exclude'                 => true,
			'inputType'               => 'pageTree',
			'eval'                    => array('fieldType'=>'radio', 'helpwizard'=>true),
			'explanation'             => 'banner_help'
		), 
		'banner_target' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_target'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		),
		'banner_image' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_image'],
			'explanation'	          => 'banner_help',
			'inputType'               => 'fileTree',
			'eval'                    => array('files'=>true, 'filesOnly'=>true, 'fieldType'=>'radio', 'extensions'=>'jpg,jpe,gif,png,swf', 'maxlength'=>255, 'helpwizard'=>true)
		),
		'banner_image_extern' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_image_extern'],
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'helpwizard'=>true)
		),
        'banner_imgSize' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_imgSize'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'rgxp'=>'digit', 'nospace'=>true)
		),
        'banner_comment' => array
        (
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_comment'],
			'inputType'               => 'textarea',
			'explanation'             => 'banner_help',
			'eval'                    => array('mandatory'=>false, 'preserveTags'=>true, 'helpwizard'=>true)
        ),
		'banner_published' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_published'],
			'filter'                  => true,
			'inputType'               => 'checkbox'
		),
		'banner_start' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_start'],
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('maxlength'=>20, 'rgxp'=>'datim', 'datepicker'=>true, 'helpwizard'=>true, 'tl_class'=>'w50 wizard')
		),
		'banner_stop' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_stop'],
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('maxlength'=>20, 'rgxp'=>'datim', 'datepicker'=>true, 'helpwizard'=>true, 'tl_class'=>'w50 wizard')
		),
		'banner_until'  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_until'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'clr')
		),
		'banner_views_until' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_views_until'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('nospace'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'helpwizard'=>true, 'tl_class'=>'w50')
		),
		'banner_clicks_until' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_clicks_until'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('nospace'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'helpwizard'=>true, 'tl_class'=>'w50')
		),
		'banner_domain' => array
		(
	        'label'                   => &$GLOBALS['TL_LANG']['tl_banner']['banner_domain'],
	        'inputType'               => 'text',
	        'explanation'	          => 'banner_help',
	        'eval'                    => array('mandatory'=>false, 'maxlength'=>255, 'helpwizard'=>true)
		)
	)
);



?>