<?php

    // Load API
    require_once "../../eway.class.php";
    
    // Create connector
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // Specifications of our relation
    $relation = array(
                    'ItemGUID1'     => '129641b8-3677-11e7-9e49-080027cbca76',
                    'ItemGUID2'     => 'd9705ddc-9161-44e3-82cd-0bd0063b66f5',
                    'FolderName1'   => 'Projects',
                    'FolderName2'   => 'Contacts',
                    'RelationType'  => 'GENERAL'
                    );
    
    // Save the relation
    $output = $connector->saveRelation($relation);

?>