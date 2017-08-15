# eWay-CRM API
API used for communication with [eWay-CRM](http://www.eway-crm.com) web service. See our [documentation](https://kb.eway-crm.com/documentation/6-add-ins/6-7-api-1) for more information.

## Usage

Below is example of how to create a new item (company).

Prepare Company item, set your attributes, create connector and call save method.

```php
<?php
    // Header is set only for correct output behavior
    header('Content-type: text/html; charset=UTF-8');
    
    // This is new company, that we want to create
    $newCompany = array(
                        'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                        'FileAs' => 'Company', 
                        'CompanyName' => 'Company',
                        'Purchaser' => '1',
                        'Phone' => '111 222 333',
                        'Email' => 'Email@company.com',
                        'ItemVersion' => '1'
                        );

    require_once "eway.class.php";

    $connector = new eWayConnector('ServiceAddress/Service.svc/', 'admin', 'password');
    
    // Try to save new company
    $connector->saveCompany($newCompany);

    /*
        This is example of given result:
            public 'Description' => null
            public 'ReturnCode' => string 'rcSuccess' (length=9)
            public 'Guid' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
    */ 
?>
```

Next there is example of how to edit an item (company).

It is basically the same as the previous sample. The only difference is handling ItemVersion. If you do not specify ItemVersion, item will be merged as shown in this example.
```php
<?php
    // Lets say that you have already created company with these attributes and now you want to edit that company
    /*
       public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
       public 'ItemVersion' => int 1
       public 'FileAs' => string 'Company' (length=14)
       public 'CompanyName' => string 'Company' (length=13)
       public 'Email' => string 'Email@company.com' (length=17)
       public 'Phone' => string '111 222 333' (length=11)
       public 'Purchaser' => boolean true
    */
    
    // Create connector
    $connector = new eWayConnector('ServiceAddress/Service.svc/', 'admin', 'password');
    
    // Edit the company
    $company = array(
                      'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                      'FileAs' => 'Company', 
                      'CompanyName' => 'Company',
                      'Purchaser' => '1',
                      'Phone' => 'null',
                      'Email' => 'randomCompanyEmail@company.com'
                    );

    $connector->saveCompany($company);

    // And this is result of saving. As you can see, null property of phone has been ignored and email has been overwriten.
    /*
          public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
          public 'ItemVersion' => int 2
          public 'FileAs' => string 'Company' (length=14)
          public 'CompanyName' => string 'Company' (length=13)
          public 'Email' => string 'TestEmail@company.com' (length=21)
          public 'Phone' => string '111 222 333' (length=11)
          public 'PhoneNormalized' => string '111222333' (length=9)
          public 'Purchaser' => boolean true
    */      
?>
```

If you want to edit an item and overwrite all given attributes, you have to specify ItemVersion of object. Below is a modification of previous example.

```php
<?php
    // Lets say that you have already created company with these attributes and now you want to edit that company
    /*
       public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
       public 'ItemVersion' => int 1    (ItemVersion of our object is now set to 1)
       public 'FileAs' => string 'Company' (length=14)
       public 'CompanyName' => string 'Company' (length=13)
       public 'Email' => string 'Email@company.com' (length=17)
       public 'Phone' => string '111 222 333' (length=11)
       public 'Purchaser' => boolean true
    */

    // Create connector
    $connector = new eWayConnector('ServiceAddress/Service.svc/', 'admin', 'password');
    
    // Edit our created company and increase ItemVersion.
    $company = array(
                      'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                      'FileAs' => 'Company', 
                      'CompanyName' => 'Company',
                      'Purchaser' => '1',
                      'Phone' => 'null',
                      'Email' => 'randomCompanyEmail@company.com',
                      'ItemVersion => '2'
                    );

    $connector->saveCompany($company);

    // And this is result of saving. As you can see, null property of phone has been edited and email has been overwriten.
    /*
          public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
          public 'ItemVersion' => int 2
          public 'FileAs' => string 'Company' (length=14)
          public 'CompanyName' => string 'Company' (length=13)
          public 'Email' => string 'TestEmail@company.com' (length=21)
          public 'Phone' => null
          public 'PhoneNormalized' => string '111222333' (length=9)
          public 'Purchaser' => boolean true
    */      
?>
```

If you want to handle conflicts yourself, set dieOnItemConflict.

### Examples of dieOnItemConflict using

DieOnItemConflict parameter gives you full control over item conflicts.

This example shows saving and updating of company with dieOnItemConflict set to false. 

First we prepare company attributes that we want to save and set ItemVersion to 1. This signalize that company is new and should be created. After that we create connector to web service with service url address, login name, login password, bool if password is already encrypted and dieOnItemConflict bool parameters. And when connector is created, save method is called. Api takes request, sees that item sould be saved, searches if item with given guid does exist. If yes, items are merged, if not, item is created. Because ItemVersion does not change before second saving, merge will occur, because item has already been created. If you increase item version before saving, item will be overwritten by new attributes and merge will not occur.

```php
<?php
    // Header is set only for correct output behavior
    header('Content-type: text/html; charset=UTF-8');
    
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

    require_once "eway.class.php";
    
    // Create connector and set dieOnItemConflict to false
    $connector = new eWayConnector('ServiceAddress/Service.svc/', 'admin', 'password', false, false);
    
    // Try to save new company
    $connector->saveCompany($newCompany);

    /*
        This is example of given result:
            public 'Description' => null
            public 'ReturnCode' => string 'rcSuccess' (length=9)
            public 'Guid' => string 'b8f6b5e2-8fdb-41f9-9aa5-51142a92d35e' (length=36)
    */ 

    // If you try to create the same company again, result will be the same. But the object itself is handled not like creation but as editing of an object. So ItemVersion is increased automatically.
    $connector->saveCompany($newCompany);

    /*
        This is example of given result:
            public 'Description' => null
            public 'ReturnCode' => string 'rcSuccess' (length=9)
            public 'Guid' => string 'b8f6b5e2-8fdb-41f9-9aa5-51142a92d35e' (length=36)
    */
?>
```

This example shows saving and updating of company with dieOnItemConflict set to true.

Now we have the same situation, the only difference is in dieOnItemConflict, that is set to true this time. As before we prepare company, set all atributes that we need, create connector, call method. Api takes request and searches if guid has not been used yet. In case that guid have not been used yet, item is created, otherwise, service returns rcItemAlreadyUploaded.

```php
<?php
    // Header is set only for correct output behavior
    header('Content-type: text/html; charset=UTF-8');
    
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

    require_once "eway.class.php";
    // Create connector and set dieOnItemConflict to true
    
    $connector = new eWayConnector('ServiceAddress/Service.svc/', 'admin', 'password', false, true);
    
    // Try to save new company.
    $connector->saveCompany($newCompany);

    /*
        This is example of given result:
            public 'Description' => null
            public 'ReturnCode' => string 'rcSuccess' (length=9)
            public 'Guid' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
    */ 

    // If you try to create the same company again, rcItemAlreadyUploaded error will be returned.
    $connector->saveCompany($newCompany);

    /*
        This is example of given result:
            public 'Description' => string 'Web service returned an error (rcItemAlreadyUploaded): Item with the same GUID 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' has already been uploaded' (length=143)
            public 'ReturnCode' => string 'rcItemAlreadyUploaded' (length=21)
            public 'Guid' => string '00000000-0000-0000-0000-000000000000' (length=36)
    */
?>
```

This example shows editing of company with dieOnItemConflict set to false.

Now we want to edit company, that already exists and dieOnItemConflict is false. First of all we create new company and then we will edit it. As you can see, ItemVersion is missing. This request is handled as we described before. Item by guid is not found, item is created. After that we edit attributes and process saving again. Item will be found this time and because ItemVersion is not specified, merge will occur.

```php
<?php
    // Header is set only for correct output behavior
    header('Content-type: text/html; charset=UTF-8');
    
    // Lets create new company to have something to edit
    $company = array(
                        'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                        'FileAs' => 'Company', 
                        'CompanyName' => 'Company',
                        'Purchaser' => '1',
                        'Phone' => '111 222 333',
                        'Email' => 'Email@company.com',
                        );

    require_once "eway.class.php";
    
    // Create connector and set dieOnItemConflict to false
    $connector = new eWayConnector('ServiceAddress/Service.svc/', 'admin', 'password');
    
    // Try to save new company
    $connector->saveCompany($company);

    /*
          public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
          public 'ItemVersion' => int 1
          public 'FileAs' => string 'Company' (length=14)
          public 'CompanyName' => string 'Company' (length=13)
          public 'Email' => string 'Email@company.com' (length=17)
          public 'Phone' => string '111 222 333' (length=11)
          public 'Purchaser' => boolean true
    */

    // Now lets edit our created company
    $company['Email'] = 'randomCompanyEmail@company.com';
    $company['Phone'] = null;

    // Our item version is still 1 - not increased.
    // Api handles this request by dieItemOnConflict set to false. So Api will process little merge between versions and old data will be replaced by new.
    // If you send null value (as we did with Phone), merge does not change value inserted before, everything else is changed.
    $connector->saveCompany($company);

    /*
          public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
          public 'ItemVersion' => int 2
          public 'FileAs' => string 'Company' (length=14)
          public 'CompanyName' => string 'Company' (length=13)
          public 'Email' => string 'TestEmail@company.com' (length=21)
          public 'Phone' => string '111 222 333' (length=11)
          public 'PhoneNormalized' => string '111222333' (length=9)
          public 'Purchaser' => boolean true
    */      

    // ReturnCode is rcSuccess in both save situations.
?>
```

Now we want to edit company, that already exists and dieOnItemConflict is true. First of all we create new company and edit the company later. As you can see, ItemVersion is not missing this time, because api would not let you create or edit item without specifying ItemVersion, when dieOnItemConflict is true. If Item is not found, item will be created, and if item is found, ItemVersions are compared. In case that your new ItemVersion is not higher, rcItemConflict is returned, and in case it is higher, item is overwritten.

```php
<?php
    // Header is set only for correct output behavior
    header('Content-type: text/html; charset=UTF-8');
    
    // Lets create new company to have something to edit
    $company = array(
                        'ItemGUID' => 'ebdd18f3-92e9-412d-afec-e1aaf6139b09',
                        'FileAs' => 'Company', 
                        'CompanyName' => 'Company',
                        'Purchaser' => '1',
                        'Phone' => '111 222 333',
                        'Email' => 'Email@company.com',
                        'ItemVersion' => '1'
                        );

    require_once "eway.class.php";
    
    // Create connector and set dieOnItemConflict to true
    $connector = new eWayConnector('http://localhost:56537/Service.svc/', 'admin', 'heslo', false, true);
    
    // Try to save new company
    $connector->saveCompany($company);

    /*
          public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
          public 'ItemVersion' => int 1
          public 'FileAs' => string 'Company' (length=14)
          public 'CompanyName' => string 'Company' (length=13)
          public 'Email' => string 'Email@company.com' (length=17)
          public 'Phone' => string '111 222 333' (length=11)
          public 'Purchaser' => boolean true
    */

    // Now lets edit our created company
    $company['Email'] = 'TestEmail@company.com';
    $company['Phone'] = null;

    // Our item version is still 1 - not increased.
    // With dieItemOnConflict true, api returns ReturnCode = rcItemConflict, no changes are made.
    $connector->saveCompany($company);

    /*
          public 'Description' => string 'Web service returned an error (rcItemConflict): ItemVersion of item 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' is set to '1' while current item modified by 'a71c4a87-f360-4f67-8fce-e99f48c6e4fb' has version '2', you have to increase the version by one during edit
          public 'ReturnCode' => string 'rcItemConflict' (length=14)
          public 'Guid' => string '00000000-0000-0000-0000-000000000000' (length=36)

          public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
          public 'ItemVersion' => int 2
          public 'FileAs' => string 'Company' (length=14)
          public 'CompanyName' => string 'Company' (length=13)
          public 'Email' => string 'TestEmail@company.com' (length=21)
          public 'Phone' => string '111 222 333' (length=11)
          public 'Purchaser' => boolean true
    */      
?>
```