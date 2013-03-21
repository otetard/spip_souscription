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
  $type = sql_getfetsel("type_objectif",
                        "spip_souscription_campagnes",
                        "id_souscription_campagne=$id_souscription_campagne");

  $recu_fiscal = "";
  if($type == "adhesion")
    $recu_fiscal = "on";

  return array('montant' => '',
               'courriel' => '',
               'recu_fiscal' => $recu_fiscal,
               'envoyer_info' => 'on',
               'informer_comite_local' => 'on',
               'prenom' => '',
               'nom' => '',
               'adresse' => '',
               'code_postal' => '',
               'ville' => '',
               'pays' => '',
               'id_souscription_campagne' => $id_souscription_campagne,
               'type_souscription' => $type,
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

  if(!verifier_campagne($id_souscription_campagne)) {
    $erreurs['message_erreur'] = "La campagne à laquelle est associée cette souscription est invalide";
  }

  if(!$id_souscription_campagne || intval($id_souscription_campagne) != intval($campagne)) {
      $erreurs['message_erreur'] = "Campagne invalide";
  }

  /* La campagne doit être valide (définie dans la base) et doit
   * accepter les dons. */
  $type = sql_getfetsel("type_objectif",
                        "spip_souscription_campagnes",
                        "id_souscription_campagne=$id_souscription_campagne");

  if(!$type || !in_array($type, array("don", "adhesion")))
    $erreurs['message_erreur'] = "Type de souscription invalide";

  /* Le champ 'type' (hidden) doit être le même que celui défini dans
   * la campagne. */
  if(_request('type_souscription') != $type)
    $erreurs['message_erreur'] = "Type de souscription invalide: " . _request('type_souscription');


  if(_request('recu_fiscal') || $type == "adhesion") {
    foreach(array('prenom', 'nom', 'adresse', 'code_postal', 'ville', 'pays') as $obligatoire) {
      if(!_request($obligatoire)) {
        if($type == "adhesion") {
          $erreurs[$obligatoire] = "Ce champ est obligatoire pour les adhésions";
        }
        else {
          $erreurs[$obligatoire] = 'Ce champ est obligatoire (reçu fiscal demandé)';
        }
      }
    }
  }

  if ($e = _request('courriel') AND !email_valide($e))
    $erreurs['courriel'] = _T('form_prop_indiquer_email');

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

  if ($e = _request('montant')) {
    if(!(ctype_digit($e)))
      $erreurs['montant'] = "Montant invalide";
    else {
      $type_saisie = lire_config("souscription/${type}_type_sasie");

      /* On ne vérifie strictement la valeur du montant que si on
       * n'utilise pas le type de saisie « entrée libre » (input) pour
       * le montant. */
      if(($type_saisie != "input") AND !array_key_exists($e, lire_config("souscription/${type}_montants")))
        $erreurs['montant'] = "Le montant spécifié est invalide";
    }
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
    $redirect = generer_url_public("payer", "id_transaction=$id_transaction&transaction_hash=$hash", false, false);
    $ret['redirect'] = $redirect;
  }

  return $ret;
}

function verifier_campagne($id_souscription_campagne) {
  /* FIXME: vérifier que la campagne a bien le bon statut (ouverte, fermée, terminée, etc.) */

  /* Vérification de l'existance de la *campagne*, de son *statut* et de la *concordance du type* */
  if(intval($id_souscription_campagne)
     AND $t = sql_getfetsel('type_objectif', 'spip_souscription_campagnes', 'id_souscription_campagne='.intval($id_souscription_campagne)))
    {
      return true;
    }

  return false;
}
