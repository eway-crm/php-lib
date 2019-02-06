# Searching for Company

This example should show you how to search for company based on one or more parameters.

All you need to is to make yourself an array with parameters by which you want to search company and use it as an argument of function ```$connector->SearchCompanies()``` .

```php

//Search criteria
$company = array(
                'FileAs' => 'CompanyK'    
                );

// Create connector
$connector = new eWayConnector('https://trial.eway-crm.com/31994/WcfService/Service.svc', 'api', 'ApiTrial@eWay-CRM');

//Search for the company
$output = $connector->SearchCompanies($company);

```

## Output

Result of your search is now in ```$output```. To get to it, we use ```print_r($output->Data);``` . Based on how many companies were matching search parameters, ```$output->Data``` will contain from 0 to X companies.

```php

print("<pre>");
print_r($output->Data);
print("</pre>");

```

Output on screen should look something like this:

![sample_output](Images/sample_output.PNG)

## Sample code
To see the whole sample code click [here](sample_code.php)