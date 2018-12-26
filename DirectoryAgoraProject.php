<?php
use Sabre\DAV;
use Sabre\DAV\Auth\Backend\PDO;

// require_once 'PDOAgoraProject.php';

require_once 'FileAgoraProject.php';

class DirectoryAgoraProject extends DAV\Collection {
	
  private $strPath;
  private $idPath;
  private $pdo;
  private $authBackend;
  private $lastModified;
  private $parent;
 

  function __construct($idPath,$strPath,$lastModified,$pdo,$authBackend,$parent = NULL) {
  	$this->strPath = $strPath;
    $this->idPath = $idPath;
    $this->pdo = $pdo;
    $this->authBackend = $authBackend;
    $this->lastModified = $lastModified;
    $this->parent = $parent;
  }
  
  function getIdPath() {
  	return $this->idPath;
  }
  
  function getParent() {
  	return $this->parent;
  }

  function getChildren() {

  	
  	$pdostatement = $this->pdo->prepare('SELECT DISTINCT `gt_fichier_dossier`.`id_dossier`,
                `gt_fichier_dossier`.`nom`,
  				UNIX_TIMESTAMP(`gt_fichier_dossier`.`date_crea`) AS `date_crea`,
  				UNIX_TIMESTAMP(`gt_fichier_dossier`.`date_modif`) AS `date_modif`
				FROM `gt_fichier_dossier`,
				     `gt_jointure_objet`,
				     `gt_utilisateur_groupe`,
				     `gt_utilisateur`
				WHERE `gt_fichier_dossier`.`id_dossier` = `gt_jointure_objet`.`id_objet`
				  AND `gt_jointure_objet`.`type_objet` = \'fichier_dossier\'
				  AND `gt_fichier_dossier`.`id_dossier_parent` = ?
				  AND `gt_utilisateur`.`id_utilisateur` = ?
				  AND `gt_utilisateur_groupe`.`id_utilisateurs` LIKE CONCAT(\'%@@\', `gt_utilisateur`.`id_utilisateur`, \'@@%\')
				  AND (`gt_jointure_objet`.`target` = \'tous\'
				       OR `gt_jointure_objet`.`target` = CONCAT(\'U\', `gt_utilisateur`.`id_utilisateur`)
				       OR `gt_jointure_objet`.`target` = CONCAT(\'G\', `gt_utilisateur_groupe`.`id_groupe`))
				ORDER BY `gt_fichier_dossier`.`nom` ASC
			');
  	
  	$pdostatement->execute([$this->idPath,$this->authBackend->getIdUser()]);
  	$result = $pdostatement->fetchAll();
  	
  	$children = array();
  	
  	foreach ($result as $value)
  		$children[] = new DirectoryAgoraProject($value['id_dossier'], $value['nom'], (($value['date_modif'] != NULL) ?$value['date_modif'] : $value['date_crea']), $this->pdo, $this->authBackend,$this);
  	
  	$pdostatement = $this->pdo->prepare('SELECT DISTINCT `gt_fichier`.`id_fichier`,
                `gt_fichier`.`nom`,
  				`gt_fichier`.`taille_octet`,
  				`gt_fichier_version`.`nom_reel`,
  				UNIX_TIMESTAMP(`gt_fichier`.`date_crea`) AS `date_crea`,
  				UNIX_TIMESTAMP(`gt_fichier`.`date_modif`) AS `date_modif`,
  				`gt_fichier`.`extension`  			
				FROM `gt_fichier`,
  					 `gt_fichier_version`
				WHERE `gt_fichier`.`id_dossier` = ?
  				  AND `gt_fichier_version`.`id_fichier` = `gt_fichier`.`id_fichier`
				ORDER BY `gt_fichier`.`nom` ASC
			');
  	 
  	$pdostatement->execute([$this->idPath]);
  	$result = $pdostatement->fetchAll();
  	
  	foreach ($result as $value) {
  		$children[] = new FileAgoraProject($value['id_fichier'], $value['nom'], $value['taille_octet'], $value['nom_reel'],(($value['date_modif'] != NULL) ?$value['date_modif'] : $value['date_crea']),$value['extension'], $this->pdo,$this);
  	}

    return $children;

  }

  function getName() {
  	return html_entity_decode($this->strPath);
  }
  
  function getLastModified()
  {
  	return $this->lastModified;  
  }
}