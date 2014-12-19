<?php

/**
 * Workaround for Contao 3.4.0 Model Bug #7490
 */

// BannerCategoryModel.php
class BannerCategoryModel extends Model
{
    // Angabe des Tabellennamens ist zwingend notwendig
    protected static $strTable = 'tl_banner_category';
}