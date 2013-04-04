<?php

function seenthis_importer_flux_taches_generales_cron($taches_generales){
	// duree du cron a moduler en fonction du nombre de flux...
	// pour le moment, on en prend un au hasard à chaque tour
	$taches_generales['seenthis_importer_flux'] = 60;

	return $taches_generales;
}

function genie_seenthis_importer_flux($t){
	define('_SYNDICATION_DEREFERENCER_URL', true); // feedburner

	$s = sql_query("SELECT id_auteur,login,rss, RAND() AS hasard FROM spip_auteurs WHERE rss>'' ORDER BY hasard LIMIT 1");

	if ($t = sql_fetch($s)) {
		include_spip('inc/distant');
		include_spip('inc/syndic');
		if ($url = $t['rss']
		AND preg_match(',^https?://,', $url)
		AND $rss = recuperer_page($url, $transcoder = true)
		AND $articles = analyser_backend($rss)
		AND is_array($articles)
		) {
			foreach (array_values($articles) as $k => $article) {
				$action = seenthis_importer_rss_article($article, $t['id_auteur'], $create = ($k<5));
				if ($action == 2) {
					# creation d'un nouveau message : on sort
					break;
				}
			}
		} else {
			spip_log("probleme avec le RSS '$url' de l'auteur $t[login]", 'flux');
		}
	}

	return 1;
}

function seenthis_importer_rss_article($article, $moi, $create=true) {
	$urlo = $article['url'];

	# fixer les URLs
	$urlo = sucrer_utm($urlo);

	// 'pmo'
	$urlo = preg_replace(
		',^(http://www.piecesetmaindoeuvre.com/)spip.php\?article(\d+),',
		'\1spip.php?page=resume&id_article=\2', $urlo);

	// seenthis n'aime pas les / final :
	$url = preg_replace(',/+$,', '', $urlo);

	# si l'url pointe un message local, il faut fav
	if (preg_match(',^https?://('
	.preg_quote(_HOST).'/messages/(\d+)|'
	.preg_quote(_SHORT_HOST).'/([a-f0-9]+)'
	.'),',
	$url, $r)) {
		if ($r[3]) { # short
			$id_me = base_convert($r[3],36,10);
		} else {
			$id_me = $r[2];
		}
		spip_log("$url local: $id_me", 'flux');
	}
	else {
		# Règles du jeu :
		# 1. si un lien est deja en base mais dans un message
		# effacé nous appartenant, on ne fait rien : cela permet
		# de supprimer a la main un message ajouté par le rss, sans
		# qu'il ne reviennent bégayer...
		# 2. si un lien existe et appartient à quelqu'un d'autre,
		# on le partage, sauf si on a bloqué la personne
		$q = 'SELECT t.id_me,m.id_auteur
		FROM spip_me_tags AS t
		INNER JOIN spip_me AS m ON t.uuid=m.uuid AND t.tag='.sql_quote($url);
		# auteurs que je bloque / ou que je follow
		if ($block = sql_allfetsel('id_auteur', 'spip_me_block', 'id_block='.$moi)) {
			$b = array();
			foreach($block as $k)
				$b[] = $k['id_auteur'];
			$q .= ' WHERE '.sql_in('m.id_auteur', $b, 'NOT');
		}
		if ($deja = sql_fetch(sql_query($q))) {
			# $deja = array (id_me => id_me, id_auteur => id_auteur)
			$id_me = $deja['id_me'];
		}
	}

	# si rien, on cree
	if (!$id_me) {
		if (!$create) {
			spip_log("Ne pas creer ($url)", 'flux');
			return 0;
		}

		include_spip('inc/uuid');
		$uuid = UUID::getuuid($moi.$url);

		# verifier si le message uuid n'a pas été effacé
		if (sql_allfetsel('uuid','spip_me', 'uuid='.sql_quote($uuid))) {
			spip_log("Message $uuid existant mais efface ($url)", 'flux');
			return 0;
		}

		$message = $article['titre']."\n".'[@@@@@@]';
		if (strlen($desc = $article['descriptif'])
		OR strlen($desc = $article['content'])) {

			if (!$image
			AND $img = extraire_balise($desc, 'img')
			AND $img = extraire_attribut($img, 'src')
			AND preg_match(',^https?://.*(jpe?g|gif|png)$,i', $img)) {
				$image = $img;
			}

			$desc = couper(supprimer_tags($desc),500);
			$desc = str_replace('&nbsp;', ' ', $desc);
			$desc = preg_replace(',  +,', ' ', $desc);
		}

		if ($image)
			$message .= "\n\n$image";

		if ($desc)
			$message .= "\n\n❝".$desc."❞";

		if (is_array($article['tags'])) {
			$tags = array();
			# tags a ignorer
			$censure = explode(' ', 'Cahier internetactu internetactu2net fing MesInfos article_consultable');
			foreach ($article['tags'] as $tag) {
				$rel = extraire_attribut($tag, 'rel');
				if (strstr(",tag,directory,", ",$rel,")
				AND $tag = seenthis_nettoyer_tag($tag)
				AND !in_array($tag, $censure)
				) {
					$bt = '/\b'.str_replace('_', '[ _]', preg_quote($tag)).'\b/i';
					if (preg_match($bt, $message)) {
						$message = preg_replace($bt, '#'.$tag, $message, 1);
						$message = str_replace('##', '#', $message);
					}
					else
						$tags[] = "#".$tag;
				}
				else
				// les enclosures sont affichees sous forme de lien brut
				if (strstr(",enclosure,external,", ",$rel,")
				AND $href = extraire_attribut($tag, 'href'))
					$tags[] = "\n".$href;
			}
			foreach (extraire_balises($article['enclosures'],'a') as $enc) {
				$rel = extraire_attribut($enc, 'rel');
				if (strstr(",enclosure,external,", ",$rel,")
				AND $href = extraire_attribut($enc, 'href')
				AND $href != $url # enclosure <> adresse de l'article
				AND false === strpos($message, $href))  # enclosure n'est pas dans le descriptif
					$tags[] = "\n".$href;
			}
		}
		if ($tags) $message = trim($message."\n".trim(join(' ',array_unique($tags))));

		$message = str_replace('[@@@@@@]', $urlo, $message);

		$message = unicode_to_utf_8(
			html_entity_decode(
				preg_replace('/&([lg]t;)/S', '&amp;\1', charset2unicode($message)),
				ENT_NOQUOTES, 'utf-8')
		);

		spip_log("creation $uuid $message",'flux');
		if (strlen($message))
			instance_me($moi, $message,  $id_me=0, $id_parent=0, $id_dest=0, $ze_mot=0, $time="NOW()", $uuid);
		return 2;
	}

	// on a trouvé un message :
	// s'il est a nous, ou si on l'a deja partage, ne rien faire
	$mess = sql_allfetsel('*', 'spip_me', "id_me=$id_me");
	if ($mess[0]['id_auteur'] == $moi) {
		spip_log("$id_me deja envoye par $moi ($url)", 'flux');
		return 0;
	}
	# si c'est dans une reponse, partager le parent
	if ($mess[0]['id_parent'] > 0)
		$id_me = $mess[0]['id_parent'];
	$share = sql_allfetsel('*', 'spip_me_share', "id_me=$id_me AND id_auteur=$moi");
	if (count($share)) {
		spip_log("$id_me deja partage par $moi ($url)", 'flux');
		return 0;
	}

	// sinon, ajouter un partage
	spip_log("$moi partage $id_me ($url)", 'flux');
	sql_insertq('spip_me_share', array('id_me' => $id_me, 'id_auteur' => $moi, 'date' => 'NOW()'));
	cache_me($id_me);
	return 1;
}

function seenthis_nettoyer_tag($tag) {
	$tag = unicode2charset(charset2unicode(supprimer_tags($tag)));
	$tag = preg_replace('/&#0?39;/', "'", $tag);
	$tag = preg_replace('/([ ()"]|&(quot|#.*?);)+/', '_', $tag);
	return $tag;
}

?>