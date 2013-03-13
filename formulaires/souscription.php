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
                                                      $hidden='')
{
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

  return array('montant' => '',
               'courriel' => '',
               'recu_fiscal' => '',
               'envoyer_info' => 'on',
               'prenom' => '',
               'nom' => '',
               'adresse' => '',
               'code_postal' => '',
               'ville' => '',
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
function formulaires_souscription_verifier_dist($id_souscription_campagne)
{
  $campagne = _request('id_souscription_campagne');

  $erreurs = formulaires_editer_objet_verifier('souscription', 'new',
                                               array('courriel',
                                                     'montant',
                                                     'id_souscription_campagne'));

  if(!verifier_campagne($id_souscription_campagne)) {
    $erreurs['message_erreur'] = "La campagne à laquelle est associée cette souscription est invalide";
  }

  if(_request('recu_fiscal')) {
    foreach(array('prenom', 'nom', 'adresse', 'code_postal', 'ville') as $obligatoire) {
      if(!_request($obligatoire)) {
        $erreurs[$obligatoire] = 'Ce champ est obligatoire (reçu fiscal demandé)';
      }
    }
  }

  if(!$id_souscription_campagne || intval($id_souscription_campagne) != intval($campagne)) {
      $erreurs['message_erreur'] = "Campagne invalide";
  }
    
  /* La campagne doit être valide (définie dans la base) et doit
   * accepter les dons. */
  $type = sql_getfetsel("type_objectif",
                        "spip_souscription_campagnes",
                        "id_souscription_campagne=$id_souscription_campagne");

  if(!$type || !in_array($type, array("don", "adhesion", "abonnement")))
    $erreurs['message_erreur'] = "Type de souscription invalide";

  /* Le champ 'type' (hidden) doit être le même que celui défini dans
   * la campagne. */
  if(_request('type_souscription') != $type)
    $erreurs['message_erreur'] = "Type de souscription invalide: " . _request('type_souscription');

  if ($e = _request('courriel') AND !email_valide($e))
    $erreurs['courriel'] = _T('form_prop_indiquer_email');

  if ($e = _request('montant') AND !(ctype_digit($e))) {
    /* FIXME: vérifier que le montant est compris dans les bornes. */
    $erreurs['montant'] = "Montant invalide";
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
function formulaires_souscription_traiter_dist($id_souscription_campagne)
{

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
  $row = sql_fetsel("transaction_hash,id_transaction", /* $select */
                    "spip_transactions LEFT JOIN spip_souscriptions USING(id_transaction)", /* $from */
                    "id_souscription=".$ret['id_souscription']); /* $where */

  print_r($row);

  if(!$row) {
    $ret['message_erreur'] = "Echec creation de la transaction";
  }
  else {
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
