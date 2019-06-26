<?php
         
    // Load API
    require_once ('../../eway.class.php');
    
    // Connect to API
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // Search for all additiona fields
    $additionalFields = $connector->getAdditionalFields();
    
    //Create container for field names
    $additionalFieldsNames = array();
    
    // Create array of names for easier manipulation
    foreach ($additionalFields->Data as $field)
    {
        // Take to acount only fields which belong to company
        if($field->ObjectTypeFolderName == 'Companies')
        {
            $additionalFieldsNames[$field->FileAs] = 'AF_'.$field->FieldId;
        }
    }
    
    // Here we prepare criteria of enum type search
    $criteria = array(
                       'EnumName' => $additionalFieldsNames['Enum']
                    );
    
    // Search enum type of our enum additional field
    $enumType = $connector->searchEnumTypes($criteria);
    
    // Here we prepare criteria of enum values search
    $criteria = array(
                       'EnumType' => $enumType->Data[0]->ItemGUID
                    );
    
    // Search Enum type of our enum additional field
    $enumValues = $connector->searchEnumValues($criteria);
    
    //Prepare container for enum values
    $enumValuesOptions = array();
    
    // Create array of enum values
    foreach ($enumValues->Data as $value)
    {
        $enumValuesOptions[$value->FileAs] = $value->ItemGUID;
    }
    
    // Here we prepare criteria of enum type search
    $criteria = array(
                       'EnumName' => $additionalFieldsNames['MultiDropDown']
                    );
    
    // Search enum type of our MultiDropDown additional field
    $enumType = $connector->searchEnumTypes($criteria);
    
    // Here we prepare criteria of MultiDropDown values search
    $criteria = array(
                       'EnumType' => $enumType->Data[0]->ItemGUID
                    );
    
    // Search Enum type of our MultiDropDown additional field
    $enumValues = $connector->searchEnumValues($criteria);
    
    
    // Prepare container for values
    $multiDropDownValues = array();
    
    // Create value for the MultiDropDown
    foreach($enumValues->Data as $value)
    {
        array_push($multiDropDownValues, $value->ItemGUID); 
    }
    
        
    // This is new journal we want to create
    $newJournal = array(
                        'FileAs' => 'Journal of Company',
                        'Note' => 'this is journal of Company.'
                        );
    
    // Try to save new journal
    $journal = $connector->saveJournal($newJournal);
    
    // Fill the additional fields
    $additionalFieldsValues = array(
                                    $additionalFieldsNames['Number'] => '7',
                                    $additionalFieldsNames['Date'] => '1970-01-01',
                                    $additionalFieldsNames['Enum'] => $enumValuesOptions['Option 2'],
                                    $additionalFieldsNames['MultiDropDown'] => $multiDropDownValues,
                                    $additionalFieldsNames['Relation'] => $journal->Guid
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

?>