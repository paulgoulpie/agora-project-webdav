<?php
use Sabre\DAV;
use Sabre\DAV\Exception;
use function GuzzleHttp\Psr7\mimetype_from_filename;

require_once '../app/misc/google-api-php-client/vendor/guzzlehttp/psr7/src/functions.php';

class FileAgoraProject extends DAV\File
{

    private $strName;

    private $idFile;

    private $pdo;

    private $size;

    private $nom_reel;

    private $lastModified;

    private $directoryAgoraProjectParent;

    function __construct($idFile, $strName, $size, $nom_reel, $lastModified, $pdo, $directoryAgoraProjectParent = NULL)
    {
        $this->strName = $strName;
        $this->idFile = $idFile;
        $this->pdo = $pdo;
        $this->size = $size;
        $this->nom_reel = $nom_reel;
        $this->lastModified = $lastModified;
        $this->directoryAgoraProjectParent = $directoryAgoraProjectParent;
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
        $result = mimetype_from_filename($this->nom_reel);
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