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
	$souscription = sql_fetsel(array('courriel', 'id_souscription_campagne'), 'spip_souscriptions', 'id_transaction='.intval($flux['args']['id_transaction']));
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
