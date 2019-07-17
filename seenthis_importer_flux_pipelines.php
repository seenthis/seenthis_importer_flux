<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

function seenthis_importer_flux_taches_generales_cron($taches_generales){
	if (!defined('_SEENTHIS_IMPORTER_FLUX_PERIODE_IMPORTER')) {
		define('_SEENTHIS_IMPORTER_FLUX_PERIODE_IMPORTER', 10);
	}
	if (!defined('_SEENTHIS_IMPORTER_FLUX_PERIODE_DESACTIVER')) {
		define('_SEENTHIS_IMPORTER_FLUX_PERIODE_DESACTIVER', 86400);
	}
	// duree du cron a moduler en fonction du nombre de flux...
	// pour le moment, on en prend un au hasard à chaque tour
	$taches_generales['seenthis_importer_flux'] = _SEENTHIS_IMPORTER_FLUX_NB_IMPORTER;
	// désactiver le flux des auteurs qui ne se sont pas logués depuis 6 mois
	$taches_generales['seenthis_desactiver_flux'] = _SEENTHIS_IMPORTER_FLUX_NB_DESACTIVER;

	return $taches_generales;
}
