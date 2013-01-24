<?php
/*
 * Seenthis_importer_flux : ajouter un champ 'rss' dans spip_auteurs
 *
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function seenthis_importer_flux_declarer_tables_principales($tables_principales){
	$tables_principales['spip_auteurs']['field']['rss'] = "varchar(256)";
	return $tables_principales;
}

function seenthis_importer_flux_install($action,$prefix,$version_cible){
	$version_base = $GLOBALS[$prefix."_base_version"];
	switch ($action){
		case 'test':
			$ok = (isset($GLOBALS['meta'][$prefix."_base_version"])
				AND version_compare($GLOBALS['meta'][$prefix."_base_version"],$version_cible,">="));
			return $ok;
			break;
		case 'install':
			include_spip('base/create');
			maj_tables(array(
				'spip_auteurs',
			));
			ecrire_meta($prefix."_base_version",
				$current_version=$version_cible, 'non');
			break;
		case 'uninstall':
			sql_query("ALTER TABLE spip_auteurs DROP rss");
			break;
	}
}


?>