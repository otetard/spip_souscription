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


function genie_surveiller_paiement_souscriptions_dist(){


	genie_relancer_souscriptions_abandonnees();

	genie_alerte_echeances_manquantes();

	genie_relance_souscriptions_finies();

	return 1;
}


/**
 * Trouver toutes les souscriptions qui sont restees en commande : processus abandonne avant paiement
 * (ce qui permet de donner une url directe vers la page de paiement sans refaire le processus)
 * et on relance en invitant a revenir souscrire
 *
 * @param null|int $now
 */
function genie_relancer_souscriptions_abandonnees($now = null){
	if (!$now) $now = time();

	$datemoins1w = date('Y-m-d H:i:s',strtotime('-1 week',$now));
	$notifications = charger_fonction('notifications', 'inc');


	// trouver toutes les souscriptions dont l'echeance est passee de plus de 1 semaine et notifier
	// ca laisse le temps de recevoir les cheques pour les reglements par cheque, sans relancer inutilement
	$rows = sql_allfetsel(
		"S.id_souscription,S.courriel,S.date_souscription",
		"spip_souscriptions AS S JOIN spip_transactions AS T on T.id_transaction=S.id_transaction_echeance",
		"S.statut=".sql_quote('prepa')." AND S.date_souscription<".sql_quote($datemoins1w)." AND T.statut=".sql_quote('commande'),
	  '','date_souscription DESC','0,5');

	foreach($rows as $row){
		// il faut verifier que la personne n'a pas reussi a faire une nouvelle souscription par la suite
		// si c'est le cas, on note en abandon cette souscription
		if (sql_countsel("spip_souscriptions","statut=".sql_quote('ok')." AND courriel=".sql_quote($row['courriel'])." AND date_souscription>".sql_quote($row['date_souscription']))){
			sql_updateq("spip_souscriptions",array('statut'=>'abandon'),'id_souscription='.intval($row['id_souscription']));
		}
		// si on a deja fait une relance plus recente, on ne fait rien non plus
		elseif (sql_countsel("spip_souscriptions","statut=".sql_quote('relance')." AND courriel=".sql_quote($row['courriel'])." AND date_souscription>".sql_quote($row['date_souscription']))){
			sql_updateq("spip_souscriptions",array('statut'=>'abandon'),'id_souscription='.intval($row['id_souscription']));
		}
		// sinon on envoi une relance, et on note en relance
		else {
			$notifications('inviterrecommencersouscription', $row['id_souscription']);
			spip_log("inviterrecommencersouscription id_souscription=".$row['id_souscription'],'souscriptions_surveillance');
			// noter qu'on a fait le rappel
			sql_updateq("spip_souscriptions",array('statut'=>'relance'),'id_souscription='.intval($row['id_souscription']));
		}
	}

}


/**
 * chercher toutes les souscriptions dont l'echeance est passee de plus de 2 jours et notifier aux admins
 * @param null|int $now
 */
function genie_alerte_echeances_manquantes($now=null){
	if (!$now) $now = time();

	$datemoins2d = date('Y-m-d H:i:s',strtotime('-2 day',$now));
	$datemoins2m = date('Y-m-d H:i:s',strtotime('-2 month',$now));
	$notifications = charger_fonction('notifications', 'inc');

	// trouver toutes les souscriptions dont l'echeance est passee de plus de 2 jours et notifier
	$rows = sql_allfetsel("*","spip_souscriptions",
		"abo_statut=".sql_quote('ok')." AND abo_fin_raison=".sql_quote('')
		." AND date_echeance<".sql_quote($datemoins2d)
		." AND date_echeance>".sql_quote($datemoins2m)
	  ." AND (date_echeance<date_fin OR date_fin<date_souscription)",
		'','date_echeance','0,5'
	);

	foreach($rows as $row){
		$notifications('alerterecheancesouscription', $row['id_souscription']);
		spip_log("alerterecheancesouscription id_souscription=".$row['id_souscription'],'souscriptions_surveillance');
		// noter qu'on a fait le rappel
		sql_updateq("spip_souscriptions",array('abo_fin_raison'=>'Alerte echeance manquante'),'id_souscription='.intval($row['id_souscription']));
	}

}



/**
 * chercher les souscriptions mensuelles terminees et les relancer
 * et envoyer une relance
 * @param null|int $now
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

	$nb=5;
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