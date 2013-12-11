<?php
/**
 * Gestion du formulaire de d'édition de souscription_campagne
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

function formulaires_editer_souscription_campagne_identifier_dist($id_souscription_campagne = 'new',
                                                                  $retour = '',
                                                                  $lier_trad = 0,
                                                                  $config_fonc = '',
                                                                  $row = array(),
                                                                  $hidden = ''){
	return serialize(array(intval($id_souscription_campagne)));
}

function formulaires_editer_souscription_campagne_charger_dist($id_souscription_campagne = 'new',
                                                               $retour = '',
                                                               $lier_trad = 0,
                                                               $config_fonc = '',
                                                               $row = array(),
                                                               $hidden = ''){
	$valeurs = formulaires_editer_objet_charger('souscription_campagne',
		$id_souscription_campagne,
		'',
		$lier_trad,
		$retour,
		$config_fonc,
		$row,
		$hidden);

	/* Si la valeur du champ 'objectif' est 0, alors, c'est que
	 * l'objectif n'est pas activé. */
	$valeurs['_defaut_objectif'] = "";
	if ($valeurs['objectif'] AND $valeurs['objectif']>0)
		$valeurs['_defaut_objectif'] = "on";

	// pour la saisie uniquement, pas en base
	$valeurs['objectif_oui_non'] = "";

	return $valeurs;
}

function formulaires_editer_souscription_campagne_verifier_dist($id_souscription_campagne = 'new',
                                                                $retour = '',
                                                                $lier_trad = 0,
                                                                $config_fonc = '',
                                                                $row = array(),
                                                                $hidden = ''){

	$erreurs = formulaires_editer_objet_verifier('souscription_campagne',
		$id_souscription_campagne,
		array('titre',
			'type_objectif',
		));

	$type = _request("type_objectif");
	if (!in_array($type, array('don', 'adhesion')))
		$erreurs['type_objectif'] = _T("souscription_campagne:erreur_objectif_invalide");
	elseif ($type=="don" AND lire_config("souscription/don_activer","off")!=="on")
		$erreurs['type_objectif'] = _T("souscription_campagne:erreur_objectif_don_inactif");
	elseif ($type=="adhesion" AND lire_config("souscription/adhesion_activer","off")!=="on")
		$erreurs['type_objectif'] = _T("souscription_campagne:erreur_objectif_adhesion_inactif");

	/* Si un objectif est demandé, alors on vérifie que les champs sont
	 * bien des entiers. */
	if (_request('objectif_oui_non')=='on'){
		$objectif = _request('objectif');
		if (!ctype_digit($objectif) || intval($objectif)<0)
			$erreurs['objectif'] = _T("souscription_campagne:erreur_objectif_invalide");

		$objectif_initial = _request('objectif_initial');
		if ($objectif_initial!=''){
			if (!ctype_digit($objectif_initial) || intval($objectif_initial<0))
				$erreurs['objectif_initial'] = _T("souscription_campagne:erreur_objectif_initial_valeur");
			elseif (intval($objectif_initial)>$objectif)
				$erreurs['objectif_initial'] = _T("souscription_campagne:erreur_objectif_initial_supperieur_objectif");
		}
	}

	/* Si une limite est demandée, alors, on vérifie que le champs est
	 * bien un entier. */
	$limite_oui_non = _request('limite_oui_non');
	if ($limite_oui_non=="on"){
		$limite = _request('limite');
		if (!ctype_digit($objectif))
			$erreurs['limite'] = _T("souscription_campagne:erreur_limite_valeur");
	}

	/* Si une guration spécifique est demandée, alors on vérifie les
	 * valeurs 'type_saisie' et 'montants' (si on a demandé un bouton
	 * radio ou une selection). Autrement, les données sont supprimées
	 * dans la fonction traiter. */
	if (_request('configuration_specifique')){
		$type_saisie = _request('type_saisie');
		$saisies = array("input", "radio", "radioinput", "selection");
		if (!$type_saisie || !in_array($type_saisie, $saisies))
			$erreurs['type_saisie'] = _T('souscription:erreur_champ_invalide');
		else {
			$montants = _request('montants');
			if ($type_saisie && $type_saisie!=="input"){
				if (!$montants || !is_string($montants))
					$erreurs['montants'] = _T('souscription:erreur_montants');

				elseif (!montants_str2array($montants))
					$erreurs['montants'] = _T('souscription:erreur_montants');
			}
		}
	}

	return $erreurs;
}

function formulaires_editer_souscription_campagne_traiter_dist($id_souscription_campagne = 'new',
                                                               $retour = '',
                                                               $lier_trad = 0,
                                                               $config_fonc = '',
                                                               $row = array(),
                                                               $hidden = ''){
	/* Si un objectif n'est pas demandée, alors, on remplace la valeur
	 * fournie (quelqu'elle soit, par 0) */
	if (_request('objectif_oui_non')!="on"){
		set_request('objectif', 0);
		set_request('objectif_initial', 0);
		set_request('objectif_limiter', '');
	}

	if (_request('configuration_specifique'!="on")){
		set_request('type_saisie', '');
		set_request('montants', '');
	}

	$res = formulaires_editer_objet_traiter('souscription_campagne',
		$id_souscription_campagne,
		'',
		$lier_trad,
		$retour,
		$config_fonc,
		$row,
		$hidden);

	return $res;
}
