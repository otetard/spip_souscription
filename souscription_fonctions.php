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

function calcul_avancement_campagne($id_campagne, $type_objectif, $objectif_initial) {

  $res = sql_fetsel(array("COUNT(*) as nombre",
			  "SUM(montant) AS somme"),
		    "spip_souscriptions AS S INNER JOIN spip_transactions AS T ON (S.id_transaction = T.id_transaction)",
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

  return saisies_chaine2tableau(saisies_aplatir_chaine($str));
}

function campagne_afficher_objectif($nombre,$type_objectif){
	return $nombre.($type_objectif == "don" ? " EUR" : "");
}