<?php
/**
 * Notification qu'une echeance n'a pas ete recue
 * aux webmestres uniquement pour le moment
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Genie
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * @param string $quoi
 * @param int $id_souscription
 * @param array $options
 */
function notifications_alerterecheancesouscription_dist($quoi, $id_souscription, $options){

	include_spip('inc/config');
	$email = lire_config('souscription/email_alerte_echeances');
	if (!$email){
		$email = lire_config('bank_paiement/email_ticket_admin');
	}
	if (!$email){
		$email = $GLOBALS['meta']['email_webmaster'];
	}

	$texte = recuperer_fond("notifications/alerter_echeance_souscription",array('id_souscription'=>$id_souscription));

	include_spip('inc/notifications');
	notifications_envoyer_mails($email,$texte);

}