<?php @error_reporting(0); @ini_set("display_errors", 0);  

if (version_compare(VERSION . '.' . BUILD, '2.8.9', '>'))
{
	try { $objDatabase = Database::getInstance(); } catch (Exception $e) { $errors[] = $e->getMessage(); }		
	try { $objDatabase->listTables(); } catch (Exception $e) { $errors[] = $e->getMessage(); }
	
	$migration = false;
	$addTemplate = false;
	
	if ($objDatabase->tableExists('tl_banner_category')) 
	{
		if ($objDatabase->fieldExists('banner_template', 'tl_banner_category') 
		&& !$objDatabase->fieldExists('banner_template', 'tl_module'))
		{
			//Migration mit Neufeldanlegung
			//Feld anlegen
			try { $objDatabase->execute("ALTER TABLE `tl_module` ADD `banner_template` varchar(32) NOT NULL default ''"); } catch (Exception $e) { $errors[] = $e->getMessage(); }
			$addTemplate = true;
			//Feld versuchen zu fuellen, macht der naechste Abschnitt
		}
		
		if ( ($objDatabase->fieldExists('banner_template', 'tl_banner_category') 
		   && $objDatabase->fieldExists('banner_template', 'tl_module')) || $addTemplate === true)
		{
			//Test ob Feld in allen Banner Modulen leer
			try { $objTemplates = $objDatabase->execute("SELECT count(banner_template) AS ANZ FROM tl_module WHERE banner_template !=''"); } catch (Exception $e) { $errors[] = $e->getMessage(); }
			while ($objTemplates->next())
			{
				if ($objTemplates->ANZ > 0) {
					//nicht gefuellt
					$migration = false;
				} else {
					$migration = true;
				}
			}
			
			if ($migration == true) {
				//Feld versuchen zu fuellen
				try { $objBannerTemplatesNew = $objDatabase->execute("SELECT `id`, `name` , `banner_categories` FROM `tl_module` WHERE `type`='banner'"); } catch (Exception $e) { $errors[] = $e->getMessage(); }
				while ($objBannerTemplatesNew->next())
				{
					if (strpos($objBannerTemplatesNew->banner_categories,':') !== false) 
					{
						$arrKat = deserialize($objBannerTemplatesNew->banner_categories,true);
					} else {
						$arrKat = array($objBannerTemplatesNew->banner_categories);
					}
					if (count($arrKat) == 1 && (int)$arrKat[0] >0) { //nicht NULL
						//eine eindeutige Zuordnung, kann eindeutig migriert werden
						try { $objTemplatesOld = $objDatabase->execute("SELECT `id`, `title`, `banner_template` FROM `tl_banner_category` WHERE id =".$arrKat[0].""); } catch (Exception $e) { $errors[] = $e->getMessage(); }
						while ($objTemplatesOld->next())
						{
							try { $objDatabase->prepare("UPDATE tl_module SET banner_template=? WHERE id=?")->execute($objTemplatesOld->banner_template, $objBannerTemplatesNew->id); } catch (Exception $e) { $errors[] = $e->getMessage(); }
							//Protokoll
							$strText = 'Banner-Module "'.$objBannerTemplatesNew->name.'" has been migrated';
							try { $objDatabase->prepare("INSERT INTO tl_log (tstamp, source, action, username, text, func, ip, browser) VALUES(?, ?, ?, ?, ?, ?, ?, ?)")->execute(time(), 'BE', 'CONFIGURATION', '', specialchars($strText), 'Banner Modul Template Migration', '127.0.0.1', 'NoBrowser'); } catch (Exception $e) { $errors[] = $e->getMessage(); }
						}
					} elseif (count($arrKat) > 1) {
						try { $objTemplatesOld = $objDatabase->execute("SELECT `id`, `title`, `banner_template` FROM `tl_banner_category` WHERE id =".$arrKat[0].""); } catch (Exception $e) { $errors[] = $e->getMessage(); }
						while ($objTemplatesOld->next())
						{
							//Protokoll
							$strText = 'Banner-Module "'.$objBannerTemplatesNew->name.'" could not be migrated';
							try { $objDatabase->prepare("INSERT INTO tl_log (tstamp, source, action, username, text, func, ip, browser) VALUES(?, ?, ?, ?, ?, ?, ?, ?)")->execute(time(), 'BE', 'ERROR', '', specialchars($strText), 'Banner Modul Template Migration', '127.0.0.1', 'NoBrowser'); } catch (Exception $e) { $errors[] = $e->getMessage(); }
						}
					}
				}
			}
		}
	}
} else {
	$objDatabase = Database::getInstance();
	try { $objDatabase->prepare("INSERT INTO tl_log (tstamp, source, action, username, text, func, ip, browser) VALUES(?, ?, ?, ?, ?, ?, ?, ?)")->execute(time(), 'FE', 'ERROR', ($GLOBALS['TL_USERNAME'] ? $GLOBALS['TL_USERNAME'] : ''), 'ERROR: Banner-Module requires at least Contao 2.9', 'ModulBanner Runonce', '127.0.0.1', 'NoBrowser'); } catch (Exception $e) { $errors[] = $e->getMessage(); }
}

?>