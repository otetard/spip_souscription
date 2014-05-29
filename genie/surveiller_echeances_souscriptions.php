<?php
/**
 * Surveiller les echeances des souscriptions mensuelles
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Genie
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


function genie_surveiller_echeances_souscriptions_dist(){

	$datemoins2d = date('Y-m-d H:i:s',strtotime('-2 day'));
	$datemoins2m = date('Y-m-d H:i:s',strtotime('-2 month'));
	$notifications = charger_fonction('notifications', 'inc');

	// trouver toutes les souscriptions dont l'echeance est passee de plus de 2 jours et notifier
	$rows = sql_allfetsel("*","spip_souscriptions",
		"abo_statut=".sql_quote('ok')." AND abo_fin_raison=".sql_quote('')
		." AND date_echeance<".sql_quote($datemoins2d)
		." AND date_echeance>".sql_quote($datemoins2m)
	  ." AND (date_echeance<date_fin OR date_fin<date_souscription)"
	);

	foreach($rows as $row){
		$notifications('alerterecheancesouscription', $row['id_souscription']);
		spip_log("alerterecheancesouscription id_souscription=".$row['id_souscription'],'souscriptions_surveillance');
		// noter qu'on a fait le rappel
		sql_updateq("spip_souscriptions",array('abo_fin_raison'=>'Alerte echeance manquante'),'id_souscription='.intval($row['id_souscription']));
	}

	return 1;
}



/**
 * chercher les souscriptions mensuelles terminees et les relancer
 * et envoyer une relance
 */
function genie_relance_souscriptions_finies($now=null){
	if (!$now) $now = time();

	// passer en fini les souscriptions terminees : date_fin passee
	// abo_statut = fini pour les souscriptions qui vont a leur terme mais s'arrete a cause de la validite CB
	// il faut les relancer, contrairement aux resilies
	$date_fin = date('Y-m-d H:i:s',$now);
	sql_updateq("spip_souscriptions",array('abo_statut'=>'fini','abo_fin_raison'=>'date de fin'),"abo_statut=".sql_quote('ok')." AND date_fin>date_souscription AND date_fin<".sql_quote($date_fin));


	// trouver tous les rappels en cours sur les abo_statut=fini
	$rappels = sql_allfetsel("DISTINCT abo_relance","spip_souscriptions",'abo_statut='.sql_quote('fini').' AND abo_relance<>'.sql_quote('off'));
	$rappels = array_map('reset',$rappels);

	$where = array();
	foreach($rappels as $r){
		$where[] = "(abo_relance=".sql_quote($r,'','text')." AND date_fin<".sql_quote(date('Y-m-d H:i:s',strtotime('-'.(intval($r)).' day',$now))).")";
	}

	$where = "(".implode(") OR (",$where).")";
	$where = "(($where) AND (abo_statut=".sql_quote('fini')."))";

	$nb=2;
	$notifications = charger_fonction('notifications', 'inc');
	while($nb--){
		if ($row = sql_fetsel('id_souscription,date_fin,abo_relance','spip_souscriptions',$where,'','date_fin','0,1')){
			spip_log("genie_relance_souscriptions_finies id_souscription=".$row['id_souscription'].", date_fin:".$row['date_fin'].", abo_relance:".$row['abo_relance'],'souscriptions_surveillance');
			$notifications('relancerfinsouscription', $row['id_souscription']);
			// noter qu'on a fait le rappel
			sql_updateq("spip_souscriptions",array('abo_relance'=>souscriptions_prochaine_relance($row['date_fin'],$now)),'id_souscription='.intval($row['id_souscription']));
		}
		else $nb=0;
	}
}

function souscriptions_prochaine_relance($date_fin,$now=null){
	if (!$now) $now = time();

	// TODO : rendre les echeances de relance parametrables
	// sous forme de chaine par exemple "1,7,15,30"
	$relances = "0,7,15,30";
	$relances = explode(",",$relances);
	$relances = array_map("intval",$relances);
	$relances = array_unique($relances);
	rsort($relances);

	$next = 'off';
	while ($jours = array_shift($relances)){
		if ($date_fin<date('Y-m-d H:i:s',strtotime("-".$jours." day",$now)))
			return $next;
		$next = $jours;
	}

	return 'off'; // on n'arrive jamais la
}