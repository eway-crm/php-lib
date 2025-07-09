<?php

    // Load API
    require_once "eway.class.php";
    
    // Connect to API and set dieOnItemConflict to false
    $connector = new eWayConnector('https://free.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // Lets create new company to have something to edit
    $company = array(
                        'FileAs' => 'Monsters Inc.', 
                        'CompanyName' => 'Monsters Inc.',
                        'Purchaser' => "1",
                        'Phone' => '544 727 379',
                        'Email' => 'info@monsters.com',
                        );
    $companyGuid = $connector->saveCompany($company)->Guid;
    $loadedCompany = $connector->getCompaniesByItemGuids(array($companyGuid))->Data[0];
    $companyItemVersion = $loadedCompany->ItemVersion;
    var_dump($loadedCompany);
    
    echo('<h2>---</h2>');
    
    // Edit company fields - with automerge.
    $company = array(
                        'ItemGUID' => $companyGuid,
                        'ItemVersion' => $companyItemVersion,
                        'Phone' => null,
                        'Email' => 'support@monsters.com',
                        );
    $connector->saveCompany($company);	
    $loadedCompany = $connector->getCompaniesByItemGuids(array($companyGuid))->Data[0];
    $companyItemVersion = $loadedCompany->ItemVersion;
    var_dump($loadedCompany);
    
    echo('<h2>---</h2>');
    
    // Edit company fields - with ItemVersion high enough.
    $company = array(
                        'ItemGUID' => $companyGuid,
                        'ItemVersion' => $companyItemVersion + 1,
                        'Phone' => null
                        );
    $connector->saveCompany($company);
    var_dump($connector->getCompaniesByItemGuids(array($companyGuid))->Data[0]);
    
    echo('<h2>---</h2>');
    
    // Edit company fields - with no conflicts.
    $company = array(
                        'ItemGUID' => $companyGuid,
                        'Email' => null,
                        );
    $connector->saveCompany($company);
    var_dump($connector->getCompaniesByItemGuids(array($companyGuid))->Data[0]);
    
    // Clean the mess.
    $connector->deleteCompany($companyGuid);
    
?>