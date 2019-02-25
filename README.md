![eWay-CRM Logo](https://www.eway-crm.com/wp-content/themes/eway/img/email/logo_grey.png)
# eWay-CRM API
API used for communication with [eWay-CRM](http://www.eway-crm.com/) web service. See our [documentation](https://kb.eway-crm.com/documentation/6-add-ins/6-7-api-1) for more information. 

## Establishing connection
To communicate eWay-CRM web service, we first have to establish connection. This must be done prior to every action we want to accomplish with use of the web service. To do that, we have to lad the ```eway.class.php``` and create new instance of ```eWayConnector()``` with three parameters: service url address (same as the one you use in outlook), username and password. 

```php

//Load API
require_once "eway.class.php";

// Create connector
$connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');

```

## Actions at the service
You can check actions available on your service on ```[service adress]/WcfService/Service.svc/help``` .  We have put together a list of examples for some basic actions you can use the service for, so don't be shy an try it out.

###[Create new company](Examples/CreateNewCompany/README.md)<br />
Example showcasing creation of new Company.
[Sample code](Examples/CreateNewCompany/sample_code.md)

###[Edit existing company](Examples/EditExistingCompany/README.md)<br />
Example showcasing editing existing Company.
[Sample code](Examples/CreateNewCompany/sample_code.md)

###[List all companies](Examples/ListAllCompanies/README.md)<br />
Example showcasing listing of all existing Companies.
[Sample code](Examples/CreateNewCompany/sample_code.md)

###[Search for company](Examples/SearchForCompany/README.md)<br />
Example showcasing serching for Company by parameters.
[Sample code](Examples/CreateNewCompany/sample_code.md)

###[Link existing item](Examples/LinkExistingItem/README.md)<br />
Example showcasing creation of simple relation.
[Sample code](Examples/CreateNewCompany/sample_code.md)

###[Aquire contacts by company](Examples/AquireContactsByCompany/README.md)<br />
Example showcasing listing contacts linked to company.
[Sample code](Examples/CreateNewCompany/sample_code.md)


