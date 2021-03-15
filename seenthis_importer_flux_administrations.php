<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/cextras');
include_spip('base/seenthis_importer_flux');
include_spip('inc/meta');

function seenthis_importer_flux_upgrade($nom_meta_base_version,$version_cible){
	$maj = array();

	$maj['create'] = array(
		array('sql_alter',"TABLE spip_me ADD viarss tinyint(1) NOT NULL DEFAULT '0'")
	);

	cextras_api_upgrade(seenthis_importer_flux_declarer_champs_extras(), $maj['create']);

	// en 1.0.1, poser viarss=0
	$maj['1.0.1'] = array(
		array('sql_alter',"TABLE spip_me ADD viarss tinyint(1) NOT NULL DEFAULT '0'"),
	);

	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

function seenthis_importer_flux_vider_tables($nom_meta_base_version) {
	cextras_api_vider_tables(seenthis_importer_flux_declarer_champs_extras());
	effacer_meta($nom_meta_base_version);
}
