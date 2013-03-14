<?php
/**
 * Déclarations relatives à la base de données
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Déclaration des alias de tables et filtres automatiques de champs
 *
 * @pipeline declarer_tables_interfaces
 * @param array $interfaces
 *     Déclarations d'interface pour le compilateur
 * @return array
 *     Déclarations d'interface pour le compilateur
 */
function souscription_declarer_tables_interfaces($interfaces) {
  $interfaces['table_des_tables']['souscriptions'] = 'souscriptions';
  $interfaces['table_des_tables']['souscription_campagnes'] = 'souscription_campagnes';

  return $interfaces;
}


/**
 * Déclaration des objets éditoriaux
 *
 * @pipeline declarer_tables_objets_sql
 * @param array $tables
 *     Description des tables
 * @return array
 *     Description complétée des tables
 */
function souscription_declarer_tables_objets_sql($tables) {

  $tables['spip_souscriptions'] =
    array('type' => 'souscription',
          'principale' => "oui",
          'table_objet_surnoms' => array('souscription'), // table_objet('souscription') => 'souscription'
          'field'=> array("id_souscription" => "bigint(21) NOT NULL",
                          "id_transaction"      => "bigint(21) NOT NULL DEFAULT 0",
                          "id_souscription_campagne" => "bigint(21) NOT NULL DEFAULT 0",
                          "courriel"            => "text NOT NULL DEFAULT ''",
                          "nom"                 => "text NOT NULL DEFAULT ''",
                          "prenom"              => "text NOT NULL DEFAULT ''",
                          "code_postal"         => "text NOT NULL DEFAULT ''",
                          "adresse"             => "text NOT NULL DEFAULT ''",
                          "ville"               => "text NOT NULL DEFAULT ''",
                          "recu_fiscal"         => "varchar(3) NOT NULL DEFAULT ''",
                          "type_souscription"   => "varchar(255) NOT NULL DEFAULT ''",
                          "informer_comite_local" => "varchar(3) NOT NULL DEFAULT ''",
                          "envoyer_info"        => "varchar(3) NOT NULL DEFAULT ''",
                          "date_souscription "  => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
                          "maj"                 => "TIMESTAMP"
                          ),
          'key' => array("PRIMARY KEY"          => "id_souscription",
                         "KEY id_transaction"   => "id_transaction",
                         "KEY id_souscription_campagne" => "id_souscription_campagne"),
          'titre' => "nom AS titre, '' AS lang",
          'date' => "date_souscription",
          'champs_editables'  => array('courriel', 'nom', 'prenom', 'code_postal', 'adresse', 'ville', 'recu_fiscal', 'envoyer_info'),
          'champs_versionnes' => array('courriel', 'nom', 'prenom', 'code_postal', 'adresse', 'ville', 'recu_fiscal', 'envoyer_info'),
          'rechercher_champs' => array(),
          'join' => array("id_transaction" => "id_transaction"),
          /* 'join' => array("id_souscription_campagne" => "id_souscription_campagne"), */
          /* 'tables_jointures'  => array('souscription_campagnes'), */
          'tables_jointures'  => array('spip_transactions'),
          );

  $tables['spip_souscription_campagnes'] =
    array('type' => 'souscription_campagne',
          'principale' => "oui",
          'table_objet_surnoms' => array('souscriptioncampagne'),
          'field'=> array("id_souscription_campagne" => "bigint(21) NOT NULL",
                          "objectif"                 => "int(11) NOT NULL DEFAULT 0",
                          "objectif_initial"         => "int(11) NOT NULL DEFAULT 0",
                          "type_objectif"            => "varchar(255) NOT NULL DEFAULT 0",
                          "titre"                    => "text NOT NULL DEFAULT ''",
                          "texte"                    => "longtext NOT NULL DEFAULT ''",
                          "statut"                   => "varchar(255) NOT NULL DEFAULT 0",
                          "date"                     => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
                          "maj"                      => "TIMESTAMP"
                          ),
          'key' => array("PRIMARY KEY"               => "id_souscription_campagne",
                         "KEY id_souscription_campagne" => "id_souscription_campagne",
                         ),
          'titre' => "titre AS titre, '' AS lang",
          'date' => "date",
          'champs_editables'  => array('objectif', 'titre', 'texte', 'objectif_initial', 'type_objectif'),
          'champs_versionnes' => array('objectif', 'titre', 'texte', 'objectif_initial', 'type_objectif'),
          'rechercher_champs' => array(),
          /* 'tables_jointures'  => array('spip_souscription_campagnes'), */
          );

  return $tables;
}
