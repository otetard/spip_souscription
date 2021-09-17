<?php
/**
 * Gestion du formulaire de d'édition de souscription
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Formulaires
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/config');


/**
 * Chargement du formulaire d'édition de souscription
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @uses formulaires_editer_objet_charger()
 *
 * @param int$id_souscription_campagne
 *     Identifiant de la campagne de souscription
 * @return array
 *     Environnement du formulaire
 */
function formulaires_souscription_charger_dist($id_souscription_campagne){

	if (!verifier_campagne($id_souscription_campagne))
		return false;

	/* Récupération des information à propos de la campagne */
	$campagne = sql_fetsel("*","spip_souscription_campagnes","id_souscription_campagne=".intval($id_souscription_campagne));

	$type = $campagne['type_objectif'];

	$recu_fiscal = "off";
	if ($type=="adhesion")
		$recu_fiscal = "on";

	if ($campagne['configuration_specifique']!=='on'){
		$montant_type = lire_config("souscription/{$type}_type_saisie", 'input');
		$montant_datas = lire_config("souscription/${type}_montants", '');
		$abo_montant_type = lire_config("souscription/{$type}_abo_type_saisie", 'none');
		$abo_montant_datas = lire_config("souscription/${type}_abo_montants", '');
	} else {
		$montant_type = $campagne['type_saisie'];
		$montant_datas = $campagne['montants'];
		$abo_montant_type = $campagne['abo_type_saisie'];
		$abo_montant_datas = $campagne['abo_montants'];
	}

	$montant_label = lire_config("souscription/${type}_montants_label", _T('souscription:label_montant'));
	$abo_montant_label = lire_config("souscription/${type}_abo_montants_label", _T('souscription:label_montant'));
	$montant_explication = nl2br(lire_config("souscription/${type}_montants_description"));
	$abo_montant_explication = nl2br(lire_config("souscription/${type}_abo_montants_description"));

	$email = '';
	// dans l'espace prive c'est une souscription pour autrui, pas de pre-remplissage
	if (!test_espace_prive()){
		if (isset($GLOBALS['visiteur_session']['email']) AND $GLOBALS['visiteur_session']['email'])
			$email = $GLOBALS['visiteur_session']['email'];
		elseif (isset($GLOBALS['visiteur_session']['session_email']) AND $GLOBALS['visiteur_session']['session_email'])
			$email = $GLOBALS['visiteur_session']['session_email'];
	}

	if (!function_exists('montants_str2array')) {
		include_spip('souscription_fonctions');
	}

	$valeurs = array(
		'montant' => '',
		'montant_libre' => '',
		'abo_montant' => '',
		'abo_montant_libre' => '',
		'courriel' => $email,
		'recu_fiscal' => $recu_fiscal,
		'envoyer_info' => 'off',
		'informer_comite_local' => 'on',
		'civilite' => '',
		'prenom' => '',
		'nom' => '',
		'adresse' => '',
		'code_postal' => '',
		'ville' => '',
		'pays' => 'FR',
		'telephone' => '',
		'id_souscription_campagne' => $id_souscription_campagne,
		'type_souscription' => $type,
		'_montant_datas' => montants_str2array($montant_datas),
		'_montant_type' => $montant_type,
		'_montant_label' => $montant_label,
		'_montant_explication' => $montant_explication,
		'_abo_montant_datas' => montants_str2array($abo_montant_datas,"abo"),
		'_abo_montant_type' => $abo_montant_type,
		'_abo_montant_label' => $abo_montant_label,
		'_abo_montant_explication' => $abo_montant_explication,
		'_souscription_paiement' => isset($GLOBALS['formulaires_souscription_paiement'])?$GLOBALS['formulaires_souscription_paiement']:'',
	);

	return $valeurs;
}


/**
 * Chargement du formulaire d'édition de souscription
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @uses formulaires_editer_objet_charger()
 *
 * @param int$id_souscription_campagne
 *     Identifiant de la campagne de souscription
 * @return array
 *     Tableau des erreurs
 */
function formulaires_souscription_verifier_dist($id_souscription_campagne){

	$erreurs = formulaires_editer_objet_verifier('souscription', 'new', array('courriel'));

	$campagne = sql_fetsel("*","spip_souscription_campagnes","id_souscription_campagne=".intval($id_souscription_campagne));

	$type_campagne = $campagne['type_objectif'];

	if (!verifier_campagne($id_souscription_campagne, $type_campagne)){
		$erreurs['message_erreur'] = _T('souscription:erreur_souscription_campagne_invalide');
	}


	if (_request('recu_fiscal')==="on" OR $type_campagne=="adhesion"){
		foreach (array('prenom', 'nom', 'adresse', 'code_postal', 'ville', 'pays') as $obligatoire){
			if (!_request($obligatoire)){
				if ($type_campagne=="adhesion"){
					$erreurs[$obligatoire] = _T('souscription:erreur_adhesion_champ_obligatoire');
				} else {
					$erreurs[$obligatoire] = _T('souscription:erreur_recu_fiscal_champ_obligatoire');
				}
			}
		}
	}

	if ($e = _request('courriel') AND !email_valide($e))
		$erreurs['courriel'] = _T('form_prop_indiquer_email');

	if ($e = _request('pays')){
		$ret = sql_select('nom', 'spip_pays', "code='${e}'");

		if (sql_count($ret)!=1)
			$erreurs['pays'] = _T('souscription:erreur_pays_invalide');

		/* Le code postal n'est vérifié que si on est dans le cas de la France */
		elseif ($e = _request('pays') AND $e=="FR") {
			if ($e = _request('code_postal') AND !preg_match("/^(2[ABab]|0[1-9]|[1-9][0-9])[0-9]{3}$/", $e)){
				$erreurs['code_postal'] = _T('souscription:erreur_code_postal_invalide');
			}
		}
	}

	if ($e = _request('telephone') AND !preg_match("/^[0-9\+ \.]+$/", $e)){
		$erreurs['telephone'] = _T('souscription:erreur_telephone_invalide');
	}

	$montant = formulaires_souscription_trouver_montant($campagne,$erreurs);
	if (!$montant){
		$erreurs['montant'] = $erreurs['abo_montant'] = _T('souscription:erreur_montant_obligatoire');
	}

	if (count($erreurs)>0
		AND !isset($erreurs['message_erreur'])){
		$erreurs['message_erreur'] = _T('souscription:erreur_formulaire');
	}

	return $erreurs;
}

/**
 * Vérification du montant. Si la campagne est configurée pour
 * utiliser une configuration spécifique, alors, il faut vérifier
 * avec les montants de la campagne. Autrement, il faut utiliser les
 * paramètres globaux.
 *
 * @param $campagne
 * @param $erreurs
 * @return null|string
 */
function formulaires_souscription_trouver_montant($campagne,&$erreurs){
	$e = _request('montant');
	$libre = false;

	if ($ea = _request('abo_montant')){
		$ea = _request('abo_montant');
		if ($ea=="libre"){
			$ea = _request('abo_montant_libre');
			if ($ea)
				$libre = true;
		}
		if ($ea)
			$e = ((strncmp($ea,"abo",3)==0)?"":"abo").$ea;
	}
	if ($e=="libre"){
		$e = _request('montant_libre');
		$libre = true;
	}
	elseif ($e=="abo_libre"){
		$e = (_request('abo_montant_libre')?"abo"._request('abo_montant_libre'):"");
		$libre = true;
	}

	if ($e){
		$abo = (strncmp($e,"abo",3)==0)?"abo_":"";
		if (!ctype_digit($abo?substr($e,3):$e))
			$erreurs[$abo.'montant'] = _T("souscription:erreur_montant_invalide");
		else {

			if ($campagne['configuration_specifique']!=='on'){
				$montant_type = lire_config("souscription/".$campagne['type_objectif']."{$abo}_type_saisie", 'input');
				$montant_datas = lire_config("souscription/".$campagne['type_objectif']."{$abo}_montants", '');
			} else {
				$montant_type = $campagne[$abo.'type_saisie'];
				$montant_datas = $campagne[$abo.'montants'];
			}

			/* On ne vérifie strictement la valeur du montant que si on
			 * n'utilise pas le type de saisie « entrée libre » (input) pour
			 * le montant. */
			if (($montant_type!=="input")
			  AND !$libre
			  AND !array_key_exists($e, montants_str2array($montant_datas,$abo?"abo":"")))
				$erreurs[$abo.'montant'] = _T('souscription:erreur_montant_specifie_invalide');
		}
	}
	return $e;
}

/**
 * Chargement du formulaire d'édition de souscription
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @uses formulaires_editer_objet_charger()
 *
 * @param int$id_souscription_campagne
 *     Identifiant de la campagne de souscription
 * @return array
 *     Retours des traitements
 */
function formulaires_souscription_traiter_dist($id_souscription_campagne){
	$lier_trad = 0;
	$config_fonc = '';
	$row = array();
	$hidden = '';
	$retour = '';
	$ret = array();

	$campagne = sql_fetsel("*","spip_souscription_campagnes", "id_souscription_campagne=".intval($id_souscription_campagne));
	set_request("id_souscription_campagne",$id_souscription_campagne);
	set_request('type_souscription',$campagne['type_objectif']);
	if (!in_array(_request('envoyer_info'),array('on','off')))
		set_request('envoyer_info','off');

	$where_deja = array(
		'courriel=' . sql_quote(_request('courriel')),
		'nom=' . sql_quote(_request('nom')),
		'prenom=' . sql_quote(_request('prenom')),
		'statut=' . sql_quote('prepa'),
		"date_souscription>".sql_quote(date('Y-m-d H:i:s',strtotime("-1 day"))),
		'id_souscription_campagne='.intval($id_souscription_campagne),
	);
	$erreurs = array();
	$montant = formulaires_souscription_trouver_montant($campagne,$erreurs);
	$abo = false;
	if (strncmp($montant,"abo",3)==0){
		$abo = true;
		$montant = substr($montant,3);
		set_request("abo_statut","commande");
		$where_deja[] = 'abo_statut='.sql_quote('commande');
	}
	else {
		$where_deja[] = 'abo_statut='.sql_quote('non');
	}
	set_request('montant',$montant);
	$where_deja[] = 'montant='.sql_quote($montant,'','text');

	// si on a une souscription du meme montant, meme email, en commande, qui date de moins de 24h
	// on la reutilise pour pas generer plein de souscription en base en cas de retour arriere/modif saisie/revalidation
	if (!$id_souscription = sql_getfetsel('id_souscription','spip_souscriptions',$where_deja)){
		$id_souscription='new';
	}

	$ret = formulaires_editer_objet_traiter('souscription',
		$id_souscription,
		'',
		$lier_trad,
		$retour,
		$config_fonc,
		$row,
		$hidden);

	if ($ret['id_souscription']){
		// recuperer l'id_auteur de la souscription, qui a pu etre renseigne en post_edition par un autre plugin
		// ou recupere de la session courante hors espace prive
		$souscription = sql_fetsel("*","spip_souscriptions","id_souscription=".intval($ret['id_souscription']));
		$id_auteur = $souscription['id_auteur'];
		// generer la transaction et l'associer a la souscription
		$inserer_transaction = charger_fonction('inserer_transaction', 'bank');
		$options = array(
			"auteur" => _request('courriel'),
			"id_auteur" => $id_auteur,
			"parrain" => "souscription",
			"tracking_id" => $ret['id_souscription'],
			"force" => false,
		);
		if ($id_transaction = $inserer_transaction($montant,$options)
		  AND $hash = sql_getfetsel('transaction_hash',"spip_transactions","id_transaction=".intval($id_transaction))){

			// associer transaction et souscription
			include_spip("action/editer_liens");
			objet_associer(array("souscription"=>$ret['id_souscription']),array("transaction"=>$id_transaction));
			sql_updateq("spip_souscriptions",array('id_transaction_echeance'=>$id_transaction),"id_souscription=".intval($ret['id_souscription']));

			// si pas d'auteur ni en base ni en session, passer nom et prenom en session pour un eventuel usage dans le paiement (SEPA)
			if (!isset($GLOBALS['visiteur_session']['id_auteur'])
				OR !$GLOBALS['visiteur_session']['id_auteur']
				OR !$GLOBALS['visiteur_session']['id_auteur']==$id_auteur){
				include_spip('inc/session');
				if ($souscription['nom']){
					session_set("session_nom",$souscription['nom']);
				}
				if ($souscription['prenom']){
					session_set("session_prenom",$souscription['prenom']);
				}
			}

			$target = ($abo?"payer-abonnement":"payer-acte");
			spip_log(sprintf("La souscription [%s], associee a la transaction [%s] a bien ete cree.", $ret['id_souscription'], $id_transaction), "souscription");
			if (lire_config("souscription/processus_paiement","redirige")==="redirige"){
				$ret['redirect'] = generer_url_public($target, "id_transaction=$id_transaction&transaction_hash=$hash", false, false);
			}
			else {
				$ret['message_ok'] = _T('souscription:message_regler_votre_'.$campagne['type_objectif']);
				$GLOBALS['formulaires_souscription_paiement'] = recuperer_fond("content/$target",array('id_transaction'=>$id_transaction,'transaction_hash'=>$hash,'class'=>'souscription_paiement'));
			}
		}
		else {
			spip_log(sprintf("Erreur lors de la creation de la transaction liee a la souscription [%s].", $ret['id_souscription']), "souscription");
			$ret['message_erreur'] = _T('souscription:erreur_echec_creation_transaction');
		}

	}

	// si API newsletter est dispo ET que case inscription est cochee, inscrire a la newsletter
	if (_request("envoyer_info")==="on"
	  AND $subscribe = charger_fonction("subscribe","newsletter",true)){
		$email = _request("courriel");
		$nom = array(_request("prenom"),_request("nom"));
		$nom = array_filter($nom);
		$nom = implode(" ",$nom);
		$subscribe($email,array(
				'nom'=>$nom,
				'listes'=> explode(',',lire_config("souscription/mailing_list"))
		));
	}

	return $ret;
}

function verifier_campagne($id_souscription_campagne, $type_souscription = null){
	/* FIXME: vérifier que la campagne a bien le bon statut (ouverte, fermée, terminée, etc.) */

	$campagne = sql_fetsel(array('type_objectif', 'objectif_initial', 'objectif', 'objectif_limiter'),
		'spip_souscription_campagnes', 'id_souscription_campagne=' . sql_quote(intval($id_souscription_campagne)));

	/* La campagne doit exister */
	if (empty($campagne['type_objectif'])) {
		return false;
	}
	elseif ($type_souscription!=null && $campagne['type_objectif']!=$type_souscription) {
		return false;
	}

	/* Si la campagne doit être fermée lorsque l'objectif est atteint,
	 * alors on bloque. */
	elseif ($campagne['objectif_limiter'] == 'on' &&
		calcul_avancement_campagne($id_souscription_campagne, $campagne['type_objectif'], $campagne['objectif_initial'])>=$campagne['objectif']
	) {
		return false;
	}

	return true;
}
