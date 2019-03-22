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

### [Delete company](Examples/DeleteCompany/README.md)<br />
Example showcasing deletion Company.<br />
Sample code [here](Examples/DeleteCompany/sample_code.php).

### [Link existing item](Examples/LinkExistingItem/README.md)<br />
Example showcasing creation of simple relation.<br />
Sample code [here](Examples/LinkExistingItem/sample_code.php).

### [Acquire contacts by company](Examples/AcquireContactsByCompany/README.md)<br />
Example showcasing listing contacts linked to company.<br />
Sample code [here](Examples/AcquireContactsByCompany/sample_code.php).

### [Create new invoice](Examples/CreateNewInvoice/README.md)<br />
Example showcasing creation of new Invoice and items on it.<br />
Sample code [here](Examples/CreateNewInvoice/sample_code.php).

### [Changes on contacts](Examples/ChangesOnContacts/README.md)<br />
Example showcasing listing all changes on contacts from last check.<br />
Sample code [here](Examples/ChangesOnContacts/sample_code.php).

### [Create Task with Document](Examples/CreateTaskWithDocument/README.md)<br />
Example showcasing creation of task with basic link to a document.<br />
Sample code [here](Examples/CreateTaskWithDocument/sample_code.php).

### [Change Project status](Examples/ChangeProjectStatus/README.md)<br />
Example showcasing changing project status.<br />
Sample code [here](Examples/ChangeProjectStatus/sample_code.php).

### Item conflicts
How does it work exactly? Every item in eWay-CRM database has it's own revision number (like in SVN/Git) called ItemVersion. This number is increased by one everytime the item is updated. Using this number, eWay-CRM in Outlook is able to determine a conflict and show you the conflict resolving dialog (Use mine or theirs). When working with API, we don't force you to work with ItemVersion when you don't need it. When you save a record, you should send ItemVersion higher that the currently stored one (higher by one). By this you say that you have seen the revision N and the data you are sending is the revision N+1. Everything you send is saved. If you send ItemVersion lower or equal to the current or you don't send the ItemVersion at all, the system thinks that you have not seen the latest revision N. In Outlook, you would get conflict dialog. In API, to make thinkgs simplier, an automatic merge is done. The merge is simple. Every field value you are sending is saved except nulls. So your data are preserved but non of your deletings is done.

Every Save method has also a boolean flag, which turns the auto-merge off and you get a conflict error code instead.

### [Create with Item conflict detection disabled](default)(Examples/SaveDieOnConflictFalse/README.md)<br />
Example showcasing creation with Item conflict detection disabled.<br />
Sample code [here](Examples/SaveDieOnConflictFalse/sample_code.php).

### [Create with Item conflict detection enabled](Examples/SaveDieOnConflictTrue/README.md)<br />
Example showcasing creation with Item conflict detection enabled.<br />
Sample code [here](Examples/SaveDieOnConflictTrue/sample_code.php).

### [Edit with Item conflict detection disabled](default)(Examples/EditDieOnConflictFalse/README.md)<br />
Example showcasing editing with Item conflict detection disabled.<br />
Sample code [here](Examples/EditDieOnConflictFalse/sample_code.php).

### [Edit with Item conflict detection enabled](Examples/EditDieOnConflictTrue/README.md)<br />
Example showcasing editing with Item conflict detection enabled.<br />
Sample code [here](Examples/EditDieOnConflictTrue/sample_code.php).

## Folder name
To ease understanding folder names, look [here](FolderNames.md).
