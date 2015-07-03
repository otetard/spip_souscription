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
 * Afficher la liste des transactions d'un auteur sur la page auteur de l'espace prive
 *
 * @pipeline affiche_auteurs_interventions
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function souscription_affiche_auteurs_interventions($flux) {
	if ($id_auteur = intval($flux['args']['id_auteur'])) {

		$flux['data'] .= recuperer_fond('prive/objets/liste/souscriptions', array(
			'id_auteur' => $id_auteur,
		), array('ajax' => true));

	}
	return $flux;
}


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
	$flux['data'] += objet_optimiser_liens(array('souscription'=>'*'), '*');
	return $flux;
}

/**
 * Envoi d'une notification après reglement
 *
 * @pipeline trig_bank_notifier_reglement
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function souscription_trig_bank_notifier_reglement($flux) {
	$souscription = sql_fetsel(array('courriel', 'id_souscription_campagne','abo_statut','id_souscription'), 'spip_souscriptions', 'id_transaction_echeance='.intval($flux['args']['id_transaction']));

	// on ne notifie pas les N echeances d'un don mensuel mais seulement la premiere
	$n_echeance = 1;
	if ($souscription['abo_statut']=='ok'){
		$n_echeance = sql_countsel("spip_souscriptions_liens","id_souscription=".intval($souscription['id_souscription'])." AND objet=".sql_quote('transaction'));
	}

	if ($n_echeance<=1){
		$email = $souscription['courriel'];
		$campagne = $souscription['id_souscription_campagne'];

		if ($flux['args']['succes']) {
			$message = recuperer_fond(_trouver_modele_courriel_reglement("succes", $campagne),
						  array('id_transaction' => $flux['args']['id_transaction']));
		}
		else {
			$message = recuperer_fond(_trouver_modele_courriel_reglement("echec", $campagne),
						  array('id_transaction' => $flux['args']['id_transaction']));
		}

		spip_log("Envoi de notification de confirmation de paiement a [$email] pour la souscription #".$flux['args']['id_transaction'],"souscription");

		include_spip("inc/notifications");
		notifications_envoyer_mails($email, $message, "", $GLOBALS['meta']['email_webmaster']);
	}

	return $flux;
}

function _trouver_modele_courriel_reglement($type, $id_souscription_campagne) {
	$modele = "modeles/mail-souscription-${type}";

	if(trouver_fond("${modele}-${id_souscription_campagne}"))
		$modele = "${modele}-${id_souscription_campagne}";

	return $modele;
}


function souscription_bank_traiter_reglement($flux){

	// on peut marquer cette souscription comme effective
	// et mettre a jour le montant cumul si besoin
	if ($id_transaction = $flux['args']['id_transaction']
	  AND $r = sql_fetsel("statut,montant","spip_transactions","id_transaction=".intval($id_transaction))
	  AND $sous = sql_fetsel("*","spip_souscriptions","id_transaction_echeance=".intval($id_transaction))){

		$set = array(
			'statut' => 'ok',
			'id_transaction_echeance' => 0
		);
		if ($sous['abo_statut']=="ok"){
			$set['montant_cumul'] = round(floatval($sous['montant_cumul']) + floatval($r['montant']),2);
		}
		sql_updateq("spip_souscriptions",$set,'id_souscription='.intval($sous['id_souscription']));
	}

	$flux['data'].=" <br />Vous allez recevoir un email de confirmation.";
	return $flux;
}

function souscription_bank_traiter_remboursement($flux){

	// on marque cette souscription comme remboursee
	// et mettre a jour le montant cumul si besoin
	if ($id_transaction = $flux['args']['id_transaction']
	  AND $r = sql_fetsel("statut,montant","spip_transactions","id_transaction=".intval($id_transaction))
	  AND $sous = sql_fetsel("*","spip_souscriptions","id_transaction_echeance=".intval($id_transaction))){

		$set = array(
			'statut' => 'rembourse'
		);
		#if ($sous['abo_statut']=="ok"){
		#	$set['montant_cumul'] = round(floatval($sous['montant_cumul']) + floatval($r['montant']),2);
		#}
		sql_updateq("spip_souscriptions",$set,'id_souscription='.intval($sous['id_souscription']));
	}

	$flux['data'].=" <br />Souscription #".$sous['id_souscription']." associée remboursée";
	return $flux;
}


/**
 * Activer la souscription abonnee
 * @param $flux
 * @return mixed
 */
function souscription_bank_abos_activer_abonnement($flux){
	if (!$flux['data']){
		$abo_uid = $flux['args']['abo_uid'];
		$set = array(
			"abonne_uid"=>sql_quote($abo_uid),
			"abo_statut"=>sql_quote('ok'),
		);

		if ($id_transaction = $flux['args']['id_transaction']){
			if (!$row = sql_fetsel("*","spip_souscriptions","id_transaction_echeance=".intval($id_transaction))
			  OR !$id_souscription = $row['id_souscription']){
			 	spip_log("Impossible de retrouver la souscription liee a la transaction $id_transaction",'souscriptions_abos'._LOG_ERREUR);
				return $flux;
			}

			if (!$r = sql_fetsel("statut,montant","spip_transactions","id_transaction=".intval($id_transaction))){
				spip_log("transaction $id_transaction inconnue (abo $abo_uid)",'souscriptions_abos'._LOG_ERREUR);
				return $flux;
			}
			if ($r['statut']!=='ok'){
			 	spip_log("La transaction $id_transaction n'a pas ete reglee (abo $abo_uid)",'souscriptions_abos'._LOG_ERREUR);
				return $flux;
			}
			// fixer le montant cumul des dons
			$set['montant_cumul'] = sql_quote($r['montant']);

		}
		elseif (
			!$abo_uid
		  OR !($row = sql_fetsel("*","spip_souscriptions","abonne_uid=".sql_quote($abo_uid)))
		  ){
		  spip_log("Impossible de retrouver l'abo_uid $abo_uid",'souscriptions_abos'._LOG_ERREUR);
			return $flux;
		}

		if ($row['abo_statut']==='non'){
			// cela ne nous concerne pas : ce n'est pas une souscription recurrente
			spip_log("activer_abonnement sur souscription #".$row['id_souscription']." non recurente",'souscriptions_abos');
			return $flux;
		}

		// ici on ne traite que le premier appel, a la premiere transaction payee
		// et pas les autres appels sur paiements reccurentes (bank v3.0+)
		if ($row['abo_statut']==='ok'
		  AND $row['abonne_uid']===$abo_uid
		  AND (!isset($set['montant_cumul']) OR floatval($row['montant_cumul'])>=0.01)){
			// c'est un double appel, on retourne sans rien faire
			spip_log("activer_abonnement sur souscription #".$row['id_souscription']." abo_statut=ok (double appel)",'souscriptions_abos');
			return $flux;
		}


		if ($id_transaction
		  AND ($id_transaction==$row['id_transaction_echeance']) ){

			$prochaine_echeance = date('Y-m-d H:i:s',strtotime("+1 month",strtotime($row["date_souscription"])));
			if ($flux['args']['validite']==='echeance'){
				$set["date_echeance"] = sql_quote($prochaine_echeance);
				$set["date_fin"] = $set["date_echeance"];
			}
			else {
				if ($prochaine_echeance<$flux['args']['validite']){
					$set["date_echeance"] = sql_quote($prochaine_echeance);
				}
				else {
					$set["date_echeance"] = sql_quote($row["date_souscription"]);
				}
				$set["date_fin"] = sql_quote($flux['args']['validite']);
			}

		}

		if ($row['id_souscription'] AND count($set)){
			sql_update("spip_souscriptions",$set,"id_souscription=".intval($row['id_souscription']));
		}
		if ($row['id_souscription']){
			$flux['data'] = $row['id_souscription'];
		}
	}

	return $flux;
}

/**
 * Decrire l'echeance d'une souscription mensuelle
 * @param array $flux
 * @return array
 */
function souscription_bank_abos_decrire_echeance($flux){
	if ($id_transaction = $flux['args']['id_transaction']
	  AND $row = sql_fetsel("*","spip_souscriptions","id_transaction_echeance=".intval($id_transaction))){
		$flux['data']['montant'] = $row['montant'];
		$flux['data']['montant_ht'] = $row['montant'];
	}
	return $flux;
}

/**
 * Gerer le renouvellement lors de la notification de paiement d'une echeance par le presta bancaire
 *
 * @param array
 * @return array
 */
function souscription_bank_abos_renouveler($flux){

	if (!$flux['data']){

		$id = $flux['args']['id'];
		if (strncmp($id,"uid:",4)==0){
			$where = "abonne_uid=".sql_quote(substr($id,4));
		}
		else {
			$where = "id_souscription=".intval($id);
		}
		if ($row = sql_fetsel("*","spip_souscriptions",$where,'','date_souscription DESC')){
			$options = array(
				'auteur' => $row['courriel'],
				'parrain' => 'souscription',
				'tracking_id' => $row['id_souscription'],
				'id_auteur' => $row['id_auteur'],
			);
			$inserer_transaction = charger_fonction("inserer_transaction","bank");
			include_spip("action/editer_liens");

			// verifier que c'est bien l'echeance attendue
			// et sinon generer des transactions offline de rattrapage
			if (!intval($row["date_echeance"])){
				spip_log("souscription #".$row['id_souscription']." date echeance vide ".$row["date_echeance"],'souscriptions_abos');
				$row["date_echeance"] = $row["date_souscription"];
			}
			$date_echeance = $row['date_echeance'];
			$datem45 = date('Y-m-d H:i:s',strtotime("-45 day"));
			while ($date_echeance<$datem45){
				$o = $options;
				$o['champs']['date_transaction'] = $date_echeance;
				$o['champs']['mode'] = 'offline';
				$prochaine_echeance = date('Y-m-d H:i:s',strtotime("+1 month",strtotime($date_echeance)));
				if ($id_transaction = $inserer_transaction($row['montant'],$o)){
					// regler la transacton offline
					$regler_transaction = charger_fonction('regler_transaction','bank');
					$regler_transaction($id_transaction);

					// mettre a jour la souscription
					$set = array(
						'statut' => 'ok',
						'abo_statut' => 'ok',
						'montant_cumul' =>  round(floatval($row['montant_cumul']) + floatval($row['montant']),2),
						'date_echeance' => $prochaine_echeance,
						'abo_fin_raison' => '', // effacer la trace d'un rappel de paiement manquant
					);
					sql_updateq('spip_souscriptions',$set,"id_souscription=".intval($row['id_souscription']));
					$row = sql_fetsel("*","spip_souscriptions","id_souscription=".intval($row['id_souscription']));
					objet_associer(array("souscription"=>$row['id_souscription']),array("transaction"=>$id_transaction));
					spip_log("transaction offline $id_transaction sur souscription #".$row['id_souscription']." / prochaine echeance $prochaine_echeance",'souscriptions_abos');
				}
				$date_echeance = $prochaine_echeance;
			}

			// si il y a deja eu une transaction echeance il y a moins de 15j sur cette souscription
			// c'est un double appel, renvoyer l'id_transaction concerne
			$datem15 = date('Y-m-d H:i:s',strtotime("-15 day"));
			if ($id_transaction = sql_getfetsel(
				"id_transaction",
				"spip_transactions",
				"statut<>".sql_quote('commande')
				." AND date_transaction>".sql_quote($datem15)
				." AND parrain=".sql_quote('souscription')
				." AND tracking_id=".intval($row['id_souscription'])
				." AND id_auteur=".intval($row['id_auteur']),
				"",
				"date_transaction"
			)){
				$flux['data'] = $id_transaction;
			}
			// ouvrir la transaction
			elseif ($id_transaction = $inserer_transaction($row['montant'],$options)){
				$prochaine_echeance = $row['date_echeance'];
				$datep15 = date('Y-m-d H:i:s',strtotime("+15 day"));
				spip_log("souscription #".$row['id_souscription']." $prochaine_echeance vs $datep15","souscriptions_abos");
				// recaler la prochaine echeance si trop en avance (double appel anterieur ou erreur de calcul)
				while($prochaine_echeance>$datep15){
					$prochaine_echeance = date('Y-m-d H:i:s',strtotime("-1 month",strtotime($prochaine_echeance)));
					spip_log("souscription #".$row['id_souscription']." echeance=echeance-1 month : $prochaine_echeance vs $datep15","souscriptions_abos");
				}
				// l'incrementer pour atteindre celle du mois prochain
				while($prochaine_echeance<$datep15){
					$prochaine_echeance = date('Y-m-d H:i:s',strtotime("+1 month",strtotime($prochaine_echeance)));
					spip_log("souscription #".$row['id_souscription']." echeance=echeance+1 month : $prochaine_echeance vs $datep15","souscriptions_abos");
				}

				// a ce stade on ne sait pas encore si la transaction est reussie ou en echec
				// on ne peut donc pas incrementer le montant cumul, mais seulement mettre a jour les echeances etc
				// si echec => declenchera une resiliation
				// si succes => declenchera un traitement reglement ou l'on mettra a jour le cumul
				$set = array(
					'id_transaction_echeance' => $id_transaction,
					'statut' => 'ok',
					'abo_statut' => 'ok',
					'date_echeance' => $prochaine_echeance,
					'abo_fin_raison' => '', // effacer la trace d'un rappel de paiement manquant
				);
				sql_updateq('spip_souscriptions',$set,"id_souscription=".intval($row['id_souscription']));
				$row = sql_fetsel("*","spip_souscriptions","id_souscription=".intval($row['id_souscription']));
				objet_associer(array("souscription"=>$row['id_souscription']),array("transaction"=>$id_transaction));
				$flux['data'] = $id_transaction;

				// verifier si ce n'est pas la derniere transaction, auquel cas on notifie
				if ($row['date_echeance']>$row['date_fin']
				  AND $row['date_fin']>$row['date_souscription']){

					// Notifications
					if ($notifications = charger_fonction('notifications', 'inc', true)) {
						$notifications('informersouscriptionterminee', $row['id_souscription']);
					}

				}
			}
		}
	}

	return $flux;
}

/**
 * Prendre en charge la resiliation demandee par le client
 *
 * @param array $flux
 * @return array
 */
function souscription_bank_abos_resilier($flux){

	$id = $flux['args']['id'];
	if (strncmp($id,"uid:",4)==0){
		$where = "abonne_uid=".sql_quote(substr($id,4));
	}
	else {
		$where = "id_souscription=".intval($id);
	}
	if ($row = sql_fetsel("*","spip_souscriptions",$where,'','date_souscription DESC')){
		$set = array(
			'abo_statut' => 'resilie',
			'abo_fin_raison' => $flux['args']['message'],
		);
		if ($flux['args']['date_fin']=='date_echeance'){
			$set['date_fin'] = date('Y-m-d 00:00:00',strtotime($row['date_echeance']));
			if (!function_exists('souscription_derniere_echeance')){
				include_spip("public/parametrer");
			}
			$set['date_echeance'] = souscription_derniere_echeance($row['date_echeance'],$set['date_fin']);
		}
		else {
			$set['date_fin'] = $flux['args']['date_fin'];
		}

		$ok = true;
		if ($flux['args']['notify_bank']
		  AND $mode_paiement = sql_getfetsel("mode","spip_transactions","id_transaction=".intval($row['id_transaction_echeance']))){
			$ok = abos_resilier_notify_bank($row['abonne_uid'],$mode_paiement);
		}
		if ($ok){
			sql_updateq("spip_souscriptions",$set,"id_souscription=".intval($row['id_souscription']));
		}

		// si c'est une resiliation suite a refus de paiement, notifier
		if (strncmp($flux['args']['message'],'[bank]',6)==0){
			// Notifications
			if ($notifications = charger_fonction('notifications', 'inc', true)) {
				$id_transaction = 0;
				if (preg_match(",\b#(\d+)\b,",$flux['args']['message'],$m)){
					$id_transaction = intval($m[1]);
				}
				$notifications('informersouscriptioninterrompue', $row['id_souscription'], array('id_transaction'=>$id_transaction,'message'=>$flux['args']['message']));
			}
		}

	}

	return $flux;
}

/**
 * Programmer la surveillance des echeances
 * @param $taches_generales
 * @return mixed
 */
function souscription_taches_generales_cron($taches_generales){
	$taches_generales['surveiller_paiement_souscriptions'] = 2*3600; // 2h
	return $taches_generales;
}