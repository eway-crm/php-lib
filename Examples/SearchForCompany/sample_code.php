<?php

    //Load API
    require_once "eway.class.php";
    
    //Search criteria
    $company = array(
                    'FileAs' => 'CompanyK'    
                    );
    
    // Create connector
    $connector = new eWayConnector('https://trial.eway-crm.com/31994/WcfService/Service.svc', 'api', 'ApiTrial@eWay-CRM');
    
    //Search for the company
    $output = $connector->SearchCompanies($company);
    
    print("<pre>");
    print_r($output->Data);
    print("</pre>");
    
?>