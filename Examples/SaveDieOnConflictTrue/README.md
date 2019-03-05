# Creating items with dieOnItemConflict set to true

Now we have the same situation as  in previous example, the only difference is in dieOnItemConflict, that is set to true this time. As before we prepare company, set all atributes that we need, create connector, call method. Api takes request and searches if guid has not been used yet. In case that guid have not been used yet, item is created, otherwise, service returns rcItemAlreadyUploaded.

```php

//Connect to API and set dieOnItemConflict to true
$connector = new eWayConnector('https://trial.eway-crm.com/31994/WcfService/Service.svc/', 'api', 'ApiTrial@eWay-CRM', false, true);

 // This is new company, that we want to create.
$newCompany = array(
                    'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                    'FileAs' => 'Company', 
                    'CompanyName' => 'Company',
                    'Purchaser' => '1',
                    'Phone' => '111 222 333',
                    'Email' => 'Email@company.com',
                    'ItemVersion' => '1'
                    );

// Try to save new company
$connector->saveCompany($newCompany);

```

This is example of given result:
```console

object(stdClass)[2]
  public 'Description' => null
  public 'ReturnCode' => string 'rcSuccess' (length=9)
  public 'Guid' => string 'b8f6b5e2-8fdb-41f9-9aa5-51142a92d35e' (length=36)
  public 'IsUserMessageOptionalError' => null
  public 'UserMessage' => null

```

If you try to create the same company again, rcItemAlreadyUploaded error will be returned.
```console

object(stdClass)[2]
  public 'Description' => string 'Web service returned an error (rcItemAlreadyUploaded): Item with the same GUID 'ebdd18f3-92e9-412d-afec-		e1aaf6139b09' has already been uploaded' (length=143)
  public 'ReturnCode' => string 'rcItemAlreadyUploaded' (length=21)
  public 'Guid' => string '00000000-0000-0000-0000-000000000000'

```

## Sample code
To see the whole sample code click [here](sample_code.php)