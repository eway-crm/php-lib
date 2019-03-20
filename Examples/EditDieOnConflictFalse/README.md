
# Editing items with dieOnItemConflict detection disabled

We want to edit company, that already exists and dieOnItemConflict detection id disabled. First of all we create new company and then we will edit it. As you can see, ItemVersion is missing. 

```php

// Connect to API
$connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');

// Lets create new company to have something to edit
$company = array(
                    'FileAs' => 'Monsters Inc.', 
                    'CompanyName' => 'Monsters Inc.',
                    'Purchaser' => '1',
                    'Phone' => '544 727 379',
                    'Email' => 'info@monsters.com',
                    );

// Try to save new company
$companyGuid = $connector->saveCompany($company)->Guid;

```

If we were to search this newly created Company, we would get this:
```console

object(stdClass)[2]
  public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
  public 'ItemVersion' => int 1
  public 'FileAs' => string 'Monsters Inc.' (length=14)
  public 'CompanyName' => string 'Monsters Inc.' (length=13)
  public 'Email' => string 'info@monsters.com' (length=17)
  public 'Phone' => string '544 727 379' (length=11)
  public 'Purchaser' => boolean true

```
Now we prepare new data and try editing the company. Item will be found this time and because ItemVersion is not specified, merge will occur.
```php

// Edited company fields
$companyEdit = array(
                    'ItemGUID' => $companyGuid,
                    'Phone' => '',
                    'Email' => 'support@monsters.com',
                    );

// Try to edit new company
$connector->saveCompany($companyEdit);

```


Our item version is still 1 - not increased. Api will process little merge between versions and old data will be replaced by new. If you send null value (as we did with Phone), merge does not change value inserted before, everything else is changed.
```console

object(stdClass)[2]
  public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
  public 'ItemVersion' => int 2
  public 'FileAs' => string 'Monsters Inc.' (length=14)
  public 'CompanyName' => string 'Monsters Inc.' (length=13)
  public 'Email' => string 'support@monsters.com' (length=17)
  public 'Phone' => string '544 727 379' (length=11)
  public 'Purchaser' => boolean true

```

## Sample code
To see the whole sample code click [here](sample_code.php)