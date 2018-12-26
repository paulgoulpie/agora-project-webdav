<?php
use Sabre\DAV\Auth\Backend\AbstractBasic;

require_once '../app/Common/MdlObjectAttributes.php';
require_once '../app/Common/MdlObjectMenus.php';
require_once '../app/Common/MdlObject.php';
require_once '../app/Common/MdlPerson.php';
require_once '../app/ModUser/MdlUser.php';

class PDOAgoraProject extends AbstractBasic {
	
	protected $pdo;
	public $tableName = 'ap_user';
	private $idUser;
	
	function __construct(\PDO $pdo, $tableName = 'ap_user') {	
		$this->pdo = $pdo;
        $this->tableName = $tableName;	
        $this->idUser = -1;
	}
	
	function validateUserPass($username, $password)
	{
		$result = false;
		$stmt = $this->pdo->prepare('SELECT password,_id FROM '.$this->tableName.' WHERE login = ?');
		$stmt->execute([$username]);
		$result_db = $stmt->fetch();
		$hashPassDb = $result_db['password'];
		$hashPassHtml = MdlUser::passwordSha1($password); 
				
		if ($hashPassHtml == $hashPassDb)
		{
			$result = true;
			$this->idUser = $result_db['_id'];
		}
		
		return $result;
	}	
	
	function getIdUser()
	{
		return $this->idUser;		
	}
}