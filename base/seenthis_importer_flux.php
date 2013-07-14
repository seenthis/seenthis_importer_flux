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


// champs extras pour afficher tous les champs supplŽmentaires d'un auteur
// dans l'espace privŽ (si on active le plugin champ_extras :
// svn co svn://zone.spip.org/spip-zone/_plugins_/champs_extras/core/branches/v1/ champs_extras2/
function seenthis_importer_flux_declarer_champs_extras($champs = array()){

	$champs[] = new ChampExtra(array(
		'table' => 'auteur', // sur quelle table ?
		'champ' => 'rss', // nom sql
		'label' => 'RSS', // chaine de langue 'prefix:cle'
		'precisions' => '', // precisions sur le champ
		'obligatoire' => false, // 'oui' ou '' (ou false)
		'rechercher' => 1, // false, ou true ou directement la valeur de ponderation (de 1 ˆ 8 generalement)
		'type' => 'ligne', // type de saisie
		'sql' => "varchar(256) DEFAULT NULL", // declaration sql
	));

	return $champs;
}


?>