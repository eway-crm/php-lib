![eWay-CRM Logo](https://www.eway-crm.com/wp-content/themes/eway/img/logo_new-new.svg)
# eWay-CRM API
API used for communication with [eWay-CRM](http://www.eway-crm.com/) web service.

## Establishing connection
To communicate eWay-CRM web service, we first have to establish connection. This must be done prior to every action we want to accomplish with use of the web service. To do that, we have to load the ```eway.class.php``` and create new instance of ```eWayConnector()``` with three parameters: service url address (same as the one you use in outlook), username and password. 

```php

// Load API
require_once "eway.class.php";

// Create connector
$connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');

```

⚠️ This connector does not support [Microsoft Account Authenticaion](https://kb.eway-crm.com/documentation/2-installation/2-3-installation-the-server-part/adjust-eway-crm-web-service-for-azure-login-office-365?set_language=en). If you log into eWay-CRM with your Microsoft account, this connector will not work.

## Simple actions with the eWay-CRM API
You can check actions available on your service on ```[service adress]/API.svc/help```. If the help is not enabled on your API have a look at [instructions](https://kb.eway-crm.com/faq-1/tips/how-to-activate-eway-crm-api-help) to activate it. You can also see [help](https://trial.eway-crm.com/31994/API.svc/help) of the sample web service.
We have put together a list of examples for some basic actions you can use the service for, so don't be shy an try it out.

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

### [Manipulation with additional fields](Examples/AdditionalFields/README.md)<br />
Example showcasing manipulation with additional fields.<br />
Sample code [here](Examples/AdditionalFields/sample_code.php).

## Data changes over time and conflicts
eWay-CRM server component (web service) stores data uploaded from various clients (Outlook Addin, Mobile App, API...). One of the main features of eWay-CRM is sharing data among users (among clients). When permissions configuration allows, multiple users have the possibility to modify the same data records at the same time. Making a change at the same time means to load the record, change it and save it while another client is doing the same steps and loads the data before the first client saves it. Because of the client software’s ability to work offline, this situation comes up more often than one would expect.

eWay-CRM deals with this subject in a similar way to Subversion (SVN) or Git. Every data record has its own revision number called `ItemVersion`. This field contains integer, which is increased on every change made to the item. Every client software should consider the very latest revision of the data record before any change is uploaded to the server. Then by uploading the data record with the field `ItemVersion` increased by one, the client tells the server that it has taken the latest revision into account. The server processes the uploads sequentially. Hence, when two clients change the same item at the same time, there is always one client who loses – does not actually take the change made by the faster client into account. This slower client uploads the `ItemVersion` lower or equal to the current state. The server component does not allow such uploads and returns error code `rcItemConflict` (or `rcItemAlreadyUploaded`).

The logic described above implies that these conflicts must be solved on the client side. eWay-CRM for MS Outlook does it in cooperation with the user (see more in [eWay-CRM Documentation](https://kb.eway-crm.com/documentation/3-description/3-3-item-working-window/item-conflict?set_language=en) ). Nevertheless, eWay-CRM API is a middle-layer software between eWay-CRM server component and 3rd party clients. By default, the API solves these conflicts for you.

Of course, you always have the option to not specify `ItemVersion` field at all. In that case the API determines the right `ItemVersion` for you and works with the incremented value. No conflict appears on the background then. When you set the version integer high enough, no conflict solving is needed as well. When you upload an item with `ItemVersion` lower or equal to the current server state, the API solves the conflict by merging the uploaded data with the data stored on the server. For example, if you download the item, change something and send it back without creating a new object, you probably send back the same `ItemVersion` as you downloaded. API will do the merging in this saving without you even notice.

How does this automatic merging work? Very simply. The data sent into the API always win, except of nulls. In other words, the API writes all the changes into database except the fields where an existing value would be erased. 

Wanna see it for real? Check this [example](Examples/EditDieOnConflictFalse) out.

If you want to make sure no merge is done or you just want to really take the very latest version into account, you can always switch the conflicts on by specifying the `dieOnItemConflict` flag. Then you will get the return codes `rcItemConflict` and `rcItemAlreadyUploaded` and you will have to deal with them yourself. The usage of this flag is shown in this [example](Examples/EditDieOnConflictTrue).

## Folder names
To ease understanding folder names, look [here](FolderNames.md).

## Sessions
This php class handles eWay-CRM API sessions automatically. It opens an API session once the first call is done. Use one instance of this class for all the calls you are going to make in order to not open a new session for each call. If you still get the error `There is too many sessions`, make sure you close the API session with `logOut()` method at the end of the class instance lifetime.