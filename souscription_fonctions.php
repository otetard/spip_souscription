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
	return $ids;
}

function calcul_avancement_campagne($id_campagne, $type_objectif, $objectif_initial) {

  $res = sql_fetsel(array("COUNT(S.id_souscription) as nombre",
			  "SUM(T.montant) AS somme"),
		    "spip_souscriptions AS S JOIN spip_souscriptions_liens as L ON (L.id_souscription=S.id_souscription) JOIN spip_transactions AS T ON (L.id_objet = T.id_transaction AND L.objet='transaction')",
		    array("S.id_souscription_campagne=".sql_quote($id_campagne),
			  "T.reglee = 'oui'"));

  return ($type_objectif == "don" ? $res['somme'] : $res['nombre']) + $objectif_initial;
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