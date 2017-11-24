<?php
/**
 * Export des souscriptions en CSV
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Action
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function action_exporter_souscriptions_dist($arg = null){
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

	if (is_null($arg)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}

	/* Vérification des droits de l'utilisateur. */
	if (!autoriser("exporter", "souscription", '')){
		include_spip('inc/minipres');
		echo minipres();
		exit;
	}

	try {
		$arg = explode("/", $arg);

		if (sizeof($arg)!=5)
			throw new Exception();

		$type_souscription = $arg[0];
		if ($type_souscription && !in_array($type_souscription, array('don', 'adhesion')))
			throw new Exception();

		$statut = $arg[1];
		if ($statut && !in_array($statut, array('paye', 'commande', 'erreur')))
			throw new Exception();

		$id_campagne = $arg[2];
		if ($id_campagne && !ctype_digit($id_campagne))
			throw new Exception();

		$date_debut = $arg[3];
		$date_fin = $arg[4];

		if (($date_debut && !ctype_digit($date_debut)) || ($date_fin && !ctype_digit($date_fin)))
			throw new Exception();
	} catch (Exception $e) {
		include_spip('inc/minipres');
		echo minipres();
		exit;
	}

	/* Préparation de la requête */
	$select = "S.id_souscription, S.courriel, S.type_souscription,S.abo_statut,"
		. "T.montant, T.reglee, T.statut, T.date_paiement, T.mode, T.autorisation_id,"
		. "S.nom, S.prenom, S.adresse, S.code_postal, S.ville, S.pays, S.telephone, S.recu_fiscal, S.envoyer_info, S.informer_comite_local, S.date_souscription,"
		. "C.id_souscription_campagne, C.titre";
	$from = "spip_souscriptions AS S
		LEFT JOIN spip_souscriptions_liens AS L ON (L.id_souscription=S.id_souscription)
		LEFT JOIN spip_transactions AS T ON (T.id_transaction=L.id_objet AND objet='transaction')
		LEFT JOIN spip_souscription_campagnes AS C ON (C.id_souscription_campagne = S.id_souscription_campagne)";

	$where = array();
	if ($type_souscription)
		$where[] = "S.type_souscription=".sql_quote($type_souscription);

	if ($statut){
		if ($statut=="paye")
			$where[] = "T.statut=" . sql_quote('ok') . " AND reglee=" . sql_quote('oui');
		elseif ($statut=="commande")
			$where[] = "T.statut=" . sql_quote('commande');
		elseif ($statut=="erreur")
			$where[] = "T.statut LIKE " . sql_quote('echec%');
	}

	if ($id_campagne)
		$where[] = "C.id_souscription_campagne=" . intval($id_campagne);

	if ($date_debut)
		$where[] = "T.date_transaction>" . sql_quote(date("Y-m-d 00:00:00", $date_debut));

	if ($date_fin)
		$where[] = "T.date_transaction<" . sql_quote(date("Y-m-d 23:59:59", $date_fin));

	$res = sql_select($select, $from, $where);

	$entete = array(
		_T("souscription:label_exporter_entete_id_don"),
		_T("souscription:label_exporter_entete_courriel"),
		_T("souscription:label_exporter_entete_type_souscription"),
		_T("souscription:label_exporter_entete_statut_don"),
		_T("souscription:label_exporter_entete_montant"),
		_T("souscription:label_exporter_entete_reglee"),
		_T("souscription:label_exporter_entete_statut"),
		_T("souscription:label_exporter_entete_date_paiement"),
		_T("souscription:label_exporter_entete_mode_paiement"),
		_T("souscription:label_exporter_entete_id_autorisation"),
		_T("souscription:label_exporter_entete_nom"),
		_T("souscription:label_exporter_entete_prenom"),
		_T("souscription:label_exporter_entete_adresse"),
		_T("souscription:label_exporter_entete_code_postal"),
		_T("souscription:label_exporter_entete_ville"),
		_T("souscription:label_exporter_entete_pays"),
		_T("souscription:label_exporter_entete_telephone"),
		_T("souscription:label_exporter_entete_recu_fiscal"),
		_T("souscription:label_exporter_entete_informer"),
		_T("souscription:label_exporter_entete_informer_comite_local"),
		_T("souscription:label_exporter_entete_date_don"),
		_T("souscription:label_exporter_entete_id_campagne"),
		_T("souscription:label_exporter_entete_titre_campagne")
	);
	include_spip('inc/charsets');
	$entete = array_map('unicode2charset',$entete);

	/* Utilisation de la fonction exporter_csv de Bonux */
	$exporter_csv = charger_fonction('exporter_csv', 'inc/', true);

	$titre = _T("souscription:titre_souscriptions")."-".$GLOBALS['meta']['nom_site'];
	if ($id_campagne)
		$titre .= "-Campagne$id_campagne";
	if ($date_debut)
		$titre .= "-From".date("Ymd", $date_debut);
	if ($date_fin)
		$titre .= "-To".date("Ymd", $date_fin);
	$titre .= "-".date('Ymd');
	$exporter_csv($titre, $res, ',', $entete);
	exit();

}
