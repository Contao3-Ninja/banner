<?php

/**
 * Workaround for Contao 3.4.0 Model Bug #7490
 */

// BannerModel.php
class BannerModel extends Model
{
    // Angabe des Tabellennamens ist zwingend notwendig
    protected static $strTable = 'tl_banner';
}
