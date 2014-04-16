<?php
/**
 * Fichier gérant l'installation et désinstallation du plugin Souscription
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Installation
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


/**
 * Fonction d'installation et de mise à jour du plugin Souscription.
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @param string $version_cible
 *     Version du schéma de données dans ce plugin (déclaré dans paquet.xml)
 * @return void
 **/
function souscription_upgrade($nom_meta_base_version, $version_cible){
	$maj = array();

	$maj['create'] = array(
		array('maj_tables',	array('spip_souscriptions','spip_souscription_campagnes'))
	);
	$maj['0.1'] = array(
		array('sql_alter', "TABLE spip_souscriptions ADD informer_comite_local varchar(3) NOT NULL DEFAULT ''")
	);

	$maj['0.2'] = array(
		array('sql_alter', "TABLE spip_souscriptions ADD pays text NOT NULL DEFAULT ''")
	);

	$maj['0.3'] = array(
		array('sql_alter', "TABLE spip_souscriptions ADD telephone text NOT NULL DEFAULT ''")
	);

	$maj['0.4'] = array(
		array('sql_alter', "TABLE spip_souscription_campagnes ADD objectif_limiter varchar(3) NOT NULL DEFAULT ''")
	);

	$maj['0.5'] = array(
		array('sql_alter', "TABLE spip_souscription_campagnes ADD configuration_specifique varchar(3) NOT NULL DEFAULT ''"),
		array('sql_alter', "TABLE spip_souscription_campagnes ADD type_saisie varchar(255) NOT NULL DEFAULT ''"),
		array('sql_alter', "TABLE spip_souscription_campagnes ADD montants text NOT NULL DEFAULT ''")
	);

	$maj['0.6'] = array(array('maj_configuration_montants'));

	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}

/* Fonction permettant de changer le format des montants globaux pour
 * le plugin souscription. Les montants étaient stockés sous la forme
 * d'un array() sérialisés. Il sont désormais stockés dans leur format
 * chaine de caractères. */
function maj_configuration_montants(){
	foreach (array('adhesion_montants', 'don_montants') as $cfg){
		$cle_cfg = "souscription/${cfg}";

		$montants_orig = lire_config($cle_cfg);

		$montants = "";
		foreach ($montants_orig as $prix => $description){
			$montants .= $prix . "|" . $description . "\n";
		}

		ecrire_config($cle_cfg, $montants);
	}
}

/**
 * Fonction de désinstallation du plugin Souscription.
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @return void
 **/
function souscription_vider_tables($nom_meta_base_version){

	sql_drop_table("spip_souscriptions");
	sql_drop_table("spip_souscription_campagnes");

	/* Nettoyer les versionnages et forums */
	sql_delete("spip_versions", sql_in("objet", array('souscription')));
	sql_delete("spip_versions_fragments", sql_in("objet", array('souscription')));
	sql_delete("spip_forum", sql_in("objet", array('souscription')));

	effacer_meta($nom_meta_base_version);
}

?>
