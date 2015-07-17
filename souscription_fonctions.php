<?php
/**
 * filtres et balises specifiques
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

function souscription_liste_transactions($id_souscription){

	$ids = sql_allfetsel("id_objet","spip_souscriptions_liens","id_souscription=".sql_quote($id_souscription)." AND objet=".sql_quote('transaction'));
	$ids = array_map('reset',$ids);
	if (!count($ids))
		$ids = array(0);
	return $ids;
}

function calcul_avancement_campagne($id_campagne, $type_objectif, $objectif_initial) {

	// les paiements uniques
  $row_unique = sql_fetsel(
	  array("COUNT(S.id_souscription) as nombre","SUM(S.montant) AS somme"),
		"spip_souscriptions AS S",
		array("S.id_souscription_campagne=".sql_quote($id_campagne),"S.statut=".sql_quote('ok'),"S.abo_statut=".sql_quote('non'))
  );
	// les paiements recurents
	// comptes pour 1 (don|adhesion) et pour le comul de la somme versee
	$row_abos = sql_fetsel(
    array("COUNT(S.id_souscription) as nombre","SUM(S.montant_cumul) AS somme"),
		"spip_souscriptions AS S",
		array("S.id_souscription_campagne=".sql_quote($id_campagne),sql_in("S.abo_statut",array('ok','resilie')))
  );

  return ($type_objectif == "don" ? $row_unique['somme']+$row_abos['somme'] : $row_unique['nombre']+$row_abos['nombre']) + $objectif_initial;
}

function souscription_campagne_historique_encaissements($id_campagne){
	$rows = sql_allfetsel(
		"sum(T.montant_ht) as montant_mensuel_ht,sum(T.montant) as montant_mensuel,T.date_paiement",
		"spip_transactions AS T
			JOIN spip_souscriptions_liens AS L ON (L.objet='transaction' AND L.id_objet=T.id_transaction)
			JOIN spip_souscriptions AS S ON S.id_souscription=L.id_souscription",
		"T.statut='ok' AND S.id_souscription_campagne=".intval($id_campagne),
		"DATE_FORMAT(T.date_paiement,'%Y-%m')",
		"T.date_paiement DESC"
	);
	$out = "";
	foreach($rows as $row){
		$mois = affdate_mois_annee($row['date_paiement']);
		$montant = affiche_monnaie($row['montant_mensuel']);
		$montant_ht = affiche_monnaie($row['montant_mensuel_ht']);
		$out .= "<tr><td>$mois</td><td class='numeric'>$montant_ht</td><td class='numeric'>$montant</td></tr>\n";
	}

	$out = "<table class='spip'>
<thead><tr class='row_first'><th>Mois</th><th class='numeric'>Montant HT</th><th class='numeric'>Montant</th></td></thead>
<tbody>$out</tbody></table>";

	return $out;
}

/*
 * Balise #AVANCEMENT_CAMPAGNE permettant de faire les calculs
 * d'avancement de la campagne (nombre des adhésions pourl es
 * campagnes de type adhésion ; somme des montants pour les campagnes
 * de dons).
 *
 * Cette balise ne peut être utilisée que dans une boucle de type
 * SOUSCRIPTION_CAMPAGNE
 */
function balise_AVANCEMENT_CAMPAGNE_dist($p) {

  if($p->type_requete != "souscription_campagnes") {
    $msg = array('zbug_champ_hors_boucle',
		 array('champ' => '#AVANCEMENT_CAMPAGNE')
		 );
    erreur_squelette($msg, $p);
  }
  else {
    $_campagne = champ_sql('id_souscription_campagne', $p);
    $_type_objectif = champ_sql('type_objectif', $p);
    $_objectif_initial = champ_sql('objectif_initial', $p);
    $p->code = "calcul_avancement_campagne($_campagne, $_type_objectif, $_objectif_initial)";
    $p->interdire_scripts = false;
  }

  return $p;
}

function montants_str2array($str,$abo="") {
  include_spip('inc/saisies');
  include_spip('inc/texte');

  /* Vérification du format de la chaine. Elle doit être sous la forme
   * « [montant] | [label] », par exemple « 10 | 10 € ». */
  foreach(explode("\n", trim($str)) as $l) {
    if(!preg_match('/^[0-9]+\|.*/', $l)) {
      return false;
    }
  }

	if ($abo){
		$str = $abo . trim(str_replace("\n","\n$abo",$str));
	}

  $res = saisies_chaine2tableau(saisies_aplatir_chaine($str));
	$res = array_map('typo',$res);
	return $res;
}

function campagne_afficher_objectif($nombre,$type_objectif){
	return $nombre.($type_objectif == "don" ? " EUR" : "");
}

function souscription_derniere_echeance($date_echeance,$date_fin){
	$next = $date_echeance;
	if (!intval($date_fin)){
		$date_fin = "2020-12-31 00:00:00";
	}
	while (intval($date_fin) AND $date_echeance>$date_fin)
		$date_echeance = date('Y-m-d H:i:s',strtotime('-1 month',strtotime($date_echeance)));
	while (($next=date('Y-m-d H:i:s',strtotime('+1 month',strtotime($date_echeance))))<=$date_fin)
		$date_echeance = $next;
	return $date_echeance;
}

function souscription_rappel_duree($date_echeance){
	$fin = strtotime('+1 month',strtotime($date_echeance));
	$now = time();
	if ($now>$fin) return "end";
	foreach(array(15,30,45,60,90) as $d){
		$jalon = strtotime("-$d day",$fin);
		if ($now>$jalon) return $d;
	}
	return $d;
}


function souscription_montant_recu($id_souscription,$annee){
	$souscription = sql_fetsel('*','spip_souscriptions','id_souscription='.intval($id_souscription));
	if ($souscription['statut']!=='ok') return 0;
	// c'est un don ponctuel : facile il suffit de verifier l'annee de souscription
	if ($souscription['abo_statut']=='non'){
		if (intval(date('Y',strtotime($souscription['date_souscription']))==$annee))
			return $souscription['montant'];
		else
			return 0;
	}
	// c'est un don mensuel : ressortir toutes les transactions de la bonne annee associees a cette souscription
	// et faire la somme
	$montant = sql_getfetsel("sum(montant)","spip_transactions","statut=".sql_quote('ok')." AND ".sql_in('id_transaction',souscription_transactions($id_souscription,$annee)));
	return $montant;

}

function souscription_transactions($id_souscription,$annee){
	$start = mktime(0,0,0,1,1,$annee);
	$end = mktime(0,0,0,1,1,$annee+1);
	$start = date('Y-m-d H:i:s',$start);
	$end = date('Y-m-d H:i:s',$end);

	$trans = sql_allfetsel("T.id_transaction",
		"spip_souscriptions_liens as L JOIN spip_transactions as T on (L.objet=".sql_quote('transaction')." AND L.id_objet=T.id_transaction)",
		"L.id_souscription=".intval($id_souscription)." AND date_transaction>=".sql_quote($start)." AND date_transaction<".sql_quote($end)
	);
	$trans = array_map("reset",$trans);
	return $trans;
}

/**
 * Calculer un hash lowsec pour securiser les urls qui permettent de voir les recus
 * @param $id_souscription
 * @param $annee
 * @return string
 */
function souscription_hash_lowsec($id_souscription,$annee){
	$souscription = sql_fetsel('id_souscription,montant,courriel,date_souscription,nom,prenom,adresse,code_postal,ville','spip_souscriptions','id_souscription='.intval($id_souscription));

	$souscription = serialize($souscription);
	$hash = md5("$souscription-hashlowsec-$annee");
	$hash = substr($hash,0,10);
	return $hash;
}

/**
 * Numeroter un recu
 * @param int $id_souscription
 * @param int $annee
 * @return string
 */
function souscription_numero_recu($id_souscription,$annee){
	return "R-$annee-$id_souscription";
}

/**
 * Lister les recus disponibles pour une souscription :
 *  - 1 recu par annee fiscale
 *  - s'arrete a annee N-1 si souscription mensuelle toujours en cours
 * @param $id_souscription
 * @return array
 */
function souscription_lister_recus($id_souscription){
	$annees = array();
	$souscription = sql_fetsel('id_souscription,date_souscription,date_fin,statut,abo_statut','spip_souscriptions','id_souscription='.intval($id_souscription));

	if ($souscription['statut']!=='ok')
		return $annees;

	if ($souscription['abo_statut']=='non'){
		$annees[] = date('Y',strtotime($souscription['date_souscription']));
	}
	else {
		$annee = date('Y',strtotime($souscription['date_souscription']));
		$annee_fin = max($annee,date('Y',strtotime($souscription['date_fin'])));
		$annee_fiscalenmoins1 = date('Y')-1;

		while ($annee<=$annee_fin AND $annee<=$annee_fiscalenmoins1) {
			$annees[] = $annee;
			$annee++;
		}
		// si resilie ou fini, on peut ajouter l'annee de fin si pas prise en compte
		if ($souscription['abo_statut']!=='ok'){
			if ($annee_fin>$annee_fiscalenmoins1){
				$annees[] = $annee_fin;
			}
		}
	}

	return $annees;
}

/**
 * Lister les souscriptions d'un auteur, triee par annee
 * @param int $id_auteur
 * @return array
 */
function souscription_lister_recus_auteur($id_auteur){
	$sous = sql_allfetsel("id_souscription","spip_souscriptions","id_auteur=".intval($id_auteur));
	$recus = array();
	foreach($sous as $sou){
		$annees = souscription_lister_recus($sou['id_souscription']);
		foreach($annees as $annee){
			$recus["$annee-".$sou['id_souscription']] = array('id_souscription'=>$sou['id_souscription'],'annee'=>$annee);
		}
	}
	ksort($recus);
	return $recus;
}

/**
 * URL pour generer/voir le recu pour une souscription+annee
 * @param int $id_souscription
 * @param int $annee
 * @return string
 */
function souscription_generer_url_recu($id_souscription,$annee){
	return generer_url_action("generer_recu_souscription","id_souscription=$id_souscription&annee=$annee&hash=".souscription_hash_lowsec($id_souscription,$annee));
}