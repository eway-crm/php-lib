![eWay-CRM Logo](https://www.eway-crm.com/wp-content/themes/eway/img/email/logo_grey.png)
# eWay-CRM API
API used for communication with [eWay-CRM](http://www.eway-crm.com/) web service. See our [documentation](https://kb.eway-crm.com/documentation/6-add-ins/6-7-api-1) for more information. 

## Establishing connection
To communicate eWay-CRM web service, we first have to establish connection. This must be done prior to every action we want to accomplish with use of the web service. To do that, we have to load the ```eway.class.php``` and create new instance of ```eWayConnector()``` with three parameters: service url address (same as the one you use in outlook), username and password. 

```php

// Load API
require_once "eway.class.php";

// Create connector
$connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');

```

## Actions at the service
You can check actions available on your service on ```[service adress]/WcfService/Service.svc/help``` .  We have put together a list of examples for some basic actions you can use the service for, so don't be shy an try it out.

### [Create new company](Examples/CreateNewCompany/README.md)<br />
Example showcasing creation of new Company.<br />
Sample code [here](Examples/CreateNewCompany/sample_code.php).

### [Edit existing company](Examples/EditExistingCompany/README.md)<br />
Example showcasing editing existing Company.<br />
Sample code [here](Examples/EditExistingCompany/sample_code.php).

### [List all companies](Examples/ListAllCompanies/README.md)<br />
Example showcasing listing of all existing Companies.<br />
Sample code [here](Examples/ListAllCompanies/sample_code.php).

### [Search for company](Examples/SearchForCompany/README.md)<br />
Example showcasing serching for Company by parameters.<br />
Sample code [here](Examples/SearchForCompany/sample_code.php).

### [Search for company](Examples/DeleteCompany/README.md)<br />
Example showcasing deletion Company.<br />
Sample code [here](Examples/DeleteCompany/sample_code.php).

### [Link existing item](Examples/LinkExistingItem/README.md)<br />
Example showcasing creation of simple relation.<br />
Sample code [here](Examples/LinkExistingItem/sample_code.php).

### [Aquire contacts by company](Examples/AquireContactsByCompany/README.md)<br />
Example showcasing listing contacts linked to company.<br />
Sample code [here](Examples/AquireContactsByCompany/sample_code.php).

### [Create new invoice](Examples/CreateNewInvoice/README.md)<br />
Example showcasing creation of new Invoice and items on it.<br />
Sample code [here](Examples/CreateNewInvoice/sample_code.php).

### [Changes on contacts](Examples/ChangesOnContacts/README.md)<br />
Example showcasing listing all changes on contacts from last check.<br />
Sample code [here](Examples/ChangesOnContacts/sample_code.php).

### [Create Task with Document](Examples/CreateTaskWithDocument/README.md)<br />
Example showcasing creation of task with basic link to a document.<br />
Sample code [here](Examples/CreateTaskWithDocument/sample_code.php).

### [Change Project state](Examples/ChangeProjectStatus/README.md)<br />
Example showcasing changing project state.<br />
Sample code [here](Examples/ChangeProjectStatus/sample_code.php).

## Folder name
To ease understanding folder names, look [here](FolderNames.md).
