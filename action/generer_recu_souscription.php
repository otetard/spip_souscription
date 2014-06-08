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

include_spip("public/parametrer"); // fonctions

function action_generer_recu_souscription_dist($id_souscription = null, $annee=null){
	if (is_null($id_souscription)){
		$id_souscription = _request('id_souscription');
		$annee = _request('annee');
		$hash = _request('hash');
		$lowsec = souscription_hash_lowsec($id_souscription, $annee);
		if ($hash!==$lowsec){
			die('Erreur : URL pas autorisee');
		}
	}
	else {
		$lowsec = souscription_hash_lowsec($id_souscription, $annee);
	}

	$format = _request('format');
	if (!in_array($format,array('html','pdf'))){
		// PDF ou HTML ? si le plugin SPIPDF est la on genere un recu en PDF
		$format = "html";
		if (test_plugin_actif("spipdf")){
			$format = "pdf";
		}
	}

	$numero = souscription_numero_recu($id_souscription,$annee);
	$dir = sous_repertoire(_DIR_IMG,"attestations");

	// securite : dossier inaccessible en http
	if (!file_exists($f=$dir.".htaccess")){
		ecrire_fichier($f,"deny from all\n");
	}

	$filename = $numero.".$format";
	$file = $dir.$filename;

	if (!file_exists($file)){

		$fond = ($format=="pdf"?"attestation_pdf":"attestation");
		$content = recuperer_fond($fond,array("id_souscription"=>$id_souscription,"annee"=>$annee,"hash"=>$lowsec));

		ecrire_fichier($file,$content);
	}

	$mime = "text/html";
	if ($format=="pdf")
		$mime = "application/pdf";
	header("Content-type: $mime");
	if ($format=="pdf"){
		$filename = preg_replace(",\W+,","",$GLOBALS['meta']['nom_site'])."-Recu-".$filename;
		header("Content-Disposition: attachment; filename=$filename");
		//header("Content-Transfer-Encoding: binary");
	}

	// fix for IE catching or PHP bug issue
	header("Pragma: public");
	header("Expires: 0"); // set expiration time
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

	if ($cl = filesize($file))
		header("Content-Length: ". $cl);

	readfile($file);

}