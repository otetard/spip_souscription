<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_exporter_souscriptions_dist($arg=null) {
  /*
   * $arg contient les différents arguments, séparés par des '/'. Une
   * fois passés dans la fonctions split, il se présente de la manière
   * suivante :
   *
   *   argument en position 1 : 'paye', 'commande', 'erreur' ou 'tous'
   *   argument en position 2 : type de souscription ('dons',
   *                            'adhesion')
   *   argument en position 3 : identifiant de la campagne
   *   argument en position 4 : date de début (au format timestamp)
   *   argument en position 5 : date de fin (au format timestamp)
   */

  /* FIXME: améliorer la jointure... */

  if (is_null($arg)) {
    $securiser_action = charger_fonction('securiser_action', 'inc');
    $arg = $securiser_action();
  }

  /* Vérification des droits de l'utilisateur. */
  if(!autoriser("exporter", "souscription", '')) {
    include_spip('inc/minipres');
    echo minipres();
    exit;
  }

  try {
    $arg = explode("/", $arg);

    if(sizeof($arg) != 5)
      throw new Exception();

    $type_souscription = $arg[0];
    if($type_souscription && !in_array($type_souscription, array('don', 'adhesion')))
      throw new Exception();

    $statut = $arg[1];
    if($statut && !in_array($statut, array('paye', 'commande', 'erreur')))
      throw new Exception();

    $id_campagne = $arg[2];
    if($id_campagne && !ctype_digit($id_campagne))
      throw new Exception();

    $date_debut = $arg[3];
    $date_fin = $arg[4];

    if(($date_debut && !ctype_digit($date_debut)) || ($date_fin && !ctype_digit($date_fin)))
      throw new Exception();
  }
  catch(Exception $e) {
    include_spip('inc/minipres');
    echo minipres();
    exit;
  }

  /* Préparation de la requête */
  $select = "id_souscription, courriel, type_souscription,"
    ."montant, reglee, spip_transactions.statut, date_paiement, mode, autorisation_id,"
    ."nom, prenom, adresse, code_postal, ville, pays, telephone, recu_fiscal, envoyer_info, date_souscription,"
    ."spip_souscription_campagnes.id_souscription_campagne, titre";
  $from = "spip_souscriptions LEFT JOIN spip_transactions USING(id_transaction) LEFT JOIN spip_souscription_campagnes USING(id_souscription_campagne)";

  $where = array();
  if($type_souscription)
    $where[] = "type_souscription='$type_souscription'";

  if($statut) {
    if($statut == "paye")
      $where[] = "reglee='oui'";
    elseif($statut == "commande")
      $where[] = "spip_transactions.statut='commande'";
    elseif($statut == "erreur")
      $where[] = "spip_transactions.statut like 'echec'";
  }

  if($id_campagne)
    $where[] = "spip_souscription_campagnes.id_souscription_campagne = '$id_campagne'";

  if($date_debut)
    $where[] = "date_souscription > '" . date ("Y-m-d 00:00:00", $date_debut) . "'";

  if($date_fin)
    $where[] = "date_souscription < '" . date ("Y-m-d 23:59:59", $date_fin) . "'";

  $row = sql_select($select, $from, $where);

  $entete = array("ID du don",
                  "Courriel",
                  "Type de souscription",
                  "Montant",
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

  $exporter_csv("souscriptions", $row, ',', $entete);
  exit();
}
