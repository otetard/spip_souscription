<?php
/**
 * Définit les autorisations du plugin Souscription
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Autorisations
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Fonction d'appel pour le pipeline
 * @pipeline autoriser */
function souscription_autoriser(){}


// -----------------
// Objet souscription_dons


/**
 * Autorisation de voir un élément de menu (souscriptiondons)
 **/
function autoriser_souscriptiondons_menu_dist($faire, $type, $id, $qui, $opt){
  return true;
}


/**
 * Autorisation de voir le bouton d'accès rapide de création (souscriptiondon)
 **/
function autoriser_souscriptiondoncreer_menu_dist($faire, $type, $id, $qui, $opt){
  /* return autoriser('creer', 'souscription_don', '', $qui, $opt); */
  return false;
}

/**
 * Autorisation de créer un don. Tout le monde est autorisé
 * à faire un don.
 **/
function autoriser_souscriptiondon_creer_dist($faire, $type, $id, $qui, $opt) {
  return true;
}

/**
 * Autorisation de voir un don. Il faut être administrateur pour voir
 * un don.
 **/
function autoriser_souscriptiondon_voir_dist($faire, $type, $id, $qui, $opt) {
  return autoriser('webmestre', '', '', $qui);
}

/**
 * Autorisation de modifier un don. Personne n'est autorisé à le
 * faire.
 **/
function autoriser_souscriptiondon_modifier_dist($faire, $type, $id, $qui, $opt) {
  return false;
}

/**
 * Autorisation de supprimer un don. Personne n'est autorisé à le
 * faire.
 **/
function autoriser_souscriptiondon_supprimer_dist($faire, $type, $id, $qui, $opt) {
  return false;
}

/* 
 * Autorisation d'exporter un don.
 */
function autoriser_souscriptiondon_exporter_dist($faire, $type, $id, $qui, $opt) {
  return autoriser('webmestre', '', '', $qui);
}
