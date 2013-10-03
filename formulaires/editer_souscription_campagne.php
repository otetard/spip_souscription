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
						  )
					    )
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
    $objectif_initial = _request('objectif_initial');
    if(!ctype_digit($objectif_initial) || intval($objectif_initial) < 0)
      $ret['objectif_initial'] = _T("souscription:message_nok_objectif_initial_invalide");

    $objectif = _request('objectif');
    if(!ctype_digit($objectif) || intval($objectif) < 0)
      $ret['objectif'] = _T("souscription:message_nok_objectif_initial_valeur");
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
