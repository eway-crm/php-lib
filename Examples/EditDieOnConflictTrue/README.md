
# Editing items with conflict detection enabled

We want to edit company that already exists and the conflict detection is turned on.

First of all we create new company to have something to edit. When creating new items witihout specifying `ItemGUID`, there is no need to specify `ItemVersion` as well. If we wanted the item to be created with a given item guid, `ItemVersion` should be `1`.
```php

// Connect to API and set dieOnItemConflict to true
$connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM', false, true);

// Lets create new company to have something to edit
$company = array(
                    'FileAs' => 'Monsters Inc.', 
                    'CompanyName' => 'Monsters Inc.',
                    'Purchaser' => '1',
                    'Phone' => '544 727 379',
                    'Email' => 'info@monsters.com'
                    );
$companyGuid = $connector->saveCompany($company);

```

Lets try to modify the item with obiviously too small item version. This will be a conflict with the item on the server.
```php

$company = array(
                    'ItemGUID' => $companyGuid,
                    'ItemVersion' => $companyItemVersion,
                    'Phone' => null,
                    'Email' => 'support@monsters.com'
                    );
$connector->saveCompany($company);

```
As we expected, we got the conflict error. If both (the stored and the uploaded) item versions were `1`, the error code would be `rcItemAlreadyUploaded` instead of `rcItemConflict`. But the meaning is the same.

> rcItemConflict: Web service returned an error (rcItemConflict): ItemVersion of item 'a5b6f76a-1b40-44c4-ae2c-7ab963ad313e' from folder 'Companies' is set to '2' while current item modified by 'ba3ff5df-2920-11e9-910f-00224d483d5b' has version '2', you have to increase the version by one during update

If we load the item, we can see that no field was changed.
```console

object(stdClass)[6]
  public 'ItemGUID' => string 'a5b6f76a-1b40-44c4-ae2c-7ab963ad313e' (length=36)
  public 'ItemVersion' => int 2
  public 'FileAs' => string 'Monsters Inc.' (length=13)
  public 'Email' => string 'info@monsters.com' (length=17)
  public 'Phone' => string '544 727 379' (length=11)
  public 'PhoneNormalized' => string '544727379' (length=9)
  public 'Purchaser' => boolean true

```

If we want to really save the data, we have to tell the API we know the latest version number.
```php

$company = array(
                    'ItemGUID' => $companyGuid,
                    'ItemVersion' => $companyItemVersion + 1,
                    'Phone' => null,
                    'Email' => 'support@monsters.com'
                    );
$connector->saveCompany($company);

```

This time, the saving was successful.
```console

object(stdClass)[6]
  public 'ItemGUID' => string 'a5b6f76a-1b40-44c4-ae2c-7ab963ad313e' (length=36)
  public 'ItemVersion' => int 4
  public 'FileAs' => string 'Monsters Inc.' (length=13)
  public 'Email' => string 'support@monsters.com' (length=20)
  public 'Phone' => null
  public 'PhoneNormalized' => null
  public 'Purchaser' => boolean true

```

Anyway, successful saving can be always achieved by not specifying `ItemVersion` at all. However, having the conflict detection turned on and not sending `ItemVersion` does not make any sense. The conflict errors raise only when you specify `ItemVersion`. Otherwise, the API assumes that you have no clue about items versioning.
```php

$company = array(
                    'ItemGUID' => $companyGuid,
                    'ItemVersion' => $companyItemVersion + 1,
                    'Phone' => '+1 (123) 654-789',
                    'Email' => null
                    );
$connector->saveCompany($company);

```

Again, the item was modified.
```console

object(stdClass)[6]
  public 'ItemGUID' => string 'a5b6f76a-1b40-44c4-ae2c-7ab963ad313e' (length=36)
  public 'ItemVersion' => int 6
  public 'FileAs' => string 'Monsters Inc.' (length=13)
  public 'Email' => null
  public 'Phone' => string '+1 (123) 654-789' (length=16)
  public 'PhoneNormalized' => string '001123654789' (length=12)
  public 'Purchaser' => boolean true

```

## Sample code
To see the whole sample code click [here](sample_code.php)