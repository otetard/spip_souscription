<?php

// Ceci est un fichier langue de SPIP -- This is a SPIP language file
// Produit automatiquement par le plugin LangOnet a partir de la langue source fr
// Module: souscription
// Langue: fr
// Date: 28-05-2013 19:55:44
// Items: 72

if (!defined('_ECRIRE_INC_VERSION')) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(
	'explication_campagne_objectif' => 'Indiquer un montant en Euros pour des dons et un nombre pour des adhésions.',
	'explication_campagne_objectif_initial' => 'Permet d\'indiquer le niveau initial de la campagne. Ce champs peut être utilisé pour indiquer des dons ou adhésions n\'ayant pas été compatibilisées par le module de souscriptions.',
	'explication_campagne_objectif_limite' => "Permet de bloquer les nouvelles adhésions/dons pour cette campagne si l'objecif est attend (offre spéciale d'adhésion par exemple)",
	'explication_type_objectif' => 'Type de l\'objectif (influe le montant de l\'objectif)',
	'explication_configuration_specifique' => "Permet de configurer des niveaux spécifiques d'adhésion ou de dons pour cette campagne",

	'icone_creer_souscription_campagne' => 'Créer une campagne de souscription',
	'icone_modifier_souscription_campagne' => 'Modifier cette campagne',
	'info_1_souscription_campagne' => '1 campagne',
	'info_aucun_souscription_campagne' => 'Aucune campagne',
	'info_nb_souscription_campagnes' => '@nb@ campagnes',
	'info_type_objectif_dons' => '(objectif en Euros)',
	'info_type_objectif_adhesions' => '(objectif en nombre d\'adhésions)',


	'titre_logo_souscription_campagne' => 'Logo de cette campagne',
	'titre_souscription_campagne' => 'Campagne',
	'titre_souscription_campagnes' => 'Campagnes',

	'label_titre_campagne' => 'Titre de la campagne',
	'label_description' => 'Description de la campagne',
	'label_type_objectif' => 'Type d\'objectif',
	'label_type_objectif_dons' => 'Dons',
	'label_type_objectif_adhesions' => 'Adhésions',
	'label_objectif_oui_non' => 'Fixer un objectif à cette campagne',
	'label_objectif' => 'Niveau de l\'objectif',
	'label_objectif_initial' => 'Niveau initial de la campagne',
	'label_objectif_groupe' => 'Definition de l\'objectif de la campagne',
	'label_objectif_limite' => 'Fermer la campagne si l\'objectif est atteint',
	'label_configuration_specifique' => 'Activer une configuration spécifique',

	'info_objectif' => 'Objectif',
	'info_avancement' => 'Avancement',
	
	'erreur_objectif_initial_invalide' => "Valeur de l'objectif initial invalide",
	'erreur_objectif_initial_valeur' => "Valeur de l'objectif initial invalide",
	'erreur_objectif_initial_supperieur_objectif' => "Valeur de l'objectif initial suppérieur à l'objectif",
	'erreur_objectif_invalide' => "Type d'objectif invalide",
	'erreur_objectif_don_inactif' => "Les objectifs de type Dons ne sont pas activés dans la configuration.",
	'erreur_objectif_adhesion_inactif' => "Les objectifs de type Adhésions ne sont pas activés dans la configuration.",

);
?>
