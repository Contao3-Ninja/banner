<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Banner - Backend DCA tl_module
 *
 * This file modifies the data container array of table tl_module.
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2011
 * @author     Glen Langer
 * @package    Banner
 * @license    GPL
 * @filesource
 */

/**
 * Load tl_page language definitions
 */
$this->loadLanguageFile('tl_page');  

/**
 * Add a palette to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['banner'] = 'name,type,headline;banner_hideempty;banner_categories,banner_template;banner_redirect;guests,protected,banner_useragent;align,space,cssID';


/**
 * Add fields to tl_module
 */ 
$GLOBALS['TL_DCA']['tl_module']['fields']['banner_hideempty'] = array
(
	'label'         => &$GLOBALS['TL_LANG']['tl_module']['banner_hideempty'],
	'exclude'       => true,
	'inputType'     => 'checkbox'
);
$GLOBALS['TL_DCA']['tl_module']['fields']['banner_categories'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['banner_categories'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'foreignKey'              => 'tl_banner_category.title',
	'eval'                    => array('multiple'=>false, 'mandatory'=>true, 'tl_class'=>'w50')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['banner_template'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['banner_template'],
    'default'                 => 'mod_banner_list_all',
    'exclude'                 => true,
    'inputType'               => 'select',
    //'options'                 => $this->getTemplateGroup('mod_banner_list_'),
    'options_callback'        => array('tl_module_banner', 'getBannerTemplates'), 
    'eval'                    => array('tl_class'=>'w50')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['banner_redirect'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['redirect'],
	'default'                 => 'permanent',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => array('permanent', 'temporary'),
	'reference'               => &$GLOBALS['TL_LANG']['tl_page']
);
$GLOBALS['TL_DCA']['tl_module']['fields']['banner_useragent'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['banner_useragent'],
	'inputType'               => 'text',
	'search'                  => true,
	'explanation'	          => 'banner_help',
	'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'helpwizard'=>true)
);

class tl_module_banner	extends Backend 
{
	/**
	 * Import the back end user object
	 */
/*	public function __construct()
	{
		parent::__construct();
		//$this->import('BackendUser', 'User');
	}
*/
	public function getBannerTemplates(DataContainer $dc)
	{
	    return $this->getTemplateGroup('mod_banner_list_', $dc->activeRecord->pid);
	}  
}

?>