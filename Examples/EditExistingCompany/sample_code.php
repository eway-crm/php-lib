<?php
    
    //Load API
    require_once "eway.class.php";
    
    // Create connector
    $connector = new eWayConnector('ServiceAddress/Service.svc/', 'username', 'password');
    
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