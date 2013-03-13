<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_exporter_souscriptions_dist($arg=null) {

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

  if($arg == "payes") {
    $row = sql_select("id_souscription, courriel,"
                      ."montant, reglee, spip_transactions.statut, date_paiement, mode, autorisation_id,"
                      ."nom, prenom, code_postal, ville, recu_fiscal, envoyer_info, date_souscription,"
                      ."id_souscription_campagne, titre",
                      "spip_souscriptions LEFT JOIN spip_transactions USING(id_transaction) LEFT JOIN spip_souscription_campagnes USING(id_souscription_campagne) ",
                      "reglee='oui'");
  }
  elseif($arg == "tous") {
    $row = sql_select("id_souscription, courriel,"
                      ."montant, reglee, spip_transactions.statut, date_paiement, mode, autorisation_id,"
                      ."nom, prenom, code_postal, ville, recu_fiscal, envoyer_info, date_souscription,"
                      ."id_souscription_campagne, titre",
                      "spip_souscriptions LEFT JOIN spip_transactions USING(id_transaction) LEFT JOIN spip_souscription_campagnes USING(id_souscription_campagne)");
  }
  else {
    include_spip('inc/minipres');
    echo minipres("Argument invalide");
    exit;    
  }

  $entete = array("ID du don",
                  "Courriel",
                  "Montant",
                  "Reglée",
                  "Statut",
                  "Date de paiement",
                  "Mode de paiement",
                  "ID de l'autorisation",
                  "Nom",
                  "Prénom",
                  "Code Postal",
                  "Ville",
                  "Souhaite reçu fiscal",
                  "Souhaite être informé",
                  "Date don",
                  "ID Campagne",
                  "Titre de la campagne");

  /* Utilisation de la fonction exporter_csv de Bonux */
  $exporter_csv = charger_fonction('exporter_csv', 'inc/', true);

  $exporter_csv("souscriptions_${arg}", $row, ',', $entete);
  exit();
}
