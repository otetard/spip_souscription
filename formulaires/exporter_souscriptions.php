<?php
/**
 * Gestion du formulaire de d'export des souscriptions
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Formulaires
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function formulaires_exporter_souscriptions_charger_dist($type_souscription="tous") {

  if (!autoriser('exporter','souscription'))
    return false;

  return array('exporter_type_souscription' => $type_souscription,
               'exporter_statut' => "tous",
               'exporter_campagne' => "tous",
               'exporter_date_debut' => '',
               'exporter_date_fin' => '');
}

function formulaires_exporter_souscriptions_verifier_dist($type_souscription="tous") {
  /*
   * Note : la vérification du format des données est fait dans la
   * fonction action (sauf pour les dates)
   */

  $erreurs = array();

  $verifier = charger_fonction('verifier', 'inc/');
  if($e = _request('exporter_date_debut') && $err = $verifier(_request('exporter_date_debut'), 'date'))
    $erreurs['exporter_date_debut'] = $err;

  if($e = _request('exporter_date_fin') && $err = $verifier(_request('exporter_date_fin'), 'date'))
    $erreurs['exporter_date_fin'] = $err;

  return $erreurs;
}

function formulaires_exporter_souscriptions_traiter_dist($type_souscription="tous") {
  $type_souscription = _request('exporter_type_souscription');
  $statut = _request('exporter_statut');
  $campagne = _request('exporter_campagne');

  $verifier = charger_fonction('verifier', 'inc/');
  $date = "";
  $verifier(_request('exporter_date_debut'), 'date', array('normaliser' => 'datetime'), $date_debut);
  $verifier(_request('exporter_date_fin'), 'date', array('normaliser' => 'datetime'), $date_fin);

  /* Construction de l'URL spéciale pour l'action d'exportation. */
  $arg = sprintf("%s/%s/%s/%s/%s",
                 $type_souscription == 'tous' ? '' : $type_souscription,
                 $statut == 'tous' ? '' : $statut,
                 $campagne == 'tous' ? '' : $campagne,
                 $date_debut ? strtotime($date_debut) : "",
                 $date_fin ? strtotime($date_fin) : "");

  include_spip('inc/actions');
  $redirect = generer_action_auteur('exporter_souscriptions', $arg);

  return array('redirect' => $redirect);
}
