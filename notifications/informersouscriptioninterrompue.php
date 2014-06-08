<?php
/**
 * Notification de derniere echeance payee sur une souscription mensuelle
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
 *   id_transaction
 *   message
 */
function notifications_informersouscriptioninterrompue_dist($quoi, $id_souscription, $options = array()){

	$email = '';
	$souscription = sql_fetsel("*","spip_souscriptions","id_souscription=".intval($id_souscription));

	// on prend l'email de l'id_auteur en priorite, car a priori plus a jour
	if ($souscription['id_auteur'])
		$email = sql_getfetsel("email","spip_auteurs","id_auteur=".intval($souscription['id_auteur']));

	if (!$email)
		$email = $souscription['courriel'];

	if ($email){
		$contexte = $options;
		$contexte['id_souscription'] = $id_souscription;
		$texte = recuperer_fond("notifications/informer_souscription_interrompue",$contexte);

		include_spip('inc/notifications');
		notifications_envoyer_mails($email,$texte);
	}
}