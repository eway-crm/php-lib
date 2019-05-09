
# Editing items - Default behaviour (dieOnItemConflict flag off)

We want to edit company that already exists and we don't care abou the dieOnItemConflict flag (keep it on its default value - `false`). First of all we create new company and then we will edit it. As you can see, we don't care about the `ItemVersion` field as well. 

```php

// Connect to API
$connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');

// Lets create new company to have something to edit
$company = array(
                    'FileAs' => 'Monsters Inc.', 
                    'CompanyName' => 'Monsters Inc.',
                    'Purchaser' => '1',
                    'Phone' => '544 727 379',
                    'Email' => 'info@monsters.com'
                    );

// Try to save new company
$companyGuid = $connector->saveCompany($company)->Guid;

```

If load this newly created Company, we would get this:
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
Now we prepare new object and try to edit the company. Because the `ItemVersion` field is obiviously too low, merge will happen.
```php

// Edited company fields
$company = array(
                    'ItemGUID' => $companyGuid,
					'ItemVersion' => 1,
                    'Phone' => null,
                    'Email' => 'support@monsters.com'
                    );

// Try to edit new company
$connector->saveCompany($company);

```


As you can se bellow, the e-mail address was modified; however, the `Phone` field was not erased. This is the result of the automatic merging which was initiated by the `rcItemConflict` return code which the API took care about.
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

If we really want to erase the `Phone` field, we must tell the system we saw the very latest version of this data recrod. We do that by sending the `ItemVersion` field increased by one.
```php

// Edited company fields
$company = array(
                    'ItemGUID' => $companyGuid,
					'ItemVersion' => 3,
                    'Phone' => null
                    );

// Try to edit new company
$connector->saveCompany($company);

```

Voilà, the phone is not there.
```console

object(stdClass)[2]
  public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
  public 'ItemVersion' => int 3
  public 'FileAs' => string 'Monsters Inc.' (length=14)
  public 'CompanyName' => string 'Monsters Inc.' (length=13)
  public 'Email' => string 'support@monsters.com' (length=17)
  public 'Phone' => null
  public 'Purchaser' => boolean true

```

If you don't want to care about the conflict logic at all, just send no `ItemVersion` field at all.
```php

// Edited company fields
$company = array(
                    'ItemGUID' => $companyGuid,
                    'Email' => null
                    );

// Try to edit new company
$connector->saveCompany($company);

```

And the e-mail address is erased as well.
```console

object(stdClass)[2]
  public 'ItemGUID' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
  public 'ItemVersion' => int 4
  public 'FileAs' => string 'Monsters Inc.' (length=14)
  public 'CompanyName' => string 'Monsters Inc.' (length=13)
  public 'Email' => null
  public 'Phone' => null
  public 'Purchaser' => boolean true

```

## Sample code
To see the whole sample code click [here](sample_code.php)