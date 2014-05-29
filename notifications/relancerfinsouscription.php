<?php
/**
 * Relancer le donnateur mensuel apres la fin de son don pour cause de CB perimee
 * pour l'inviter a souscrire a nouveau
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
function notifications_relancerfinsouscription_dist($quoi, $id_souscription, $options){

	$souscription = sql_fetsel("*","spip_souscriptions","id_souscription=".intval($id_souscription));

	// on prend l'email de l'id_auteur en priorite, car a priori plus a jour
	if ($souscription['id_auteur'])
		$email = sql_getfetsel("email","spip_auteurs","id_auteur=".intval($souscription['id_auteur']));

	if (!$email)
		$email = $souscription['courriel'];

	if ($email){
		$texte = recuperer_fond("notifications/relancer_fin_souscription",array('id_souscription'=>$id_souscription));

		include_spip('inc/notifications');
		notifications_envoyer_mails($email,$texte);
	}

}