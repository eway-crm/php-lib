# Saving new Company
Here you can see the process of saving new company by function `$connector->saveCompany($newCompany)` .


### Company information
Create array with information you want your company to have.
```php

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

```

### Save the Company
All that is left is to save the company.
```php

// Try to save new company
$connector->saveCompany($newCompany);

```

### Output
Result of this code should be visible in eWay-CRM as a new company. If you wanted to see raw data of what the service returns, add variable for result to the function and follow it up with its `var_dump()` .
```php

$output = $connector->saveCompany($newCompany);
var_dump($connector);

```
The output should look something like this :
```php

object(stdClass)[2]
  public 'Description' => null
  public 'ReturnCode' => string 'rcSuccess' (length=9)
  public 'Guid' => string 'ebdd18f3-92e9-412d-afec-e1aaf6139b09' (length=36)
  public 'IsUserMessageOptionalError' => null
  public 'UserMessage' => null

```

