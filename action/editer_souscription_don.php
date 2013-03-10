<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_editer_souscription_don_dist($arg=null) {
  if (is_null($arg)){
    $securiser_action = charger_fonction('securiser_action', 'inc');
    $arg = $securiser_action();
  }

  if (!$id_souscription_don = intval($arg)){
    $id_souscription_don = don_inserer();
  }

  if (!$id_souscription_don)
    return array(0,'');

  $err = don_modifier($id_souscription_don);

  return array($id_souscription_don, $err);
}


/**
 * Inserer une nouvelle donation en en base
 *
 * @return bool
 */
function don_inserer() {

  $champs = array('date_souscription' => date('Y-m-d H:i:s'));
	
  // Envoyer aux plugins
  $champs = pipeline('pre_insertion',
                     array('args' => array('table' => 'spip_souscription_dons'),
                           'data' => $champs)
                     );

  $id_souscription_don = sql_insertq("spip_souscription_dons", $champs);

  pipeline('post_insertion',
           array('args' => array('table' => 'spip_souscription_dons',
                                 'id_objet' => $id_souscription_don),
                 'data' => $champs)
           );

  return $id_souscription_don;
}

/**
 * Modifier une donation
 *
 * $c est un contenu (par defaut on prend le contenu via _request())
 *
 * @param int $id_souscription_don
 * @param array|bool $set
 * @return string
 */
function don_modifier($id_souscription_don, $set=false) {
  include_spip('inc/modifier');

  $c = collecter_requests(
                          // white list
                          array('courriel',
                                'recu_fiscal',
                                'envoyer_info',
                                'prenom',
                                'nom',
                                'adresse',
                                'code_postal',
                                'ville',
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
                                         $id_souscription_don, /* auteur_id => id_souscription_don */
                                         _request('courriel'));

  if(!$id_transaction) {
    return "Identifiant de transaction introuvable..."; /* FIXME: à rendre traduisible. */
  }

  $c = array_merge($c, array("id_transaction" => $id_transaction));

  if($err = objet_modifier_champs('souscription_don', $id_souscription_don, array(), $c))
    return $err;
}
