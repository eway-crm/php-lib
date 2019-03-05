<?php

    //Load API
    require_once "eway.class.php";
    
    //Connect to API and set dieOnItemConflict to true
    $connector = new eWayConnector('https://trial.eway-crm.com/31994/WcfService/Service.svc/', 'api', 'ApiTrial@eWay-CRM', false, true);
    
     // This is new company, that we want to create.
    $newCompany = array(
                        'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                        'FileAs' => 'Company', 
                        'CompanyName' => 'Company',
                        'Purchaser' => '1',
                        'Phone' => '111 222 333',
                        'Email' => 'Email@company.com',
                        'ItemVersion' => '1'
                        );

    // Try to save new company
    $connector->saveCompany($newCompany);
    
?>