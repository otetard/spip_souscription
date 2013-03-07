!<?php

/**
 *  Fichier généré par la Fabrique de plugin v5
 *   le 2013-03-01 16:49:34
 *
 *  Ce fichier de sauvegarde peut servir à recréer
 *  votre plugin avec le plugin «Fabrique» qui a servi à le créer.
 *
 *  Bien évidemment, les modifications apportées ultérieurement
 *  par vos soins dans le code de ce plugin généré
 *  NE SERONT PAS connues du plugin «Fabrique» et ne pourront pas
 *  être recréées par lui !
 *
 *  La «Fabrique» ne pourra que régénerer le code de base du plugin
 *  avec les informations dont il dispose.
 *
**/

if (!defined("_ECRIRE_INC_VERSION")) return;

$data = array (
  'fabrique' => 
  array (
    'version' => 5,
  ),
  'paquet' => 
  array (
    'nom' => 'Souscription',
    'slogan' => '',
    'description' => 'Module de souscription pour Attac France et Basta!',
    'prefixe' => 'souscription',
    'version' => '1.0.0',
    'auteur' => 'Olivier Tétard',
    'auteur_lien' => 'http://olivier.miskin.fr',
    'licence' => 'GNU/GPL',
    'categorie' => 'communication',
    'etat' => 'dev',
    'compatibilite' => '[3.0.5;3.0.*]',
    'documentation' => '',
    'administrations' => 'on',
    'schema' => '1.0.0',
    'formulaire_config' => 'on',
    'formulaire_config_titre' => 'Configuration des modules de souscription',
    'fichiers' => 
    array (
      0 => 'autorisations',
      1 => 'fonctions',
      2 => 'options',
      3 => 'pipelines',
    ),
    'inserer' => 
    array (
      'paquet' => '',
      'administrations' => 
      array (
        'maj' => '',
        'desinstallation' => '',
        'fin' => '',
      ),
      'base' => 
      array (
        'tables' => 
        array (
          'fin' => '',
        ),
      ),
    ),
    'scripts' => 
    array (
      'pre_copie' => '',
      'post_creation' => '',
    ),
    'exemples' => '',
  ),
  'objets' => 
  array (
    0 => 
    array (
      'nom' => 'Dons',
      'nom_singulier' => 'Don',
      'genre' => 'masculin',
      'logo_variantes' => '',
      'table' => 'spip_souscription_dons',
      'cle_primaire' => 'id_souscription_don',
      'cle_primaire_sql' => 'bigint(21) NOT NULL',
      'table_type' => 'souscription_don',
      'champs' => 
      array (
        0 => 
        array (
          'nom' => 'Courriel',
          'champ' => 'courriel',
          'sql' => 'text NOT NULL DEFAULT \'\'',
          'caracteristiques' => 
          array (
            0 => 'editable',
            1 => 'versionne',
            2 => 'obligatoire',
          ),
          'recherche' => '',
          'saisie' => 'input',
          'explication' => '',
          'saisie_options' => '',
        ),
        1 => 
        array (
          'nom' => 'Nom',
          'champ' => 'nom',
          'sql' => 'text NOT NULL DEFAULT \'\'',
          'caracteristiques' => 
          array (
            0 => 'editable',
            1 => 'versionne',
          ),
          'recherche' => '',
          'saisie' => 'input',
          'explication' => '',
          'saisie_options' => '',
        ),
        2 => 
        array (
          'nom' => 'Prénom',
          'champ' => 'prenom',
          'sql' => 'text NOT NULL DEFAULT \'\'',
          'caracteristiques' => 
          array (
            0 => 'editable',
            1 => 'versionne',
          ),
          'recherche' => '',
          'saisie' => 'input',
          'explication' => '',
          'saisie_options' => '',
        ),
        3 => 
        array (
          'nom' => 'Code postal',
          'champ' => 'code_postal',
          'sql' => 'text NOT NULL DEFAULT \'\'',
          'caracteristiques' => 
          array (
            0 => 'editable',
            1 => 'versionne',
          ),
          'recherche' => '',
          'saisie' => 'input',
          'explication' => '',
          'saisie_options' => '',
        ),
        4 => 
        array (
          'nom' => 'Adresse',
          'champ' => 'adresse',
          'sql' => 'text NOT NULL DEFAULT \'\'',
          'caracteristiques' => 
          array (
            0 => 'editable',
            1 => 'versionne',
          ),
          'recherche' => '',
          'saisie' => 'textarea',
          'explication' => '',
          'saisie_options' => '',
        ),
        5 => 
        array (
          'nom' => 'Ville',
          'champ' => 'ville',
          'sql' => 'text NOT NULL DEFAULT \'\'',
          'caracteristiques' => 
          array (
            0 => 'editable',
            1 => 'versionne',
          ),
          'recherche' => '',
          'saisie' => 'input',
          'explication' => '',
          'saisie_options' => '',
        ),
        6 => 
        array (
          'nom' => 'Envoyer un reçu fiscal',
          'champ' => 'recu_fiscal',
          'sql' => 'int(1) NOT NULL DEFAULT 0',
          'caracteristiques' => 
          array (
            0 => 'editable',
            1 => 'versionne',
          ),
          'recherche' => '',
          'saisie' => 'oui_non',
          'explication' => '',
          'saisie_options' => '',
        ),
        7 => 
        array (
          'nom' => 'Souhaite être informé',
          'champ' => 'envoyer_info',
          'sql' => 'int(2) NOT NULL DEFAULT 0',
          'caracteristiques' => 
          array (
            0 => 'editable',
            1 => 'versionne',
          ),
          'recherche' => '',
          'saisie' => 'oui_non',
          'explication' => 'Souhaite recevoir des informations publiques',
          'saisie_options' => '',
        ),
        8 => 
        array (
          'nom' => 'Identifiant de la transaction',
          'champ' => 'id_transaction',
          'sql' => 'int(11) NOT NULL DEFAULT 0',
          'caracteristiques' => 
          array (
            0 => 'editable',
            1 => 'versionne',
          ),
          'recherche' => '',
          'saisie' => '',
          'explication' => '',
          'saisie_options' => '',
        ),
      ),
      'champ_titre' => 'nom',
      'champ_date' => 'date_souscription',
      'statut' => '',
      'chaines' => 
      array (
        'titre_objets' => 'Dons',
        'titre_objet' => 'Don',
        'info_aucun_objet' => 'Aucun don',
        'info_1_objet' => 'Un don',
        'info_nb_objets' => '@nb@ dons',
        'icone_creer_objet' => 'Créer un don',
        'icone_modifier_objet' => 'Modifier ce don',
        'titre_logo_objet' => 'Logo de ce don',
        'titre_langue_objet' => 'Langue de ce don',
        'titre_objets_rubrique' => 'Dons de la rubrique',
        'info_objets_auteur' => 'Les dons de cet auteur',
        'retirer_lien_objet' => 'Retirer ce don',
        'retirer_tous_liens_objets' => 'Retirer tous les dons',
        'ajouter_lien_objet' => 'Ajouter ce don',
        'texte_ajouter_objet' => 'Ajouter un don',
        'texte_creer_associer_objet' => 'Créer et associer un don',
        'texte_changer_statut_objet' => 'Ce don est :',
      ),
      'table_liens' => 'on',
      'roles' => '',
      'auteurs_liens' => '',
      'vue_auteurs_liens' => '',
      'echafaudages' => 
      array (
        0 => 'prive/squelettes/contenu/objets.html',
        1 => 'prive/objets/infos/objet.html',
        2 => 'prive/squelettes/contenu/objet.html',
      ),
      'autorisations' => 
      array (
        'objet_creer' => 'toujours',
        'objet_voir' => 'webmestre',
        'objet_modifier' => 'webmestre',
        'objet_supprimer' => 'webmestre',
        'associerobjet' => 'webmestre',
      ),
      'boutons' => 
      array (
        0 => 'menu_edition',
        1 => 'outils_rapides',
      ),
      'saisies' => 
      array (
        0 => 'objets',
      ),
    ),
  ),
  'images' => 
  array (
    'paquet' => 
    array (
      'logo' => 
      array (
        0 => 
        array (
          'extension' => '',
          'contenu' => '',
        ),
      ),
    ),
    'objets' => 
    array (
      0 => 
      array (
      ),
    ),
  ),
);

?>