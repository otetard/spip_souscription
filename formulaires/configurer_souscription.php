<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/config');

function formulaires_configurer_souscription_charger_dist() {


  $valeurs = array('adhesion_montants' => montants_array2str(lire_config("souscription/adhesion_montants")),
                   'adhesion_type_saisie' => lire_config("souscription/adhesion_type_saisie"),
                   "adhesion_activer" => lire_config("souscription/adhesion_activer"),

                   'don_montants' => montants_array2str(lire_config("souscription/don_montants")),
                   'don_type_saisie' => lire_config("souscription/don_type_saisie"),
                   "don_activer" => lire_config("souscription/don_activer"),
                   );

  return $valeurs;
}

function formulaires_configurer_souscription_verifier_dist() {
  /* FIXME: vérifier le format de 'adhesion_montants' et 'don_montants' */

  $erreurs = array();
  $type_saisies = array("input", "radio", "selection");

  if(_request("adhesion_activer")) {
    if(!_request("adhesion_type_saisie")) {
      $erreurs["adhesion_type_saisie"] = "Ce champs est obligatoire";
    }
    
    if(!in_array(_request("adhesion_type_saisie"), $type_saisies)) {
      $erreurs["adhesion_type_saisie"] = "Le type de saisie pour le montant est invalide";
    }
  }

  if(_request("don_activer")) {

    if(!_request("don_type_saisie")) {
      $erreurs["don_type_saisie"] = "Ce champs est obligatoire";
    }
    
    if(!in_array(_request("don_type_saisie"), $type_saisies)) {
      $erreurs["don_type_saisie"] = "Le type de saisie pour le montant est invalide";
    }
  }

  return $erreurs;
}

function formulaires_configurer_souscription_traiter_dist() {

  ecrire_config("souscription/adhesion_montants", montants_str2array(_request("adhesion_montants")));
  ecrire_config("souscription/adhesion_type_saisie", _request("adhesion_type_saisie"));
  ecrire_config("souscription/adhesion_activer", _request("adhesion_activer"));

  ecrire_config("souscription/don_montants", montants_str2array(_request("don_montants")));
  ecrire_config("souscription/don_type_saisie", _request("don_type_saisie"));
  ecrire_config("souscription/don_activer", _request("don_activer"));

  $res = array('message_ok'=>_T('facteur:config_info_enregistree'));
  
  return $res;
}


function montants_array2str($array) {
  $montants = "";
  foreach($array as $prix => $description) {
    $montants .= $prix . "|" . $description . "\n";
  }

  return $montants;
}

function montants_str2array($str) {
  $montants = array();

  foreach(explode("\n", trim($str)) as $montant) {
    list($prix, $description) = explode("|", $montant, 2);
    $montants[trim($prix)] = trim($description);
  }

  return $montants;
}
