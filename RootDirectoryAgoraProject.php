<?php

class RootDirectoryAgoraProject extends DirectoryAgoraProject
{

    function getChildren()
    {
        $pdostatement = $this->pdo->prepare("
            SELECT DISTINCT `ap_space`.`_id`,
                            `ap_space`.`name`,
                            UNIX_TIMESTAMP(`ap_space`.`dateCrea`) AS `date_crea`,
                            UNIX_TIMESTAMP(`ap_space`.`dateModif`) AS `date_modif`
            FROM `ap_space`,`ap_joinSpaceUser`
            WHERE `ap_joinSpaceUser`.`_idSpace` = `ap_space`.`_id` AND (`ap_joinSpaceUser`.`_idUser` = :user_id  OR `ap_joinSpaceUser`.`allUsers` = 1)
            ORDER BY `ap_space`.`name` ASC
            ");

        $pdostatement->execute([
            ':user_id' => $this->authBackend->getIdUser()
        ]);
        $result = $pdostatement->fetchAll();

        $children = [];
        
        if (count($result) == 1)
        {
            $directoryagoraproject = new DirectoryAgoraProject(1,NULL,NULL,$this->pdo,$this->authBackend,$this,$result[0]['_id']); 
            $children = $directoryagoraproject->getChildren();
        }
        else
        {
            foreach ($result as $value)
                $children[] = new DirectoryAgoraProject(1, $value['name'], (($value['date_modif'] != NULL) ? $value['date_modif'] : $value['date_crea']), $this->pdo, $this->authBackend, $this,$value['_id']);            
        }

        return $children;
    }
}