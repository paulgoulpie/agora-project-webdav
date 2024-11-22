<?php
use Sabre\DAV;
use Sabre\DAV\Exception;

require_once '../app/misc/google-api-php-client/vendor/guzzlehttp/psr7/src/MimeType.php';

class FileAgoraProject extends DAV\File
{

    private $strName;

    private $idFile;

    private $pdo;

    private $size;

    private $nom_reel;

    private $lastModified;

    private $directoryAgoraProjectParent;

    function __construct($idFile, $strName, $nom_reel, $lastModified, $pdo, $directoryAgoraProjectParent = NULL)
    {
        $this->strName = $strName;
        $this->idFile = $idFile;
        $this->pdo = $pdo;
        $this->nom_reel = $nom_reel;
        $this->lastModified = $lastModified;
        $this->directoryAgoraProjectParent = $directoryAgoraProjectParent;
        $this->size = filesize($this->generateRealPath());
    }

    function getName()
    {
        return html_entity_decode($this->strName);
    }

    function getSize()
    {
        return $this->size;
    }

    function getLastModified()
    {
        return $this->lastModified;
    }

    function getContentType()
    {
        $result = null;
        $result = GuzzleHttp\Psr7\MimeType::fromFilename($this->nom_reel);
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
        $result = "../DATAS/modFile";

        $currentParent = $this->findNextParent(NULL);

        do {
            $currentParent = $this->findNextParent($currentParent);
            if (!($currentParent->getParent() instanceof RootDirectoryAgoraProject))
                $result = $result . '/' . $currentParent->getIdPath();
        } while ($currentParent != $this->directoryAgoraProjectParent);

        $result = $result . '/' . $this->nom_reel;

        return $result;
    }

    function get()
    {
        $result = NULL;
        $realPath = $this->generateRealPath();        

        if (file_exists($realPath))
            $result = fopen($realPath, 'r');
        else
            throw new Exception\Forbidden('Permission denied to read this file');

        return $result;
    }
}