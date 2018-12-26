<?php
use Sabre\DAV;
use Sabre\DAV\Auth\Backend\PDO;

// define("GLOBAL_EXPRESS",true);

require_once '../fonctions/fichier.inc.php';
// require_once '../includes/global.inc.php';

class FileAgoraProject extends DAV\File {

	private $strName;
	private $idFile;
	private $pdo;
	private $size;
	private $nom_reel;
	private $lastModified;
	private $ext;
	private $directoryAgoraProjectParent;

	function __construct($idFile,$strName,$size,$nom_reel,$lastModified,$ext,$pdo,$directoryAgoraProjectParent = NULL) {

		$this->strName = $strName;
		$this->idFile = $idFile;
		$this->pdo = $pdo;		
		$this->size = $size;
		$this->nom_reel = $nom_reel;
		$this->lastModified = $lastModified;
		$this->ext = $ext;
		$this->directoryAgoraProjectParent = $directoryAgoraProjectParent;
	}
	
	function getName() {	
		return html_entity_decode($this->strName);	
	}
	
	function getSize() {	
		return $this->size;	
	}
	
	function getLastModified()
	{
		return $this->lastModified;		
	}
	
	function getContentType() {	
		$result = null;
		
		if(controle_fichier("word",$this->strName))			{ $result = "application/msword"; }
		elseif(controle_fichier("excel",$this->strName))		{ $result = "application/vnd.ms-excel"; }
		elseif(controle_fichier("powerpoint",$this->strName))	{ $result = "application/vnd.ms-powerpoint"; }
		elseif(controle_fichier("ootext",$this->strName))		{ $result = "application/vnd.oasis.opendocument.text"; }
		elseif(controle_fichier("oocalc",$this->strName))		{ $result = "application/vnd.oasis.opendocument.spreadsheet"; }
		elseif(controle_fichier("oopresent",$this->strName))	{ $result = "application/vnd.oasis.opendocument.presentation"; }
		elseif($this->ext==".pdf")	{ $result = "application/pdf"; }
		elseif($this->ext==".zip")	{ $result = "application/zip"; }
		elseif($this->ext==".txt")	{ $result = "text/plain;"; }
		elseif($this->ext==".rtf")	{ $result = "application/rtf;"; }
		elseif($this->ext==".mp3")	{ $result = "audio/mpeg"; }
		elseif($this->ext==".mp4")	{ $result = "video/mp4"; }
		elseif($this->ext==".avi")	{ $result = "video/avi"; }
		elseif($this->ext==".flv")	{ $result = "video/x-flv"; }
		elseif($this->ext==".ogv")	{ $result = "video/ogg"; }
		elseif($this->ext==".webm")	{ $result = "video/webm"; }
		
		return $result;
	}	
	
	function findNextParent($directoryAgoraProjectFind)
	{		
		$result = $this->directoryAgoraProjectParent;
		
		while ($result != NULL && $result->getParent() != $directoryAgoraProjectFind)
			$result = $result->getParent();
		
		return $result;		
	}
	
	function generateRealPath()
	{
		$result = "../stock_fichiers/gestionnaire_fichiers";
		
		//$result = PATH_MOD_FICHIER;
		
		$currentParent = $this->findNextParent(NULL);
		
		do
		{
			$currentParent = $this->findNextParent($currentParent);
			$result = $result.'/'.$currentParent->getIdPath();
		}while ($currentParent != $this->directoryAgoraProjectParent);
		
		$result = $result.'/'.$this->nom_reel;
		
		return $result;
	}
	
	function get() {	
		$result = NULL;
		$realPath = $this->generateRealPath();
		
		if (file_exists($realPath))
			$result = fopen($realPath, 'r');
		else 	
			throw new Exception\Forbidden('Permission denied to read this file');
		
		return $result;	
	}
}