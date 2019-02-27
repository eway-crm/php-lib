# Find Contacts changed since last time
This example will show you how to find contacts which were changed or deleted since last time. It is expected that you have a persistent storage to store CurrentId (revision number) at your disposal. This number is symbolising a point in time (a timestamp of the current revision).

```php

//Method for loading current revision number
function loadCurrentRevision()
{
	//TODO: Here should be code for loading the current revision number from persistent storage.
	$current_revision = 3000;
	
	return current_revision;
}

```
*Note for experts: The API register changes on foreign keys on the item as item change as well, even though they are emulated.* 

The example shows two ways of doing this. One is realised in two steps, first get GUIDS of changed items, then find the item changes. The other is done in just one function. 

## Done by using changed identfiers
This is in case you want to see just identifiers of cganged items. First we acquire GUIDS of changed contacts with use of function ```$connector->getItemChnageIdentifiers()``` . First parameter is name of the item folder, second is current revision number and the third is target revision which is supplied by function ```$connector->GetLastItemChangeId()``` . Current revision number is essentially time of your last check of item changes and target revision number is time to which you want your item changes update to.


```php

//Revisions interval
$latest_revision = $connector->GetLastItemChangeId()->Datum;
$current_revision = loadCurrentRevision();

//Get contact GUIDS
$item_data = $connector->getItemChnageIdentifiers('Contacts', $current_revision, $latest_revision)->Data;

//Go through the contact GUIDS
foreach ($item_data as $data)
{
    //Extract the GUIDS
    array_push($contact_guids, $data->ItemGUID);
}

```

After that we can use our extracted GUIDS to find according contacts.

```php

//Get contacts based on guids
$contacts_from_guids = $connector->getContactsByItemGuids($contact_guids)->Data;

```

## Output

### Simple HTML table
To ease orientation in output of our search we can create simple HTML table. The output should look something like this.
![example output](Images/sample_output_one.PNG)

## Done by loading details right away
This option will get the item details one step. That can be done by function ```$connector->getChangedItems()``` which is very similar to function  ```$connector->getItemChnageIdentifiers()``` but can be supplied with folder names and will return you detail of changed items (Item GUIDS in case of deletion).

```php

//Get contacts
$contacts = $connector->getChangedItems(array('Contacts'), $current_revision, $latest_revision)->Data[0]->ChangedItems;
```

## Output

### Simple HTML table
To ease orientation in output of our search we can create simple HTML table. We are doing both options in this one example code, so there should be two tables now. The output should look something like this.
![example output](Images/sample_output_two.PNG)


## Sample code
To see the whole sample code click [here](sample_code.php)