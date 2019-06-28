<?php
         
    // Load API
    require_once ('eway.class.php');
    
    // Connect to API
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // Here we prepare criteria of enum type search
    $criteria = array(
                       'EnumName' => 'AF_27'
                    );
    
    // Search enum type of our enum additional field
    $enumType = $connector->searchEnumTypes($criteria);
    
    // Here we prepare criteria of enum values search
    $criteria = array(
                       'EnumType' => $enumType->Data[0]->ItemGUID
                    );
    
    // Search Enum type of our enum additional field
    $enumValues = $connector->searchEnumValues($criteria);
    
    // Here we prepare criteria of enum type search
    $criteria = array(
                       'EnumName' => 'AF_29'
                    );
    
    // Search enum type of our MultiDropDown additional field
    $multiDropDownType = $connector->searchEnumTypes($criteria);
    
    // Here we prepare criteria of MultiDropDown values search
    $criteria = array(
                       'EnumType' => $multiDropDownType->Data[0]->ItemGUID
                    );
    
    // Search Enum type of our MultiDropDown additional field
    $multiDropDownValues = $connector->searchEnumValues($criteria);
    
    // This is new journal we want to create
    $newJournal = array(
                        'FileAs' => 'Journal of Company',
                        'Note' => 'this is journal of Company.'
                        );
    
    // Try to save new journal
    $journal = $connector->saveJournal($newJournal);
    
    // Fill the additional fields
    $additionalFieldsValues = array(
                                    'af_25' => '7',
                                    'af_26' => '1970-01-01',
                                    'af_27' => pickEnum('Option 1', $enumValues->Data),
                                    'af_28' => $journal->Guid,
                                    'af_29' => array(pickEnum('Option 1', $multiDropDownValues->Data), pickEnum('Option 2', $multiDropDownValues->Data), pickEnum('Option 3', $multiDropDownValues->Data))
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
?>