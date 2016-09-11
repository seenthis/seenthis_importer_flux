<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

function seenthis_importer_flux_taches_generales_cron($taches_generales){
	// duree du cron a moduler en fonction du nombre de flux...
	// pour le moment, on en prend un au hasard à chaque tour
	$taches_generales['seenthis_importer_flux'] = 10;
	// désactiver le flux des auteurs qui ne se sont pas logués depuis 6 mois
	$taches_generales['seenthis_desactiver_flux'] = 10;

	return $taches_generales;
}
