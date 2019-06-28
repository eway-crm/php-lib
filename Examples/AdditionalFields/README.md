
# Manipulating with additional fields
This example should provide some insight into manipulation with additional fields, namely fields of numeric, date, enum, relation and multi dropdown type.

## Searching for values of the enum additional field
We are looking for enumType of the enum additional field. We will use number of the additional field (formatted AF_ and number of the field, which you can find in administration application of eWay-CRM) as parameter of  `$connector->searchEnumTypes()` and with "ItemGUID" of the found data as parameter for `$connector->searchEnumValues()` and find the values.
```php
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
```

## Searching for values of the multi dropdown additional field
We can do that the same way as we did the enum values.  Search ''EnumType" by AF_ number with `$connector->searchEnumTypes()`, then search values themselves with `$connector->searchEnumValues()`. There is one difference..
```php
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
```

## Relation additional field value
Relation additional field require foreign key as value (GUID) so we create new journal to use its GUID as the value.
```php
// This is new journal we want to create
$newJournal = array(
                    'FileAs' => 'Journal of Company',
                    'Note' => 'this is journal of Company.'
                    );

// Try to save new journal
$journal = $connector->saveJournal($newJournal);
```

## Create company with additional fields
Now we prepare array with all the additional fields. Number and date takes values as usual. Enum field will be filled with option we choose from our prepared search.Next is the relation with GUID of the journal that we created as value.And last is DropDown with array of Dropdown options of our choice as value. Now we have everything prepared, we create array with specifications of company. Here we can use our additional fields array as value for "AditionalFields". Then we save the company by  `$connector->saveCompany()`.
```php
// Fill the additional fields
// Fill the additional fields
$additionalFieldsValues = array(
                                'af_25' => '7',
                                'af_26' => '1970-01-01',
                                'af_27' => $enumValues->Date[0]->ItemGUID,
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
```

## Sample code
To see the whole sample code click [here](sample_code.php).

## Folder name
To ease understanding folder names, look [here](../../FolderNames.md).