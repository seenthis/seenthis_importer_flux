<?php

/*
 * Seenthis_importer_flux : ajouter un champ 'rss' dans spip_auteurs
 *
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

function seenthis_importer_flux_declarer_champs_extras($champs = array()) {

	// Table : spip_auteurs
	if (!is_array($champs['spip_auteurs'])) {
		$champs['spip_auteurs'] = array();
	}

	$champs['spip_auteurs']['rss'] = array (
		'saisie' => 'input',
		'options' => array (
			'nom' => 'rss',
			'label' => 'RSS',
			'sql' => "varchar(256) DEFAULT NULL",
			'rechercher' => 1,
			'obligatoire' => false,
			'versionner' => true
		)
	);

	return $champs;
}
