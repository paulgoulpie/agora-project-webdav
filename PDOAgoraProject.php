<?php

use Sabre\DAV\Auth\Backend\PDO;
use Sabre\DAV\Auth\Backend;
use Sabre\DAV\Auth\Backend\AbstractBasic;

require_once '../fonctions/text.inc.php';

class PDOAgoraProject extends AbstractBasic {
	
	protected $pdo;
	public $tableName = 'gt_utilisateur';
	private $idUser;
	
	function __construct(\PDO $pdo, $tableName = 'gt_utilisateur') {	
		$this->pdo = $pdo;
        $this->tableName = $tableName;	
        $this->idUser = -1;
	}
	
	function validateUserPass($username, $password)
	{
		$result = false;
		$stmt = $this->pdo->prepare('SELECT pass,id_utilisateur FROM '.$this->tableName.' WHERE identifiant = ?');
		$stmt->execute([$username]);
		$result_db = $stmt->fetch();
		$hashPassDb = $result_db['pass'];
		$hashPassHtml = sha1_pass($password); 
				
		if ($hashPassHtml == $hashPassDb)
		{
			$result = true;
			$this->idUser = $result_db['id_utilisateur'];
		}
		
		return $result;
	}	
	
	function getIdUser()
	{
		return $this->idUser;		
	}
}