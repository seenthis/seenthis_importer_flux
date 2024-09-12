<?php

/*
 * Seenthis_importer_flux : ajouter un champ 'rss' dans spip_auteurs
 *
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function seenthis_importer_flux_declarer_champs_extras($champs = []) {

	// Table : spip_auteurs
	if (!is_array($champs['spip_auteurs'])) {
		$champs['spip_auteurs'] = [];
	}

	$champs['spip_auteurs']['rss'] =  [
		'saisie' => 'input',
		'options' =>  [
			'nom' => 'rss',
			'label' => 'RSS',
			'sql' => 'varchar(256) DEFAULT NULL',
			'rechercher' => 1,
			'obligatoire' => false,
			'versionner' => true
		]
	];

	return $champs;
}
