<?php
         
    // Load API
    require_once ('eway.class.php');
    
    // Connect to API
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');

    //Prepare values for multiple select
    $enumValues = loadEnumValues('AF_29', $connector)->Data;
    
    // Fill the additional fields
    $additionalFieldsValues = array(
                                    'af_25' => '7',
                                    'af_26' => '1970-01-01',
                                    'af_27' => pickEnum('Option 1', loadEnumValues('AF_27', $connector)->Data),
                                    'af_28' => '10992e33-c0d6-4a2e-b565-5babc646fd48',
                                    'af_29' => array(pickEnum('Option 1', $enumValues),
                                                     pickEnum('Option 2', $enumValues),
                                                     pickEnum('Option 3', $enumValues))
                                );

    // This is new company, that we want to create
    $newCompany = array(
                        'FileAs' => 'Company a.s.', 
                        'CompanyName' => 'Company a.s.',
                        'Purchaser' => '1',
                        'Phone' => '121 252 733',
                        'Email' => 'Email@company.com',
                        'AdditionalFields' => $additionalFieldsValues
                        );
    
    // Try to save new company
    $company = $connector->saveCompany($newCompany);
    
    // Display the raw data of company's additional fields
    var_dump($connector->searchCompanies(array('ItemGUID' => $company->Guid))->Data[0]->AdditionalFields);
    
    // Function for picking Enum option by name
    function pickEnum ($name , $values)
    {
        foreach($values as $value)
        {
            if($value->FileAs == $name)
            {
                return $value->ItemGUID;
            }
        }
    }
    
    // Function for loading Enum options by additional field name
    function loadEnumValues($fieldNumber, $connector)
    {
        $criteria = array(
                          'EnumTypeName' => $fieldNumber
                        );
        
        return $connector->searchEnumValues($criteria);
    }
?>