<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_exporter_souscriptions_dist($arg=null) {
  /*
   * $arg contient les différents arguments, séparés par des '/'. Une
   * fois passés dans la fonctions split, il se présente de la manière
   * suivante :
   *   argument en position 1 : 'paye' ou 'tous'
   *   argument en position 2 : type de souscription (dons, adhesion)
   */

  /* FIXME: permettre de selectionner les exports */
  /* FIXME: améliorer la jointure... */

  if (is_null($arg)) {
    $securiser_action = charger_fonction('securiser_action', 'inc');
    $arg = $securiser_action();
  }

  /* Vérification des droits de l'utilisateur. */
  if(!autoriser("exporter", "souscriptiondon", '')) {
    include_spip('inc/minipres');
    echo minipres();
    exit;
  }

  $arg = explode("/", $arg);

  $type_statut = $arg[0];
  $type_souscription = $arg[1];

  /* Préparation de la requête */
  $select = "id_souscription, courriel, type_souscription,"
    ."montant, reglee, spip_transactions.statut, date_paiement, mode, autorisation_id,"
    ."nom, prenom, adresse, code_postal, ville, pays, telephone, recu_fiscal, envoyer_info, date_souscription,"
    ."spip_souscription_campagnes.id_souscription_campagne, titre";
  $from = "spip_souscriptions LEFT JOIN spip_transactions USING(id_transaction) LEFT JOIN spip_souscription_campagnes USING(id_souscription_campagne)";

  $where = array();
  if($type_souscription)
    $where[] = "type_souscription='$type_souscription'";
  else
    $type_souscription = "tous";


  if($type_statut == "payes") {
    $where[] = "reglee='oui'";
  }
  elseif($type_statut == "tous") {
    /* Afficher toutes les transactions du type demandé */
  }
  else {
    include_spip('inc/minipres');
    echo minipres("Argument invalide");
    exit;
  }

  $row = sql_select($select, $from, $where);

  $entete = array("ID du don",
                  "Courriel",
                  "Montant",
                  "Type de souscription",
                  "Reglée",
                  "Statut",
                  "Date de paiement",
                  "Mode de paiement",
                  "ID de l'autorisation",
                  "Nom",
                  "Prénom",
                  "Adresse",
                  "Code Postal",
                  "Ville",
                  "Pays",
                  "Téléphone",
                  "Souhaite reçu fiscal",
                  "Souhaite être informé",
                  "Date don",
                  "ID Campagne",
                  "Titre de la campagne");

  /* Utilisation de la fonction exporter_csv de Bonux */
  $exporter_csv = charger_fonction('exporter_csv', 'inc/', true);

  $exporter_csv("souscriptions_${type_souscription}_${type_statut}", $row, ',', $entete);
  exit();
}
