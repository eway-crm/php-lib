<?php
    
    // Load API
    require_once "../../eway.class.php";
    
    // Create connector
    $connector = new eWayConnector('https://free.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // Edit the company
    $company = array(
                      'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                      'FileAs' => 'CompanyK', 
                      'CompanyName' => 'CompanyK',
                      'Purchaser' => '1',
                      'Phone' => '202202202',
                      'Email' => 'randomCompanyEmail@company.cz'
                    );

    $connector->saveCompany($company);
 
?>