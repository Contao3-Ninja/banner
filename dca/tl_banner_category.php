<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @link http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * 
 * Modul Banner - Backend DCA tl_banner_category
 *
 * This is the data container array for table tl_banner_category.
 *
 * PHP version 5
 * @copyright  Glen Langer 2007..2012
 * @author     Glen Langer 
 * @package    Banner
 * @license    GPL
 */
class tl_banner_category extends Backend
{
	public function labelCallback($arrRow)
	{
		$label_1 = $arrRow['title'];
		if (version_compare(VERSION, '2.99', '>'))
		{
			$version_warning = '';
		} else {
			$version_warning = '<br /><span style="color:#ff0000;">[ERROR: Banner-Module requires at least Contao 3.0]</span>';
		}
		
		$bpc = $GLOBALS['TL_LANG']['tl_banner_category']['banner_protected_catagory'];
		if ( !empty($arrRow['banner_protected']) && strlen($arrRow['banner_groups']) )
		{
			$label_2 = '<img height="16" width="14" alt="'.$bpc.'" title="'.$bpc.'" src="system/themes/default/images/protect.gif" />';
		} else {
			$label_2 = '';
		}
		return $label_1 . ' ' . $label_2 . $version_warning;
	}
}

/**
 * Table tl_banner_category 
 */
$GLOBALS['TL_DCA']['tl_banner_category'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_banner'),
		'switchToEdit'                => true,
		'enableVersioning'            => true
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('title'),
			'flag'                    => 1,
			'panelLayout'             => 'search,limit'
		),
		'label' => array
		(
			//'fields'                  => array('title','banner_template','banner_protected'),
			//'format'                  => '%s <br /><span style="color:#b3b3b3;">[%s]<br />[%s]</span>'
			'fields'                  => array('tag'),
			'format'                  => '%s',
			'label_callback'		  => array('tl_banner_category', 'labelCallback'),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_banner_category']['edit'],
				'href'                => 'table=tl_banner',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner_category']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner_category']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['tl_banner_category']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner_category']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'stat' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_banner_category']['stat'],
				'href'                => 'do=bannerstat',
				'icon'                => 'system/modules/banner/assets/iconBannerStat.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
	    '__selector__'                => array('banner_default', 'banner_protected', 'banner_numbers'), 
		//'default'                     => '{title_legend},title,banner_template;{default_legend:hide},banner_default;{number_legend:hide},banner_numbers;{protected_legend:hide},banner_protected'
		'default'                     => '{title_legend},title;{default_legend:hide},banner_default;{number_legend:hide},banner_numbers;{protected_legend:hide},banner_protected'
	),
	// Subpalettes
	'subpalettes' => array
	(
		'banner_default'              => 'banner_default_name,banner_default_url,banner_default_image,banner_default_target',
		'banner_protected'            => 'banner_groups',
		'banner_numbers'              => 'banner_limit,banner_random'
	),

	// Fields
	'fields' => array
	(
		'title' 					  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['title'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>60, 'tl_class'=>'w50')
		),
		'banner_template'             => array // nicht mehr in palette
		(
            'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['banner_template'],
            'default'                 => 'mod_banner_list_all',
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => $this->getTemplateGroup('mod_banner_list_'),
            'eval'                    => array('tl_class'=>'w50')
		),
		'banner_default'              => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['banner_default'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		'banner_default_name'         => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_name'],
			'inputType'               => 'text',
			'search'                  => true,
			'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'tl_class'=>'w50')
		),
		'banner_default_url'		  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_url'],
			'inputType'               => 'text',
			'explanation'	          => 'banner_help',
			'eval'                    => array('mandatory'=>false, 'maxlength'=>128, 'tl_class'=>'w50')
		),
		'banner_default_image'        => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_image'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('files'=>true, 'filesOnly'=>true, 'fieldType'=>'radio', 'extensions'=>'jpg,jpe,gif,png,swf', 'maxlength'=>255, 'helpwizard'=>false, 'tl_class'=>'clr')
		),
		'banner_default_target'		  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['banner_default_target'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		),
		'banner_numbers'			  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['banner_numbers'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		'banner_random'				  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['banner_random'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		),
		'banner_limit'				  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['banner_limit'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>'digit', 'nospace'=>true, 'maxlength'=>10)
		),
		'banner_protected'            => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['banner_protected'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		'banner_groups'               => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_banner_category']['banner_groups'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array('multiple'=>true)
		)
	)
);

?>