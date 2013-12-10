<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_editer_souscription_dist($arg = null){

	if (is_null($arg)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}

	if (!$id_souscription = intval($arg)){
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
function souscription_inserer(){

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
function souscription_modifier($id_souscription, $set = false){
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
			'telephone',
			'id_souscription_campagne',
			'id_transaction',
			'type_souscription'),
		// black list
		array('statut', 'date'),
		// donnees eventuellement fournies
		$set
	);


	/* Récupération du nom du pays */
	$code_pays = _request('pays');
	$pays = sql_getfetsel(sql_multi("nom", $GLOBALS['spip_lang']), 'spip_pays', "code='$code_pays'");

	$c = array_merge($c,array("pays" => $pays));

	if ($err = objet_modifier_champs('souscription', $id_souscription, array(), $c))
		return $err;
}
