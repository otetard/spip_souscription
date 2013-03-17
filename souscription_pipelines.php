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
  $flux['data'] += objet_optimiser_liens(array('souscription'=>'*'),'*');
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
    
	$email = sql_getfetsel('courriel', 'spip_souscriptions', 'id_transaction='.intval($flux['args']['id_transaction']));
	$sujet = '['.$GLOBALS['meta']['nom_site'].'] ';
    if ($flux['args']['succes']) {
		$sujet .= 'Confirmation de votre réglement';
		$message = recuperer_fond('modeles/mail-souscription-succes', 
									array('id_transaction' => $flux['args']['id_transaction']));
    }
    else {
		$sujet .= 'Echec de votre réglement';
		$message = recuperer_fond('modeles/mail-souscription-echec',
									array('id_transaction' => $flux['args']['id_transaction']));
    }
	$envoyer_mail = charger_fonction('envoyer_mail','inc');
	$envoyer_mail($email, $sujet, $message, $GLOBALS['meta']['email_webmaster']);
    
    return $flux;
}

?>
