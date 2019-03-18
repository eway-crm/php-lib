
# Creating items with dieOnItemConflict set to true

Now we have the same situation as  in [previous example](../SaveDieOnConflictFalse), the only difference is in dieOnItemConflict, that is set to true this time. As before we prepare company, set all atributes that we need, create connector, call method. In case of item with same GUID not yet existing, item is created, otherwise, service returns rcItemAlreadyUploaded.

```php

//Connect to API and set dieOnItemConflict to true
$connector = new eWayConnector('https://trial.eway-crm.com/31994/WcfService/Service.svc/', 'api', 'ApiTrial@eWay-CRM', false, true);

// This is new company, that we want to create.
$newCompany = array(
                    'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                    'FileAs' => 'Monsters Inc.', 
                    'CompanyName' => 'Monsters Inc.',
                    'Purchaser' => '1',
                    'Phone' => '131 522 348',
                    'Email' => 'info@monsters.com',
                    'ItemVersion' => '1'
                    );

// Try to save new company
$connector->saveCompany($newCompany);

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