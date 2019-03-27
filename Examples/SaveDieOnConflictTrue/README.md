

# Creating items with dieOnItemConflict detection enabled

Now we have the same situation as  in [previous example](../SaveDieOnConflictFalse), the only difference is in dieOnItemConflict detection, that is enabled this time. As before we prepare company, set all atributes that we need, create connector, call method. The API checks, whether an item with this ItemGuid already exists. If the item does not exist, it is created. If the item already exists, the API returns rcItemAlreadyUploaded return code and nothing is saved/changed in the database (this error message is not stored anywhere, unless you deliberately store the output of function returning it).

```php

//Connect to API and set dieOnItemConflict to true
$connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM', false, true);

// This is new company, that we want to create.
$new_company = array(
                    'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                    'FileAs' => 'Monsters Inc.', 
                    'CompanyName' => 'Monsters Inc.',
                    'Purchaser' => '1',
                    'Phone' => '131 522 348',
                    'Email' => 'info@monsters.com',
                    'ItemVersion' => '1'
                    );

// Try to save new company
$connector->saveCompany($new_company);

```


If the company already exists, rcItemAlreadyUploaded error will be returned.
```console

object(stdClass)[2]
  public 'Description' => string 'Web service returned an error (rcItemAlreadyUploaded): Item with the same GUID 'ebdd18f3-92e9-412d-afec-		e1aaf6139b09' has already been uploaded' (length=143)
  public 'ReturnCode' => string 'rcItemAlreadyUploaded' (length=21)
  public 'Guid' => string '00000000-0000-0000-0000-000000000000'

```

## Sample code
To see the whole sample code click [here](sample_code.php)