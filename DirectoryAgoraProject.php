<?php
use Sabre\DAV;

require_once 'FileAgoraProject.php';

class DirectoryAgoraProject extends DAV\Collection
{

    private $strPath;

    private $idPath;

    protected $pdo;

    protected $authBackend;

    private $lastModified;

    private $parent;

    private $space_id;

    function __construct($idPath, $strPath, $lastModified, $pdo, $authBackend, $parent = NULL, $space_id = NULL)
    {
        $this->strPath = $strPath;
        $this->idPath = $idPath;
        $this->pdo = $pdo;
        $this->authBackend = $authBackend;
        $this->lastModified = $lastModified;
        $this->parent = $parent;
        $this->space_id = $space_id;

        if ($this->parent != NULL && $this->space_id == NULL)
            $this->space_id = $this->parent->space_id;
    }

    function getIdPath()
    {
        return $this->idPath;
    }

    function getParent()
    {
        return $this->parent;
    }

    function getChildren()
    {
        $pdostatement = $this->pdo->prepare("
            SELECT DISTINCT `ap_fileFolder`.`_id`,
                            `ap_fileFolder`.`name`,
                            UNIX_TIMESTAMP(`ap_fileFolder`.`dateCrea`) AS `date_crea`,
                            UNIX_TIMESTAMP(`ap_fileFolder`.`dateModif`) AS `date_modif`
            FROM `ap_fileFolder`
            WHERE (_id IN
                     (SELECT _idObject AS _id
                      FROM ap_objectTarget
                      WHERE objectType='fileFolder'
                        AND _idSpace=:space_id
                        AND (target = 'spaceGuests'
                             OR target = 'spaceUsers'
                             OR target = CONCAT('U', :user_id)
                             OR target IN
                               (SELECT CONCAT('G', `_id`)
                                FROM ap_userGroup
                                WHERE `_idUsers` LIKE CONCAT('%@@', :user_id, '@@%')
                                  AND `_idSpace` = :space_id) ))
                   AND _idContainer=:root_folder_id)
            ORDER BY `ap_fileFolder`.`name` ASC
            ");

        $pdostatement->execute([
            ':space_id' => $this->space_id,
            ':user_id' => $this->authBackend->getIdUser(),
            ':root_folder_id' => $this->idPath
        ]);
        $result = $pdostatement->fetchAll();

        $children = [];

        foreach ($result as $value)
            $children[] = new DirectoryAgoraProject($value['_id'], $value['name'], (($value['date_modif'] != NULL) ? $value['date_modif'] : $value['date_crea']), $this->pdo, $this->authBackend, $this);

        $sqlFileRequest = sprintf("
            SELECT DISTINCT `ap_file`.`_id`,
                            `ap_file`.`name`,
                            `ap_file`.`octetSize`,
                            `ap_fileVersion`.`realName`,
                            UNIX_TIMESTAMP(`ap_file`.`dateCrea`) AS `date_crea`,
                            UNIX_TIMESTAMP(`ap_file`.`dateModif`) AS `date_modif`
            FROM `ap_file`,
                 `ap_fileVersion`
            WHERE (%s`ap_file`.`_idContainer` = :root_folder_id
                   AND `ap_fileVersion`.`_idFile` = `ap_file`.`_id`)
            ORDER BY `ap_file`.`name` ASC
            ", $this->idPath == 1 ? "`ap_file`.`_id` IN
                     (SELECT _idObject AS _id
                      FROM ap_objectTarget
                      WHERE objectType='file'
                        AND _idSpace=:space_id
                        AND (target = 'spaceGuests'
                             OR target = 'spaceUsers'
                             OR target = CONCAT('U', :user_id)
                             OR target IN
                               (SELECT CONCAT('G', `_id`)
                                FROM ap_userGroup
                                WHERE `_idUsers` LIKE CONCAT('%@@', :user_id, '@@%')
                                  AND `_idSpace` = :space_id)))
                   AND " : "");

        $pdostatement = $this->pdo->prepare($sqlFileRequest);

        $sql_params = [];
        $sql_params[':root_folder_id'] = $this->idPath;
        if ($this->idPath == 1) {
            $sql_params[':space_id'] = $this->space_id;
            $sql_params[':user_id'] = $this->authBackend->getIdUser();
        }

        $pdostatement->execute($sql_params);
        $result = $pdostatement->fetchAll();

        foreach ($result as $value)
            $children[] = new FileAgoraProject($value['_id'], $value['name'], $value['octetSize'], $value['realName'], (($value['date_modif'] != NULL) ? $value['date_modif'] : $value['date_crea']), $this->pdo, $this);

        return $children;
    }

    function getName()
    {
        return html_entity_decode($this->strPath);
    }

    function getLastModified()
    {
        return $this->lastModified;
    }
}