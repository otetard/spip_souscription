<?php
/**
 * Utilisations de pipelines par Souscription
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Optimiser la base de données en supprimant les liens orphelins
 * de l'objet vers quelqu'un et de quelqu'un vers l'objet.
 *
 * @pipeline optimiser_base_disparus
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function souscription_optimiser_base_disparus($flux){

	include_spip('action/editer_liens');
	$flux['data'] += objet_optimiser_liens(array('souscription'=>'*'), '*');
	return $flux;
}

/**
 * Envoi d'une notification après reglement
 *
 * @pipeline trig_bank_notifier_reglement
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function souscription_trig_bank_notifier_reglement($flux) {
	$souscription = sql_fetsel(array('courriel', 'id_souscription_campagne'), 'spip_souscriptions', 'id_transaction_echeance='.intval($flux['args']['id_transaction']));
	$email = $souscription['courriel'];
	$campagne = $souscription['id_souscription_campagne'];

	if ($flux['args']['succes']) {
		$message = recuperer_fond(_trouver_modele_courriel_reglement("succes", $campagne),
					  array('id_transaction' => $flux['args']['id_transaction']));
	}
	else {
		$message = recuperer_fond(_trouver_modele_courriel_reglement("echec", $campagne),
					  array('id_transaction' => $flux['args']['id_transaction']));
	}

	spip_log(sprintf("Envoi de notifiaction de confirmation de paiement à [%] pour la souscription [%s].", $email, $flux['args']['id_transaction']),
		 "souscription");

	include_spip("inc/notifications");
	notifications_envoyer_mails($email, $message, "", $GLOBALS['meta']['email_webmaster']);

	return $flux;
}

function _trouver_modele_courriel_reglement($type, $id_souscription_campagne) {
	$modele = "modeles/mail-souscription-${type}";

	if(trouver_fond("${modele}-${id_souscription_campagne}"))
		$modele = "${modele}-${id_souscription_campagne}";

	return $modele;
}


function souscription_bank_traiter_reglement($flux){
	$flux['data'].=" <br />Vous allez recevoir un email de confirmation.";
	return $flux;
}


/**
 * Activer la souscription abonnee
 * @param $flux
 * @return mixed
 */
function souscription_bank_abos_activer_abonnement($flux){
	if (!$flux['data']){
		$abo_uid = $flux['args']['abo_uid'];
		$set = array(
			"abonne_uid"=>sql_quote($abo_uid),
			"abo_statut"=>sql_quote('ok'),
		);

		if ($id_transaction = $flux['args']['id_transaction']){
			if (!$row = sql_fetsel("*","spip_souscriptions","id_transaction_echeance=".intval($id_transaction))
			  OR !$id_souscription = $row['id_souscription']){
			 	spip_log("Impossible de retrouver la souscription liee a la transaction $id_transaction",'souscriptions_abos'._LOG_ERREUR);
				return $flux;
			}

			if (!$r = sql_fetsel("statut,montant","spip_transactions","id_transaction=".intval($id_transaction))){
				return $flux;
			}
			if ($r['statut']!=='ok'){
			 	spip_log("La transaction $id_transaction n'a pas ete reglee (abo $abo_uid)",'souscriptions_abos'._LOG_ERREUR);
				return $flux;
			}
			// fixer le montant cumul des dons
			$set['montant_cumul'] = sql_quote($r['montant']);
		}
		elseif (
			!$abo_uid
		  OR !($row = sql_fetsel("*","spip_souscriptions","abonne_uid=".sql_quote($abo_uid)))
		  ){
		  spip_log("Impossible de retrouver l'abo_uid $abo_uid",'souscriptions_abos'._LOG_ERREUR);
			return $flux;
		}

		if ($id_transaction
		  AND ($id_transaction==$row['id_transaction_echeance']) ){

			if (!intval($row["date_echeance"]))
				$row["date_echeance"] = $row["date_souscription"];

			$prochaine_echeance = date('Y-m-d H:i:s',strtotime("+1 month",strtotime($row["date_echeance"])));
			if ($flux['data']['validite']==='echeance'){
				$set["date_echeance"] = sql_quote($prochaine_echeance);
				$set["date_fin"] = $set["date_echeance"];
			}
			else {
				if ($prochaine_echeance<$flux['args']['validite']){
					$set["date_echeance"] = sql_quote($prochaine_echeance);
				}
				else {
					$set["date_echeance"] = sql_quote($row["date_souscription"]);
				}
				$set["date_fin"] = sql_quote($flux['args']['validite']);
			}

		}

		if ($row['id_souscription'] AND count($set)){
			sql_update("spip_souscriptions",$set,"id_souscription=".intval($row['id_souscription']));
		}
		if ($row['id_souscription']){
			$flux['data'] = $row['id_souscription'];
		}
	}

	return $flux;
}

/**
 * Decrire l'echeance d'une souscription mensuelle
 * @param array $flux
 * @return array
 */
function souscription_bank_abos_decrire_echeance($flux){
	if ($id_transaction = $flux['args']['id_transaction']
	  AND $row = sql_fetsel("*","spip_souscriptions","id_transaction_echeance=".intval($id_transaction))){
		$flux['data']['montant'] = $row['montant'];
		$flux['data']['montant_ht'] = $row['montant'];
	}
	return $flux;
}

/**
 * Gerer le renouvellement lors de la notification de paiement d'une echeance par le presta bancaire
 *
 * @param array
 * @return array
 */
function souscription_bank_abos_renouveler($flux){

	if (!$flux['data']){

		$id = $flux['args']['id'];
		if (strncmp($id,"uid:",4)==0){
			$where = "abonne_uid=".sql_quote(substr($id,4));
		}
		else {
			$where = "id_souscription=".intval($id);
		}
		if ($row = sql_fetsel("*","spip_souscriptions",$where,'','date_souscription DESC')){

			$options = array(
				'auteur' => $row['courriel'],
				'parrain' => 'souscription',
				'tracking_id' => $row['id_souscription'],
				'id_auteur' => $row['id_auteur'],
			);

			// ouvrir la transaction
			$inserer_transaction = charger_fonction("inserer_transaction","bank");
			if ($id_transaction = $inserer_transaction($row['montant'],$options)){
				$set = array(
					'id_transaction_echeance' => $id_transaction,
					'abo_statut' => 'ok',
					'montant_cumul' =>  round(floatval($row['montant_cumul']) + floatval($row['montant']),2),
				);
				sql_updateq('spip_souscriptions',$set,"id_souscription=".intval($row['id_souscription']));
				include_spip("action/editer_liens");
				objet_associer(array("souscription"=>$row['id_souscription']),array("transaction"=>$id_transaction));
				$flux['data'] = $id_transaction;
			}
		}
	}

	return $flux;
}

/**
 * Prendre en charge la resiliation demandee par le client
 *
 * @param array $flux
 * @return array
 */
function souscription_bank_abos_resilier($flux){

	$id = $flux['args']['id'];
	if (strncmp($id,"uid:",4)==0){
		$where = "abonne_uid=".sql_quote(substr($id,4));
	}
	else {
		$where = "id_souscription=".intval($id);
	}
	if ($row = sql_fetsel("*","spip_souscriptions",$where,'','date_souscription DESC')){
		$set = array(
			'abo_statut' => 'resilie',
			'abo_fin_raison' => $flux['args']['message'],
		);
		if ($flux['args']['date_fin']=='date_echeance'){
			$set['date_fin'] = date('Y-m-d 00:00:00',strtotime($row['date_echeance']));
			if (!function_exists('souscription_derniere_echeance')){
				include_spip("public/parametrer");
			}
			$set['date_echeance'] = souscription_derniere_echeance($row['date_echeance'],$set['date_fin']);
		}
		else {
			$set['date_fin'] = $flux['args']['date_fin'];
		}

		$ok = true;
		if ($flux['args']['notify_bank']
		  AND $mode_paiement = sql_getfetsel("mode","spip_transactions","id_transaction=".intval($row['id_transaction_echeance']))){
			$ok = abos_resilier_notify_bank($row['abonne_uid'],$mode_paiement);
		}
		if ($ok){
			sql_updateq("spip_souscriptions",$set,"id_souscription=".intval($row['id_souscription']));
		}
	}

	return $flux;
}