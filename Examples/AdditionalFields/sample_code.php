<?php
         
    // Load API
    require_once ('eway.class.php');
    
    // Connect to API
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // Here we prepare criteria of additional field search
    $criteria = array(
                       'ObjectTypeId' => '14' // 14 is ObjectTypeID of Company               
                    );
    
    // Search for Additiona Fields available for company
    $additionalFields = $connector->searchAdditionalFields($criteria);
    
    // Create array of names for easier manipulation
    foreach ($additionalFields->Data as $field)
    {
        $additionalFieldsNames[$field->FileAs] = 'af_'.$field->FieldId;
    }
    
    // Here we prepare criteria of enum type search
    $criteria = array(
                       'EnumName' => 'AF'.str_replace('af', '', $additionalFieldsNames['Enum'])
                    );
    
    // Search Enum type of our Enum additional field
    $enumType = $connector->searchEnumTypes($criteria);
    
    // Here we prepare criteria of enum values search
    $criteria = array(
                       'EnumType' => $enumType->Data[0]->ItemGUID
                    );
    
    // Search Enum type of our Enum additional field
    $enumValues = $connector->searchEnumValues($criteria);
    
    // Create array of enum values
    foreach ($enumValues->Data as $value)
    {
        $enumValuesOptions[$value->FileAs] = $value->ItemGUID;
    }
    
    // Here we prepare criteria of enum type search
    $criteria = array(
                       'EnumName' => 'AF'.str_replace('af', '', $additionalFieldsNames['MultiDropDown'])
                    );
    
    // Search Enum type of our MultiDropDown additional field
    $enumType = $connector->searchEnumTypes($criteria);
    
    // Here we prepare criteria of MultiDropDown values search
    $criteria = array(
                       'EnumType' => $enumType->Data[0]->ItemGUID
                    );
    
    // Search Enum type of our MultiDropDown additional field
    $enumValues = $connector->searchEnumValues($criteria);
    
    
    // Prepare container for values
    $multiDropDownValues = array();
    
    //Create value for the MultiDropDown
    foreach($enumValues->Data as $value)
    {
        array_push($multiDropDownValues, $value->ItemGUID); 
    }
    
    // Fill the Additional Fields
    $additionalFieldsValues = array(
                                    $additionalFieldsNames['Number'] => '7',
                                    $additionalFieldsNames['Date'] => '1970-01-01',
                                    $additionalFieldsNames['Enum'] => $enumValuesOptions['Option 2'],
                                    $additionalFieldsNames['MultiDropDown'] => $multiDropDownValues
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
    
    // This is new journal we want to create
    $newJournal = array(
                        'FileAs' => 'Journal of Company',
                        'Note' => 'this is journal of Company.'
                        );
    
    // Try to save new journal
    $journal = $connector->saveJournal($newJournal);
    
    // Here we specify our relation
    $relation = array(
                      'ItemGUID1' => $company->Guid,
                      'ItemGUID2' => $journal->Guid,
                      'FolderName1' => 'Companies',
                      'FolderName2' => 'Journal',
                      'RelationType' => 'AF'.str_replace('af', '', $additionalFieldsNames['Relation'])
                      );
    
    // Save the relation
    $connector->saveRelation($relation);

?>