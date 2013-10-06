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

function formulaires_editer_souscription_campagne_identifier_dist($id_souscription_campagne='new',
                                                                  $retour='',
                                                                  $lier_trad=0,
                                                                  $config_fonc='',
                                                                  $row=array(),
                                                                  $hidden='')
{
  return serialize(array(intval($id_souscription_campagne)));
}

function formulaires_editer_souscription_campagne_charger_dist($id_souscription_campagne='new',
                                                               $retour='',
                                                               $lier_trad=0,
                                                               $config_fonc='',
                                                               $row=array(),
                                                               $hidden='')
{
  $valeurs = formulaires_editer_objet_charger('souscription_campagne',
					      $id_souscription_campagne,
					      '',
					      $lier_trad,
					      $retour,
					      $config_fonc,
					      $row,
					      $hidden);

  $valeurs['objectif_oui_non'] = _request('objectif_oui_non');

  /* Si la valeur du champ 'objectif' est 0, alors, c'est que
   * l'objectif n'est pas activé. */
  $defaut_objectif = "";
  if($valeurs['objectif'] && $valeurs['objectif'] > 0)
    $defaut_objectif = "on";

  $saisies = array(array('saisie' => 'input',
			 'options' => array('nom' => 'titre',
					    'label' => _T('souscription:label_titre'),
					    'obligatoire' => 'oui')
			 ),
		   array('saisie' => 'selection',
			 'options' => array('nom' => 'type_objectif',
					    'obligatoire' => 'oui',
					    'label' => _T('souscription:label_type_objectif'),
					    'explication' => _T('souscription:explication_type_objectif'),
					    'datas' => array('don' => 'Dons',
							     'adhesion' => 'Adhésions'))
			 ),
		   array('saisie' => 'oui_non',
			 'options' => array('nom' => 'objectif_oui_non',
					    'label' => _T('souscription:label_objectif_oui_non'),
					    'defaut' => $defaut_objectif)
			 ),
		   array('saisie' => 'fieldset',
			 'options' => array('nom' => 'groupe_limite',
					    'label' => _T('souscription:label_objectif_groupe'),
					    'afficher_si' => '@objectif_oui_non@ == "on"'),
			 'saisies' => array(array('saisie' => 'input',
						  'options' => array('nom' => 'objectif',
								     'obligatoire' => 'oui',
								     'label' => _T('souscription:label_objectif'),
								     'explication' => _T('souscription:explication_campagne_objectif')),
						  ),
					    array('saisie' => 'input',
						  'options' => array('nom' => 'objectif_initial',
								     'label' => _T('souscription:label_objectif_initial'),
								     'explication' => _T('souscription:explication_campagne_objectif_initial'))
					    ),
					    array('saisie' => 'oui_non',
						  'options' => array('nom' => 'objectif_limiter',
								     'explication' => _T('souscription:explication_campagne_objectif_limite'),
								     'label' => _T('souscription:label_objectif_limite')))
					    ),
			 ),
		   array('saisie' => 'oui_non',
			 'options' => array('nom' => 'configuration_specifique',
					    'label' => _T('souscription:label_configuration_specifique'),
					    'explication' => _T('souscription:explication_configuration_specifique'),
					    'defaut' => $defaut_objectif)
			 ),
		   array('saisie' => 'fieldset',
			 'options' => array('nom' => 'groupe_configuration_specifique',
					    'label' => _T('souscription:label_objectif_groupe'),
					    'afficher_si' => '@configuration_specifique@ == "on"'),
			 'saisies' => array(array('saisie' => 'selection',
						  'options' => array('nom' => 'type_saisie',
								     'label' => _T('souscription:label_type_saisie'),
								     'explication' => _T('souscription:explication_type_saisie'),
								     'datas' => array("input" => _T("souscription:configurer_type_saisie_input"),
										      "radio" => _T("souscription:configurer_type_saisie_radio"),
										      "selection" => _T("souscription:configurer_type_saisie_selection")),
								     'defaut' => 'input')
						  ),
					    array('saisie' => 'textarea',
						  'options' => array('nom' => 'montants',
								     'label' => _T('souscription:label_montants'),
								     'explication' => _T('souscription:explication_montants'),
								     'afficher_si' => '@type_saisie@ == "radio" || @type_saisie@ == "selection"',
								     'rows' => 4))
					    ),
			 ),
		   array('saisie' => 'textarea',
			 'options' => array('nom' => 'texte',
					    'label' => _T('souscription:label_description'),
					    'inserer_barre' => 'edition',
					    'rows' => '10'))
		   );

  $valeurs['_saisies'] = $saisies;

  return $valeurs;
}

function formulaires_editer_souscription_campagne_verifier_dist($id_souscription_campagne='new',
                                                                $retour='',
                                                                $lier_trad=0,
                                                                $config_fonc='',
                                                                $row=array(),
                                                                $hidden='')
{

  $ret = formulaires_editer_objet_verifier('souscription_campagne',
                                           $id_souscription_campagne,
                                           array('titre',
                                                 'type_objectif',
                                                 ));

  $type = _request("type_objectif");
  if(!in_array($type, array('don', 'adhesion')))
    $ret['type_objectif'] = _T("souscription:message_nok_objectif_invalide");

  /* Si un objectif est demandé, alors on vérifie que les champs sont
   * bien des entiers. */
  if(_request('objectif_oui_non') == 'on') {
    $objectif = _request('objectif');
    if(!ctype_digit($objectif) || intval($objectif) < 0)
      $ret['objectif'] = _T("souscription:message_nok_objectif_invalide");

    $objectif_initial = _request('objectif_initial');
    if($objectif_initial != '') {
      if(!ctype_digit($objectif_initial) || intval($objectif_initial < 0))
	$ret['objectif_initial'] = _T("souscription:message_nok_objectif_initial_valeur");
      elseif(intval($objectif_initial) > $objectif)
	$ret['objectif_initial'] = _T("souscription:message_nok_objectif_initial_supperieur_objectif");
    }
  }

  /* Si une limite est demandée, alors, on vérifie que le champs est
   * bien un entier. */
  $limite_oui_non = _request('limite_oui_non');
  if($limite_oui_non == "on") {
    $limite = _request('limite');
    if(!ctype_digit($objectif))
      $ret['limite'] = _T("souscription:message_nok_limite_valeur");
  }

  /* Si une guration spécifique est demandée, alors on vérifie les
   * valeurs 'type_saisie' et 'montants' (si on a demandé un bouton
   * radio ou une selection). Autrement, les données sont supprimées
   * dans la fonction traiter. */
  if(_request('configuration_specifique')) {
    $type_saisie = _request('type_saisie');
    if(!$type_saisie || !in_array($type_saisie, array('radio', 'selection', 'input')))
      $ret['type_saisie'] = _T('souscription:message_nok_type_saisie');

    $montants = _request('montants');
    if($type_saisie && in_array($type_saisie, array('radio', 'selection'))) {
      if(!$montants || !is_string($montants))
	$ret['montants'] = _T('souscription:message_nok_montants');

      elseif(!montants_str2array($montants))
	$ret['montants'] = _T('souscription:message_nok_montants');	
    }
  }

  return $ret;
}

function formulaires_editer_souscription_campagne_traiter_dist($id_souscription_campagne='new',
                                                               $retour='',
                                                               $lier_trad=0,
                                                               $config_fonc='',
                                                               $row=array(),
                                                               $hidden='')
{
  /* Si un objectif n'est pas demandée, alors, on remplace la valeur
   * fournie (quelqu'elle soit, par 0) */
  if(_request('objectif_oui_non') != "on") {
    set_request('objectif', 0);
    set_request('objectif_initial', 0);
    set_request('objectif_limiter', '');
  }

  if(_request('configuration_specifique' != "on")) {
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
