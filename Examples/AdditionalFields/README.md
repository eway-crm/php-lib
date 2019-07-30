

# Manipulating with additional fields
This example should provide some insight into manipulation with additional fields, namely fields of numeric, date, enum, relation and multi dropdown type. (This example is assuming that you know names of additional fields and in case of selections,  names of the options you want to choose. These can be found in eWay-CRM administration application.)

## Create company with additional fields
Here we prepare array with all the additional fields. Number and date takes values as usual. Enum field will be filled with option we choose.Next is the relation with GUID of the journal that we created as value.And last is DropDown with array of Dropdown options of our choice as value. Now we have everything prepared, we create array with specifications of company. Here we can use our additional fields array as value for "AditionalFields". Then we save the company by  `$connector->saveCompany()`. (Getting values for enum and dropdown fields can get a little bit tricky, so we have prepared two functions to ease the access. Only thing left up to you is to fill the name of additional field and option that you want to put in it.)
```php
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
```

## Output
As a result, you should see the newly created company with filled additional fields in your outlook.
![example output](Images/sample_output.PNG)

In this example we also added raw data output done by `var_dump($connector->searchCompanies())` with GUID of the newly created company as parameter for you to see how the additional fields looks on the company.
```console
object(stdClass)[2]
  public 'af_25' => float 7
  public 'af_26' => string '1970-01-01 00:00:00Z' (length=20)
  public 'af_27' => string '2496b50a-09ae-4804-bcdf-03e79bbd014d' (length=36)
``` 

## Sample code
To see the whole sample code click [here](sample_code.php).

## Folder name
To ease understanding folder names, look [here](../../FolderNames.md).