<?php

    // Load API
    require_once "../../eway.class.php";
    
    // Connect to API and set dieOnItemConflict to true
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM', false, true);
    
    // Lets create new company to have something to edit
    $company = array(
                        'FileAs' => 'Monsters Inc.', 
                        'CompanyName' => 'Monsters Inc.',
                        'Purchaser' => '1',
                        'Phone' => '544 727 379',
                        'Email' => 'info@monsters.com',
                        'ItemVersion' => '1'
                        );

    // Try to save new company
    $companyGuid = $connector->saveCompany($company);
    
    // Edited company fields
    $company = array(
                        'ItemGUID' => $companyGuid,
                        'Phone' => '',
                        'Email' => 'support@monsters.com',
                        'ItemVersion' => '1'
                        );
    
    // Try to edit new company
    $connector->saveCompany($company);
    
?>