<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

function genie_seenthis_desactiver_flux($t){
	if (!defined('_SEENTHIS_IMPORTER_FLUX_DELAI_INACTIF')) {
		define('_SEENTHIS_IMPORTER_FLUX_DELAI_INACTIF', 86400 * 31 * 6);
	}

	$mydate = sql_quote(date("Y-m-d H:i:s", time() - _SEENTHIS_IMPORTER_FLUX_DELAI_INACTIF));

	$s = sql_query("SELECT id_auteur,login,en_ligne,email,rss FROM spip_auteurs WHERE rss > '' AND LEFT(rss,1) != '*' AND en_ligne < $mydate ORDER BY en_ligne DESC LIMIT 1");

	if ($t = sql_fetch($s)) {
		spip_log("desactivation du flux RSS de ". $t['login'] . " (". $t['id_auteur'] .") : ". $t['rss'], 'flux');
		// ajouter une * au début de l'url du flux pour le désactiver
		sql_updateq('spip_auteurs', array('rss' => '*' . $t['rss']),'id_auteur =' . $t['id_auteur']);
		// envoyer un email à l'auteur pour le prévenir qu'on a désactivé son flux
		include_spip('inc/notifications');
		$texte = recuperer_fond('notifications/flux_desactive', array('id_auteur'=>$t['id_auteur']));
		notifications_envoyer_mails($t['email'], $texte);
	}

	return 1;
}
