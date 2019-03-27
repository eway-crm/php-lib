<?php

    // Load API
    require_once "../../eway.class.php";
    
    // Create connector
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // GUID of company
    $company = '7dfde804-2ed4-11e9-be3d-bc5ff40119b6';
    
    // Delete the company
    $output = $connector->deleteCompany($company);

?>