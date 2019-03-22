
# Editing items with dieOnItemConflict detection enabled

We want to edit company, that already exists and dieOnItemConflict detection is turned on. First of all we create new company and edit the company later. As you can see, ItemVersion is not missing this time, because api would not let you create or edit item without specifying ItemVersion, when dieOnItemConflict detection is enabled. If Item is not found, item will be created, and if item is found, ItemVersions are compared. In case that your new ItemVersion is not higher, rcItemConflict is returned, and in case it is higher, item is overwritten.
```php

// Connect to API and set dieOnItemConflict to true
$connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM', false, true);

// Lets create new company to have something to edit
$company = array(
                    'FileAs' => 'Monsters Inc.', 
                    'CompanyName' => 'Monsters Inc.',
                    'Purchaser' => '1',
                    'Phone' => '544 727 379',
                    'Email' => 'info@monsters.com',
                    'ItemVersion' => '1'
                    );

// Try to save new company
$companyGuid = $connector->saveCompany($company);

```

Now we prepare new data and try editing the company.
```php

// Edited company fields
$companyEdit = array(
                    'ItemGUID' => $companyGuid,
                    'Phone' => '',
                    'Email' => 'support@monsters.com',
                    'ItemVersion' => '1'
                    );

// Try to edit new company
$connector->saveCompany($companyEdit);

```


 Our item version is still 1 - not increased.With dieItemOnConflict true, API returns ReturnCode = rcItemConflict, no changes are made.
```console

object(stdClass)[2]
  public 'Description' => string 'Web service returned an error (rcItemConflict): ItemVersion of item 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' is set to '1' while current item modified by 'a71c4a87-f360-4f67-8fce-e99f48c6e4fb' has version '2', you have to increase the version by one during edit
  public 'Guid' => string '00000000-0000-0000-0000-000000000000' (length=36)
  public 'ReturnCode' => string 'rcItemConflict' (length=14)

```

## Sample code
To see the whole sample code click [here](sample_code.php)