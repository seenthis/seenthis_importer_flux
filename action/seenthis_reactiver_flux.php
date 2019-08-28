<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function action_seenthis_reactiver_flux_dist() {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	$id_auteur = intval($arg);
	
	// reactiver le flux (s'il commence bien par une étoile)
	$s = sql_query("SELECT login,rss FROM spip_auteurs WHERE id_auteur = $id_auteur");
	if ($t = sql_fetch($s) and substr($t['rss'], 0, 1) == '*') {
		spip_log("reactivation du flux RSS de ". $t['login'] . " (". $id_auteur .") : ". ltrim($t['rss'], '*'), 'flux');
		// mettre à jour le champ en_ligne à la date courante
		sql_updateq('spip_auteurs', array('en_ligne' => date('Y-m-d H:i:s')), 'id_auteur =' . $id_auteur);
		// retirer * au début de l'url du flux pour le réactiver
		sql_updateq('spip_auteurs', array('rss' => ltrim($t['rss'], '*')), 'id_auteur =' . $id_auteur);
	}
	
	// redirect
	$GLOBALS['redirect'] = $GLOBALS['meta']['adresse_site'];

}