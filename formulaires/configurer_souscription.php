<?php
if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/config');

function formulaires_configurer_souscription_charger_dist() {
  /* Configuration des saisies */
  $liste_saisies = array('input' => _T('souscription:configurer_type_saisie_input'),
			 'radio' => _T('souscription:configurer_type_saisie_radio'),
			 'selection' => _T('souscription:configurer_type_saisie_selection'));

  $saisies = array();
  foreach(array('adhesion', 'don') as $type) {
    $saisies[] = array("saisie" => "oui_non",
		       "options" => array("nom" => "${type}_activer",
					  "label" => _T("souscription:label_activer_${type}"))
		       );
    $saisies[] = array("saisie" => "fieldset",
		       "options" => array("nom" => "${type}_groupe",
					  "label" => _T("souscription:label_${type}_groupe"),
					  "afficher_si" => "@${type}_activer@ == 'on'"),
		       "saisies" => array(array("saisie" => "selection",
						"options" => array("nom" => "${type}_type_saisie",
								   "label" => _T("souscription:label_type_saisie"),
								   "cacher_option_intro" => "oui",
								   "datas" => $liste_saisies)
						),
					  array("saisie" => "input",
						"options" => array("nom" => "${type}_montants_label",
								   "label" => _T("souscription:label_montants_label"))
						),
					  array("saisie" => "textarea",
						"options" => array("nom" => "${type}_montants",
								   "rows" => "4",
								   "label" => _T("souscription:label_montants"),
								   "explication" => _T("souscription:explication_montants"))
						),
					  array("saisie" => "textarea",
						"options" => array("nom" => "${type}_montants_description",
								   "rows" => "4",
								   "label" => _T("souscription:label_montants_explication")))
					  )
		       );
  }

  $saisies[] = array("saisie" => "fieldset",
		     "options" => array("nom" => "general_groupe",
					"label" => _T("souscription:label_general_groupe")),
		     "saisies" => array(array('saisie' => 'textarea',
					      'options' => array('nom' => 'dispositions_fiscales_explications',
								 'rows' => '4',
								 'label' => _T('souscription:label_dispositions_fiscales_explications')))));



  $valeurs = array('adhesion_montants' => lire_config("souscription/adhesion_montants"),
                   'adhesion_type_saisie' => lire_config("souscription/adhesion_type_saisie"),
                   "adhesion_activer" => lire_config("souscription/adhesion_activer"),
                   "adhesion_montants_description" => lire_config("souscription/adhesion_montants_description"),
                   "adhesion_montants_label" => lire_config("souscription/adhesion_montants_label"),

                   'don_montants' => lire_config("souscription/don_montants"),
                   'don_type_saisie' => lire_config("souscription/don_type_saisie"),
                   "don_activer" => lire_config("souscription/don_activer"),
                   "don_montants_description" => lire_config("souscription/don_montants_description"),
                   "don_montants_label" => lire_config("souscription/don_montants_label"),

		   "dispositions_fiscales_explications" => lire_config("souscription/dispositions_fiscales_explications"),
		   '_saisies' => $saisies
                   );

  return $valeurs;
}

function formulaires_configurer_souscription_verifier_dist() {
  /* FIXME: vérifier le format de 'adhesion_montants' et 'don_montants' */

  $erreurs = array();
  $type_saisies = array("input", "radio", "selection");

  if(_request("adhesion_activer")) {
    if(!_request("adhesion_type_saisie")) {
      $erreurs["adhesion_type_saisie"] = _T("souscription:message_nok_champ_obligatoire");
    }

    if(!in_array(_request("adhesion_type_saisie"), $type_saisies)) {
      $erreurs["adhesion_type_saisie"] = _T("souscription:message_nok_champ_invalide");
    }
  }

  if(_request("don_activer")) {

    if(!_request("don_type_saisie")) {
      $erreurs["don_type_saisie"] = _T("souscription:message_nok_champ_obligatoire");
    }

    if(!in_array(_request("don_type_saisie"), $type_saisies)) {
      $erreurs["don_type_saisie"] = _T("souscription:message_nok_champ_invalide");
    }
  }

  return $erreurs;
}

function formulaires_configurer_souscription_traiter_dist() {

  ecrire_config("souscription/adhesion_montants", _request("adhesion_montants"));
  ecrire_config("souscription/adhesion_type_saisie", _request("adhesion_type_saisie"));
  ecrire_config("souscription/adhesion_activer", _request("adhesion_activer"));
  ecrire_config("souscription/adhesion_montants_description", _request("adhesion_montants_description"));
  ecrire_config("souscription/adhesion_montants_label", _request("adhesion_montants_label"));

  ecrire_config("souscription/don_montants", _request("don_montants"));
  ecrire_config("souscription/don_type_saisie", _request("don_type_saisie"));
  ecrire_config("souscription/don_activer", _request("don_activer"));
  ecrire_config("souscription/don_montants_description", _request("don_montants_description"));
  ecrire_config("souscription/don_montants_label", _request("don_montants_label"));

  ecrire_config("souscription/dispositions_fiscales_explications", _request("dispositions_fiscales_explications"));

  $res = array('message_ok'=>_T('souscription:config_info_enregistree'));

  return $res;
}
