<?php
         
    //Load API
    require_once ('eway-crm-php-lib/eway.class.php');
    
    // This is new company, that we want to create
    $newCompany = array(
                        'FileAs' => 'Company', 
                        'CompanyName' => 'Company',
                        'Purchaser' => '1',
                        'Phone' => '111 222 333',
                        'Email' => 'Email@company.com'
                        );
    
    //Connect to API
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // Try to save new company
    $connector->saveCompany($newCompany);

?>