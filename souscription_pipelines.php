<?php
/**
 * Utilisations de pipelines par Souscription
 *
 * @plugin     Souscription
 * @copyright  2013
 * @author     Olivier Tétard
 * @licence    GNU/GPL
 * @package    SPIP\Souscription\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) return;
	


/**
 * Optimiser la base de données en supprimant les liens orphelins
 * de l'objet vers quelqu'un et de quelqu'un vers l'objet.
 *
 * @pipeline optimiser_base_disparus
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function souscription_optimiser_base_disparus($flux){
  include_spip('action/editer_liens');
  $flux['data'] += objet_optimiser_liens(array('souscription'=>'*'),'*');
  return $flux;
}

?>
