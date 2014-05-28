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
		// noter qu'on a fait le rappel
		sql_updateq("spip_souscriptions",array('abo_fin_raison'=>'Alerte echeance manquante'),'id_souscription='.intval($row['id_souscription']));
	}

	return 1;
}