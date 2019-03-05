<?php

    // Load API
    require_once "eway.class.php";
    
    // This willl be our Project
    $connector = new eWayConnector('https://trial.eway-crm.com/31994/WcfService/Service.svc/', 'api', 'ApiTrial@eWay-CRM');
    
    // This is new company, that we want to create
    $newCompany = array(
                        'ItemGUID' => 'b8f6b5e2-8fdb-41f9-9aa5-51142a92d35e',
                        'FileAs' => 'Company', 
                        'CompanyName' => 'Company',
                        'Purchaser' => '1',
                        'Phone' => '111 222 333',
                        'Email' => 'Emailusil@company.com',
                        'ItemVersion' => '1'
                        );

    // Try to save new company
    $connector->saveCompany($newCompany);
?>