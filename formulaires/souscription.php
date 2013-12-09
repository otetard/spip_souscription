<?php
/**
 * Gestion du formulaire de d'édition de souscription
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Formulaires
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/config');

/**
 * Identifier le formulaire en faisant abstraction des paramètres qui ne représentent pas l'objet edité
 *
 * @param int|string $id_souscription
 *     Identifiant du souscription. 'new' pour un nouveau souscription.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un souscription source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du souscription, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return string
 *     Hash du formulaire
 */
function formulaires_souscription_identifier_dist($id_souscription='new',
                                                      $retour='',
                                                      $lier_trad=0,
                                                      $config_fonc='',
                                                      $row=array(),
                                                      $hidden='') {
  return serialize(array(intval($id_souscription)));
}

/**
 * Chargement du formulaire d'édition de souscription
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @uses formulaires_editer_objet_charger()
 *
 * @param int|string $id_souscription
 *     Identifiant du souscription. 'new' pour un nouveau souscription.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un souscription source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du souscription, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Environnement du formulaire
 */
function formulaires_souscription_charger_dist($id_souscription_campagne) {

  if(!verifier_campagne($id_souscription_campagne))
    return false;

  /* Récupération des information à propos de la campagne */
  $campagne = sql_fetsel(array("type_objectif", "configuration_specifique", "type_saisie", "montants"),
			 "spip_souscription_campagnes",
			 "id_souscription_campagne=$id_souscription_campagne");

  $type = $campagne['type_objectif'];

  $recu_fiscal = "";
  if($type == "adhesion")
    $recu_fiscal = "on";
  
  $courriel = "";
  $id_auteur = "";
  $desactiver_courriel = false;
  /* Si une connexion est active, on récupère le courriel et l'id_auteur */
  if (isset($GLOBALS['visiteur_session']['email']) && isset($GLOBALS['visiteur_session']['id_auteur'])) {
      $courriel = $GLOBALS['visiteur_session']['email'];
	  $id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
      /* Si abonnement nécessité d'avoir un compte. Là il existe déjà, on désactive le champs courriel */
      if ($type == "abonnement") {
          $desactiver_courriel = true;
      }
  }

  if($campagne['configuration_specifique'] !== 'on') {
    $montant_type = lire_config("souscription/{$type}_type_saisie", 'input');
    $montant_datas = lire_config("souscription/${type}_montants", array());
  }
  else {
    $montant_type = $campagne['type_saisie'];
    $montant_datas = $campagne['montants'];
  }

  $montant_label = lire_config("souscription/${type}_montants_label", _T('souscription:label_montant'));
  $montant_explication = nl2br(lire_config("souscription/${type}_montants_description"));

  return array('montant' => '',
               'courriel' => $courriel,
               'desactiver_courriel' => $desactiver_courriel,
			   'id_auteur' => $id_auteur,
               'recu_fiscal' => $recu_fiscal,
               'envoyer_info' => 'on',
               'informer_comite_local' => 'on',
               'prenom' => '',
               'nom' => '',
               'adresse' => '',
               'code_postal' => '',
               'ville' => '',
               'pays' => 'FR',
               'telephone' => '',
               'id_souscription_campagne' => $id_souscription_campagne,
               'type_souscription' => $type,
               'montant_datas' => montants_str2array($montant_datas),
               'montant_type' => $montant_type,
               'montant_label' => $montant_label,
               'montant_explication' => $montant_explication
               );
}

/**
 * Vérifications du formulaire d'édition de souscription
 *
 * Vérifier les champs postés et signaler d'éventuelles erreurs
 *
 * @uses formulaires_editer_objet_verifier()
 *
 * @param int|string $id_souscription
 *     Identifiant du souscription. 'new' pour un nouveau souscription.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un souscription source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du souscription, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Tableau des erreurs
 */
function formulaires_souscription_verifier_dist($id_souscription_campagne) {
  $campagne = _request('id_souscription_campagne');

  $erreurs = formulaires_editer_objet_verifier('souscription', 'new',
                                               array('courriel',
                                                     'montant',
                                                     'id_souscription_campagne'));

  if(!$id_souscription_campagne || intval($id_souscription_campagne) != intval($campagne)) {
      $erreurs['message_erreur'] = "Campagne invalide";
  }

  $campagne = sql_fetsel(array("type_objectif", "configuration_specifique", "type_saisie", "montants"),
			 "spip_souscription_campagnes", "id_souscription_campagne=$id_souscription_campagne");

  $type_campagne = $campagne['type_objectif'];

  /* Le champ 'type' (hidden) doit être le même que celui défini dans
   * la campagne. */
  if(_request('type_souscription') != $type_campagne)
    $erreurs['message_erreur'] = "Type de souscription invalide : " . _request('type_souscription');

  if(!verifier_campagne($id_souscription_campagne, $type_campagne)) {
    $erreurs['message_erreur'] = "La campagne à laquelle est associée cette souscription est invalide";
  }


  if(_request('recu_fiscal') || $type_campagne == "adhesion") {
    foreach(array('prenom', 'nom', 'adresse', 'code_postal', 'ville', 'pays') as $obligatoire) {
      if(!_request($obligatoire)) {
        if($type_campagne == "adhesion") {
          $erreurs[$obligatoire] = "Ce champ est obligatoire pour les adhésions";
        }
        else {
          $erreurs[$obligatoire] = 'Ce champ est obligatoire (reçu fiscal demandé)';
        }
      }
    }
  }

  if ($e = _request('courriel') AND !email_valide($e)) {
    $erreurs['courriel'] = _T('form_prop_indiquer_email');
  }
  elseif ($type == 'abonnement' AND !$GLOBALS['visiteur_session']['email'] AND !$GLOBALS['visiteur_session']['id_auteur']) {
    /* Existe-t-il déjà un compte avec cet email ? */
    $email = sql_getfetsel('email', 'spip_auteurs', 'email='.sql_quote(_request('courriel')));
    /* si c'est le cas on demande le mot de passe */
    if ($email) {
        if (!_request('password')) {
            $erreurs['password'] = "Il existe un compte lié à votre courriel, vous devez vous identifier";   
        }
        else {
            include_spip('inc/auth');
            $auteur = auth_identifier_login($email, _request('password'));
            if (!is_array($auteur)) {
                $erreurs['password'] = "Mauvais mot de passe";
            }
			else {
				set_request('id_auteur', $auteur['id_auteur']);
			}
        }
    }
  }

  if($e = _request('pays')) {
    $ret = sql_select('nom', 'spip_pays', "code='${e}'");

    if(sql_count($ret) != 1)
      $erreurs['pays'] = "Pays invalide";

    /* Le code postal n'est vérifié que si on est dans le cas de la France */
    elseif($e = _request('pays') AND $e == "FR") {
      if ($e = _request('code_postal') AND !preg_match("/^(2[ABab]|0[1-9]|[1-9][0-9])[0-9]{3}$/", $e)) {
        $erreurs['code_postal'] = "Code postal invalide";
      }
    }
  }

  if ($e = _request('telephone') AND !preg_match("/^[0-9\+ \.]+$/", $e)) {
    $erreurs['telephone'] = "Numéro de téléphone incorrect";
  }

  /* Vérification du montant. Si la campagne est configurée pour
   * utiliser une configuration spécifique, alors, il faut vérifier
   * avec les montants de la campagne. Autrement, il faut utiliser les
   * paramètres globaux.
   */
  if ($e = _request('montant')) {
    if(!(ctype_digit($e)))
      $erreurs['montant'] = "Montant invalide";
    else {
      if($campagne['configuration_specifique'] !== 'on') {
	$montant_type = lire_config("souscription/{$type}_type_saisie", 'input');
	$montant_datas = lire_config("souscription/${type}_montants", array());
      }
      else {
	$montant_type = $campagne['type_saisie'];
	$montant_datas = montants_str2array($campagne['montants']);
      }

      /* On ne vérifie strictement la valeur du montant que si on
       * n'utilise pas le type de saisie « entrée libre » (input) pour
       * le montant. */
      if(($montant_type != "input") AND !array_key_exists($e, $montant_datas))
        $erreurs['montant'] = "Le montant spécifié est invalide" . var_export($campagne, true);
    }
  }

  if(count($erreurs) > 0) {
    $erreurs['message_erreur'] = "Le formulaire contient des erreurs";
  }

  return $erreurs;
}

/**
 * Traitement du formulaire d'édition de souscription
 *
 * Traiter les champs postés
 *
 * @uses formulaires_editer_objet_traiter()
 *
 * @param int|string $id_souscription
 *     Identifiant du souscription. 'new' pour un nouveau souscription.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un souscription source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du souscription, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Retours des traitements
 */
function formulaires_souscription_traiter_dist($id_souscription_campagne) {
  $lier_trad=0;
  $config_fonc='';
  $row=array();
  $hidden='';
  
  /* est-ce un abonnement ? */
  $type_objectif = sql_getfetsel('type_objectif', 'spip_souscription_campagnes', 'id_souscription_campagne='.intval($id_souscription_campagne));
  if ($type_objectif == 'abonnement' AND _request('id_auteur')) {
      /* Si abonnement et compte SPIP existe déjà on passe id_auteur pour insertion dans spip_transactions */
      set_request('id_auteur', _request('id_auteur'));
  }

  $ret = formulaires_editer_objet_traiter('souscription',
                                          'new',
                                          '',
                                          $lier_trad,
                                          $retour,
                                          $config_fonc,
                                          $row,
                                          $hidden);

  $redirect = "";
  $row = sql_fetsel("transaction_hash,id_transaction",
                    "spip_transactions LEFT JOIN spip_souscriptions USING(id_transaction)",
                    "id_souscription=".$ret['id_souscription']);

  if(!$row) {
    spip_log(sprintf("Erreur lors de la création de la transaction liée à la souscription [%s].", $ret['id_souscription']), "souscription");
    $ret['message_erreur'] = "Echec creation de la transaction";
  }
  else {
    spip_log(sprintf("La souscription [%s], associée à la transaction [%s] a bien été crée.", $ret['id_souscription'], $row['id_transaction']), "souscription");
    $hash = $row['transaction_hash'];
    $id_transaction = $row['id_transaction'];
    
    if ($type_objectif == 'abonnement') {
        $redirect = generer_url_public("payer-abonnement", "id_transaction=$id_transaction&transaction_hash=$hash", false, false);    
    }
    else {
        $redirect = generer_url_public("payer-acte", "id_transaction=$id_transaction&transaction_hash=$hash", false, false);
    }
    $ret['redirect'] = $redirect;
  }

  return $ret;
}

function verifier_campagne($id_souscription_campagne, $type_souscription=null) {
  /* FIXME: vérifier que la campagne a bien le bon statut (ouverte, fermée, terminée, etc.) */

  $campagne = sql_fetsel(array('type_objectif', 'objectif_initial', 'objectif', 'objectif_limiter'),
			 'spip_souscription_campagnes', 'id_souscription_campagne='.sql_quote(intval($id_souscription_campagne)));

  /* La campagne doit exister */
  if(!count($campagne['type_objectif']))
    return false;

  elseif($type_souscription != null && $campagne['type_objectif'] != $type_souscription)
    return false;

  /* Si la campagne doit être fermée lorsque l'objectif est atteint,
   * alors on bloque. */
  elseif($campagne['objectif_limiter'] &&
	 calcul_avancement_campagne($id_souscription_campagne, $campagne['type_objectif'], $campagne['objectif_initial']) >= $campagne['objectif'])
    return false;

  return true;
}

