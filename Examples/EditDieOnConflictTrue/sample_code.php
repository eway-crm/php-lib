<?php

    // Load API
    require_once "eway.class.php";
    
    // Connect to API and set dieOnItemConflict to true
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM', false, true);
    
    // Lets create new company to have something to edit
    $company = array(
                        'FileAs' => 'Monsters Inc.', 
                        'CompanyName' => 'Monsters Inc.',
                        'Purchaser' => '1',
                        'Phone' => '544 727 379',
                        'Email' => 'info@monsters.com'
                        );
    $companyGuid = $connector->saveCompany($company)->Guid;
    $loadedCompany = $connector->getCompaniesByItemGuids(array($companyGuid))->Data[0];
    $companyItemVersion = $loadedCompany->ItemVersion;
    var_dump($loadedCompany);
    
    echo('<h2>---</h2>');
    
    // Edit company fields - fails due to too low item version.
    try {
        $company = array(
                            'ItemGUID' => $companyGuid,
                            'ItemVersion' => $companyItemVersion,
                            'Phone' => null,
                            'Email' => 'support@monsters.com'
                            );
        $connector->saveCompany($company);
    } catch( Exception $ex) {
        echo('<p style="color: #ff0000;">'.$ex->getMessage().'</p>');
    }    
    $loadedCompany = $connector->getCompaniesByItemGuids(array($companyGuid))->Data[0];
    $companyItemVersion = $loadedCompany->ItemVersion;
    var_dump($loadedCompany);
    
    echo('<h2>---</h2>');
    
    // Edit company fields - with correct item version.
    try {
        $company = array(
                            'ItemGUID' => $companyGuid,
                            'ItemVersion' => $companyItemVersion + 1,
                            'Phone' => null,
                            'Email' => 'support@monsters.com'
                            );
        $connector->saveCompany($company);
    } catch( Exception $ex) {
        echo('<p style="color: #ff0000;">'.$ex->getMessage().'</p>');
    }    
    $loadedCompany = $connector->getCompaniesByItemGuids(array($companyGuid))->Data[0];
    $companyItemVersion = $loadedCompany->ItemVersion;
    var_dump($loadedCompany);
    
    echo('<h2>---</h2>');
    
    // Edit company fields - with no item version.
    try {
        $company = array(
                            'ItemGUID' => $companyGuid,
                            'Phone' => '+1 (123) 654-789',
                            'Email' => null
                            );
        $connector->saveCompany($company);
    } catch( Exception $ex) {
        echo('<p style="color: #ff0000;">'.$ex->getMessage().'</p>');
    }    
    $loadedCompany = $connector->getCompaniesByItemGuids(array($companyGuid))->Data[0];
    $companyItemVersion = $loadedCompany->ItemVersion;
    var_dump($loadedCompany);
    
    // Clean the mess.
    $connector->deleteCompany($companyGuid);
    
?>