# Find contacts bound to company with COMPANY or GENERAL type relation
We would like to find contacts based on their relation with certain company and show them even with foreign keys. Although there is a slight obstacle in our way. We can't use function ```$eway.class->searchContacts()``` because it can't take foreign keys into notice. 

## Correct approach

### Search company and it's relations
In order to find out which contacts are bound to our chosen company, we need to use function ```$eway.class->searchCompanies()```, use name of our chosen company as first parameter and 'true' as second parameter. The second parameter is indicating, that you want to include relations of searched item. We will find GUIDS of contacts we are looking for there.

```php

//Container for search parameters (FileAs in our case)
$object = array( 'FileAs' => $company_name );
    
//Get data of the company ("true" parameter determines wether we get relation data of searched company)
$object = $connector->searchCompanies( array( 'FileAs' => $company_name ), true );

```

### Get contact GUIDS
Now we have ```$object``` containing data of  found company. By calling ```$object->Data[0]->Relations``` we get into relational data of company. All we have to do now is cycle through and pick GUID of related object which in our case must have GENERAL or COMPANY type relation and the lead to contact. 

```php

//List through company relations (relations are actualy on 3rd depth of $object)
foreach( $object->Data[0]->Relations as $key => $relation ){

    //We are looking for relation which is labeled GENERAL or COMPANY and is leding to Contact
    if( ($relation->RelationType === 'GENERAL' || $relation->RelationType === 'COMPANY') && $relation->ForeignFolderName === 'Contacts' ){
        
        //Store the GUID of the contact
        array_push( $contacts, $relation->ForeignItemGUID );
    }
    
}

```

### Get contacts
Now we have array of GUIDS of contacts. We will use it as first parameter and 'true' as second for function ```$connector->getContactsByItemGuids()```. The second parameter indicates that we want to see foreign keys of item we are looking for, which was our primary goal.
```php

//Get data of contacts
$contacts = $connector->getContactsByItemGuids( $contacts, true );

```

## Output

### Simple HTML table
To ease orientation in output of our search we can create simple HTML table. The output should look something like this.
![example output](Images/AcquireContactsByCompany.png)

### Raw output
Alternatively, you can add ```var_dump($object)``` at the end of the example code to see raw output of company. If you wanted to see detail of company relations, you can add ```var_dump($object->Data[0]->Relations)``` . For raw output of contacts we searched, we would add  ```var_dump($contacts)```  .

## Sample code
To see the whole sample code click [here](sample_code.php)

