<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_editer_souscription_dist($arg=null) {

  if (is_null($arg)) {
    $securiser_action = charger_fonction('securiser_action', 'inc');
    $arg = $securiser_action();
  }

  if (!$id_souscription = intval($arg)) {
    $id_souscription = souscription_inserer();
  }

  if (!$id_souscription)
    return array(0, '');

  $err = souscription_modifier($id_souscription);

  return array($id_souscription, $err);
}

/**
 * Inserer une nouvelle souscription en en base.
 *
 * @return bool
 */
function souscription_inserer() {

  $champs = array('date_souscription' => date('Y-m-d H:i:s'));
	
  // Envoyer aux plugins
  $champs = pipeline('pre_insertion',
                     array('args' => array('table' => 'spip_souscriptions'),
                           'data' => $champs)
                     );

  $id_souscription = sql_insertq("spip_souscriptions", $champs);

  pipeline('post_insertion',
           array('args' => array('table' => 'spip_souscriptions',
                                 'id_objet' => $id_souscription),
                 'data' => $champs)
           );

  return $id_souscription;
}

/**
 * Modifier une souscription
 *
 * $c est un contenu (par defaut on prend le contenu via _request())
 *
 * @param int $id_souscription
 * @param array|bool $set
 * @return string
 */
function souscription_modifier($id_souscription, $set=false) {
  include_spip('inc/modifier');

  $c = collecter_requests(
                          // white list
                          array('courriel',
                                'recu_fiscal',
                                'envoyer_info',
                                'informer_comite_local',
                                'prenom',
                                'nom',
                                'adresse',
                                'code_postal',
                                'ville',
                                'pays',
                                'id_souscription_campagne',
                                'type_souscription'),
                          // black list
                          array('statut', 'date'),
                          // donnees eventuellement fournies
                          $set
                          );


  $inserer_transaction = charger_fonction('inserer_transaction', 'bank');
  $id_transaction = $inserer_transaction(_request('montant'),
                                         '', /* montant_ht */
                                         '', /* id_auteur */
                                         $id_souscription, /* auteur_id => id_souscription */
                                         _request('courriel'));

  if(!$id_transaction) {
    return "Identifiant de transaction introuvable..."; /* FIXME: à rendre traduisible. */
  }

  $c = array_merge($c, array("id_transaction" => $id_transaction));

  if($err = objet_modifier_champs('souscription', $id_souscription, array(), $c))
    return $err;
}
