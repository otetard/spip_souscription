<?php
/**
 * Déclarations relatives à la base de données
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Base
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
function souscription_declarer_tables_interfaces($interfaces){
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
function souscription_declarer_tables_objets_sql($tables){

	$tables['spip_souscriptions'] =
		array('type' => 'souscription',
			'principale' => "oui",
			'table_objet_surnoms' => array('souscription'), // table_objet('souscription') => 'souscription'
			'field' => array(
				"id_souscription" => "bigint(21) NOT NULL",
				"id_souscription_campagne" => "bigint(21) NOT NULL DEFAULT 0",
				"id_transaction_echeance" => "bigint(21) NOT NULL DEFAULT 0",
				"montant" 	=> "varchar(25) NOT NULL DEFAULT ''", // montant en euros
				"courriel" => "text NOT NULL DEFAULT ''",
				"nom" => "text NOT NULL DEFAULT ''",
				"prenom" => "text NOT NULL DEFAULT ''",
				"code_postal" => "text NOT NULL DEFAULT ''",
				"adresse" => "text NOT NULL DEFAULT ''",
				"ville" => "text NOT NULL DEFAULT ''",
				"pays" => "text NOT NULL DEFAULT ''",
				"telephone" => "text NOT NULL DEFAULT ''",
				"recu_fiscal" => "varchar(3) NOT NULL DEFAULT ''",
				"type_souscription" => "varchar(255) NOT NULL DEFAULT ''",
				"informer_comite_local" => "varchar(3) NOT NULL DEFAULT ''",
				"envoyer_info" => "varchar(3) NOT NULL DEFAULT 'off'",
				"date_souscription" => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"date_echeance" => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"date_fin" => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"abonne_uid" => "varchar(255) NOT NULL DEFAULT ''",
				"abo_statut" => "varchar(255) NOT NULL DEFAULT 'non'",
				"abo_fin_raison" => "varchar(255) NOT NULL DEFAULT ''",
				"montant_cumul" 	=> "varchar(25) NOT NULL DEFAULT ''", // montant en euros du total des versements pour cette souscription
				"maj" => "TIMESTAMP"
			),
			'key' => array(
				"PRIMARY KEY" => "id_souscription",
				"KEY id_transaction_echeance" => "id_transaction_echeance",
				"KEY id_souscription_campagne" => "id_souscription_campagne"
			),
			'titre' => "nom AS titre, '' AS lang",
			'date' => "date_souscription",
			'champs_editables' => array('id_souscription_campagne','id_transaction','courriel', 'nom', 'prenom', 'code_postal', 'adresse', 'ville', 'pays', 'telephone','recu_fiscal', 'type_souscription', 'envoyer_info','informer_comite_local','montant','date_echeance','date_fin','abonne_uid','abo_statut','abo_fin_raison','montant_cumul'),
			'champs_versionnes' => array('courriel', 'nom', 'prenom', 'code_postal', 'adresse', 'ville', 'pays', 'telephone','recu_fiscal', 'type_souscription', 'envoyer_info','montant'),
			'rechercher_champs' => array(
				"id_souscription" => 1,
				"courriel" => 1,
				"nom" => 1,
				"prenom" => 1,
				"adresse" => 1,
				"ville" => 1,
				"code_postal" => 1,
				"abonne_uid" => 1,
			),
			'tables_jointures' => array(
				'spip_souscriptions_liens',
				'id_transaction'=>'spip_souscriptions_liens',
				'spip_transactions',
			),
		);
	$tables['spip_transactions']['tables_jointures'][] = 'spip_souscriptions_liens';
	$tables['spip_transactions']['tables_jointures'][] = 'spip_souscriptions';

	$tables['spip_souscription_campagnes'] =
		array('type' => 'souscription_campagne',
			'principale' => "oui",
			'table_objet_surnoms' => array('souscriptioncampagne'),
			'field' => array(
				"id_souscription_campagne" => "bigint(21) NOT NULL",
				"objectif" => "int(11) NOT NULL DEFAULT 0",
				"objectif_initial" => "int(11) NOT NULL DEFAULT 0",
				"type_objectif" => "varchar(255) NOT NULL DEFAULT 0",
				"objectif_limiter" => "varchar(3) NOT NULL DEFAULT ''",
				"titre" => "text NOT NULL DEFAULT ''",
				"texte" => "longtext NOT NULL DEFAULT ''",
				"configuration_specifique" => "varchar(3) NOT NULL DEFAULT ''",
				"type_saisie" => "varchar(255) NOT NULL DEFAULT ''",
				"montants" => "text NOT NULL DEFAULT ''",
				"abo_type_saisie" => "varchar(255) NOT NULL DEFAULT ''",
				"abo_montants" => "text NOT NULL DEFAULT ''",
				"statut" => "varchar(255) NOT NULL DEFAULT 0",
				"date" => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"maj" => "TIMESTAMP"
			),
			'key' => array("PRIMARY KEY" => "id_souscription_campagne",
				"KEY id_souscription_campagne" => "id_souscription_campagne",
			),
			'titre' => "titre AS titre, '' AS lang",
			'date' => "date",
			'champs_editables' => array('objectif', 'titre', 'texte', 'objectif_initial', 'type_objectif', 'objectif_limiter', 'configuration_specifique', 'type_saisie', 'montants', 'abo_type_saisie', 'abo_montants'),
			'champs_versionnes' => array('objectif', 'titre', 'texte', 'objectif_initial', 'type_objectif', 'objectif_limiter', 'configuration_specifique', 'type_saisie', 'montants', 'abo_type_saisie', 'abo_montants'),
			'rechercher_champs' => array(),
		);

	return $tables;
}


/**
 * Table auxilaire spip_souscriptions_liens
 *
 * @param array $tables_auxiliaires
 * @return array
 */
function souscription_declarer_tables_auxiliaires($tables_auxiliaires){

	$spip_souscriptions_liens = array(
			"id_souscription"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"id_objet"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"objet"	=> "VARCHAR (25) DEFAULT '' NOT NULL"
	);

	$spip_souscriptions_liens_key = array(
			"PRIMARY KEY"		=> "id_souscription,id_objet,objet",
			"KEY id_souscription"	=> "id_souscription",
			"KEY id_objet"	=> "id_objet",
			"KEY objet"	=> "objet",
	);

	$tables_auxiliaires['spip_souscriptions_liens'] =
		array('field' => &$spip_souscriptions_liens, 'key' => &$spip_souscriptions_liens_key);

	return $tables_auxiliaires;
}