<?php
    
    //Load API
    require_once ('eway-crm-php-lib/eway.class.php');
    
    //Variable for our output
    $table =   '<style type="text/css">
    
                    table {
                        border-collapse: collapse;
                    }
                    
                    th, td {
                        padding: 15px;
                        text-align: left;
                    }
                    
                    tr:nth-child(even) {background-color: #f2f2f2;}
                    
                </style>';
    
    //Known company name
    $company_name = 'Chemel & Peterson LLC';
    
    //Connect to API
    $connector = new eWayConnector('https://trial.eway-crm.com/31994/WcfService/Service.svc', 'api', 'ApiTrial@eWay-CRM');
    
    //Get data of the company ("true" parameter determines wether we get relation data of searched company)
    $object = $connector->searchCompanies(array('FileAs' => $company_name), true);
    
    //Open <table> tag for our output
    $table .= '<table align="center">';
    
    //Head of our output table
    $table .=  '<tr>
                    <td style="border: 1px solid black;"><b>Name</b></td>
                    <td style="border: 1px solid black;"><b>Adress</b></td>
                    <td style="border: 1px solid black;"><b>Telephone</b></td>
                    <td style="border: 1px solid black;"><b>Email</b></td>
                </tr>';
                
    //Container for GUIDS            
    $contactsGUIDS = array();
    
    //List through company relations (relations are actualy on 3rd depth of $object)
    foreach ($object->Data[0]->Relations as $key => $relation)
	{
        //We are looking for relation which is labeled GENERAL or COMPANY and is leding to Contact
        if (($relation->RelationType === 'GENERAL' || $relation->RelationType === 'COMPANY') && $relation->ForeignFolderName === 'Contacts')
		{    
            //Store the GUID of the contact
            array_push($contactsGUIDS, $relation->ForeignItemGUID);
        }
    }
    
    //Get data of contacts
    $contacts = $connector->getContactsByItemGuids($contactsGUIDS, true);
    
    //List through contacts (data itself are on 2nd depth of object)
    foreach ($contacts->Data as $contact)
	{  
        $table .= '<tr>'; //Open new table row
        
        //Put the contact information we want into table cells 
        $table .=  '<td style="border: 1px solid black;">'.$contact->FirstName.' '.$contact->LastName.'</td>
                    <td style="border: 1px solid black;">'.$contact->BusinessAddressCity.'<br>'.$contact->BusinessAddressStreet.' '.$contact->BusinessAddressPostalCode.'</td>
                    <td style="border: 1px solid black;">'.$contact->TelephoneNumber1.'</td>
                    <td style="border: 1px solid black;">'.$contact->Email1Address.'</td>';
        
        $table .= '</tr>'; //Close the table row
    }
    
    //Close the <table> tags
    $table .= '</table>';
    
    //Show the table with output
    echo $table;
    
?>