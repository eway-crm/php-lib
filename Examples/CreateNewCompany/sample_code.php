<?php
         
    // Load API
    require_once ('../../eway.class.php');
    
    // This is new company, that we want to create
    $newCompany = array(
                        'FileAs' => 'Company', 
                        'CompanyName' => 'Company',
                        'Purchaser' => '1',
                        'Phone' => '111 222 333',
                        'Email' => 'Email@company.com'
                        );
    
    // Connect to API
    $connector = new eWayConnector('https://free.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // Try to save new company
    $connector->saveCompany($newCompany);

?>