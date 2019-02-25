# Find Contacts changed since last time
This example will show you how to find contacts which were changed or deleted since last time.The example shows two ways of doing this:

## Done in two steps
One of our options is to do the process in two steps. First step is to acquire GUIDS of contacts with use of function ```$connector->getItemChnageIdentifiers()``` . First parameter is name of the item folder, second is current revision GUID, which is supplied by function ```$connector->GetLastItemChangeId()``` and the third is current revision.


```php

//Revisions interval
$latest_revision = $connector->GetLastItemChangeId()->Datum;
$current_revision = 3000;

//Get contact GUIDS
$item_data = $connector->getItemChnageIdentifiers('Contacts', $current_revision, $latest_revision)->Data;

//Go through the contact GUIDS
foreach ($item_data as $data)
{
    //Extract the GUIDS
    array_push($contact_guids, $data->ItemGUID);
}

```

In the second step we can use our extracted GUIDS to find according contacts.

```php

//Get contacts based on guids
$contacts_from_guids = $connector->getContactsByItemGuids($contact_guids)->Data;

```

## Output

### Simple HTML table
To ease orientation in output of our search we can create simple HTML table. The output should look something like this.
![example output](Images/sample_output_one.PNG)

## Done in one step
This option will focus on getting the contacts in only one step. That can be done by function ```$connector->getChangedItems()``` which is very similar to function  ```$connector->getItemChnageIdentifiers()``` but can be supplied with multiple folder names in array and will return you even the changed items themselves (Item GUIDS in case of deletion).

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