<?php

    // Load API
    require_once "eway.class.php";
    
    // Connect to API and set dieOnItemConflict to true
    $connector = new eWayConnector('https://trial.eway-crm.com/31994/WcfService/Service.svc/', 'api', 'ApiTrial@eWay-CRM');
    
    // Lets create new company to have something to edit
    $company = array(
                        'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                        'FileAs' => 'Monsters Inc.', 
                        'CompanyName' => 'Monsters Inc.',
                        'Purchaser' => boolean true,
                        'Phone' => '544 727 379',
                        'Email' => 'info@monsters.com',
                        );

    // Try to save new company
    $connector->saveCompany($newCompany);
    
    // Edited company fields
    $companyEdit = array(
                        'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                        'Phone' => 'null',
                        'Email' => 'support@monsters.com',
                        );
    
    // Try to edit new company
    $connector->saveCompany($companyEdit);
    
?>