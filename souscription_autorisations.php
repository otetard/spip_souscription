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
// Objet souscriptions


/**
 * Autorisation de voir un élément de menu (souscriptions)
 **/
function autoriser_souscriptions_menu_dist($faire, $type, $id, $qui, $opt){
  return true;
}


/**
 * Autorisation de voir le bouton d'accès rapide de création (souscription)
 **/
function autoriser_souscriptioncreer_menu_dist($faire, $type, $id, $qui, $opt){
  return false;
}

/**
 * Autorisation de créer un don. Tout le monde est autorisé
 * à faire un don.
 **/
function autoriser_souscription_creer_dist($faire, $type, $id, $qui, $opt) {
  return true;
}

/**
 * Autorisation de voir un don. Il faut être administrateur pour voir
 * un don.
 **/
function autoriser_souscription_voir_dist($faire, $type, $id, $qui, $opt) {
  return autoriser('webmestre', '', '', $qui);
}

/**
 * Autorisation de modifier un don. Personne n'est autorisé à le
 * faire.
 **/
function autoriser_souscription_modifier_dist($faire, $type, $id, $qui, $opt) {
  return false;
}

/**
 * Autorisation de supprimer un don. Personne n'est autorisé à le
 * faire.
 **/
function autoriser_souscription_supprimer_dist($faire, $type, $id, $qui, $opt) {
  return false;
}

/*
 * Autorisation d'exporter un don.
 */
function autoriser_souscription_exporter_dist($faire, $type, $id, $qui, $opt) {
  return autoriser('webmestre', '', '', $qui);
}
