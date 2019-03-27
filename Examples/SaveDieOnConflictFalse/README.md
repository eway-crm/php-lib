


# Creating items with item conflict detection disabled

First we prepare company attributes that we want to save and set ItemVersion to 1. This signalize that company is new and should be created. 
```php

// Connect to API
$connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
// This is new company, that we want to create
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
By specifying `ItemVersion=1` , you tell the API that you want to create a new item with the given ItemGUID. The API checks, whether an item with this ItemGuid already exists. If the item does not exist, it is created. If the item already exists, no conflict error raises and the API merges the existing item with your data.

In conclusion, creating item with same GUID as existing item will behave as [editing](../EditExistingCompany).

## Sample code
To see the whole sample code click [here](sample_code.php)