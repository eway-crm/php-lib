# Creating items with dieOnItemConflict set to false

First we prepare company attributes that we want to save and set ItemVersion to 1. This signalize that company is new and should be created. After that we create connector to web service with service url address, login name, login password, bool if password is already encrypted and dieOnItemConflict bool parameters. And when connector is created, save method is called. Api takes request, sees that item sould be saved, searches if item with given guid does exist. If yes, items are merged, if not, item is created. Because ItemVersion does not change before second saving, merge will occur, because item has already been created. If you increase item version before saving, item will be overwritten by new attributes and merge will not occur.

```php

// This willl be our Project
    $connector = new eWayConnector('https://trial.eway-crm.com/31994/WcfService/Service.svc/', 'api', 'ApiTrial@eWay-CRM');
    
// This is new company, that we want to create
    $newCompany = array(
                        'ItemGUID' => 'b8f6b5e2-8fdb-41f9-9aa5-51142a92d35e',
                        'FileAs' => 'Company', 
                        'CompanyName' => 'Company',
                        'Purchaser' => '1',
                        'Phone' => '111 222 333',
                        'Email' => 'Emailusil@company.com',
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

If you try to create the same company again, result will be the same. But the object itself is handled not like creation but as editing of an object. So ItemVersion is increased automatically.

## Sample code
To see the whole sample code click [here](sample_code.php)