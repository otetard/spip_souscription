<?php
/**
 * Formulaire de configuration
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Formulaires
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/config');

function formulaires_configurer_souscription_verifier_dist() {
  /* FIXME: vérifier le format de 'adhesion_montants' et 'don_montants' */

  $erreurs = array();
  $type_saisies = array("input", "radio", "radioinput", "selection");

  if(_request("adhesion_activer")) {
    if(!_request("adhesion_type_saisie")) {
      $erreurs["adhesion_type_saisie"] = _T("souscription:erreur_champ_obligatoire");
    }

    if(!in_array(_request("adhesion_type_saisie"), $type_saisies)) {
      $erreurs["adhesion_type_saisie"] = _T("souscription:erreur_champ_invalide");
    }
  }

  if(_request("don_activer")) {

    if(!_request("don_type_saisie")) {
      $erreurs["don_type_saisie"] = _T("souscription:erreur_champ_obligatoire");
    }

    if(!in_array(_request("don_type_saisie"), $type_saisies)) {
      $erreurs["don_type_saisie"] = _T("souscription:erreur_champ_invalide");
    }
  }

  return $erreurs;
}
