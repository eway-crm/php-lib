<?php

    class Storage
    {
        // Container for the revision number
        public $current_revision = 3000;
    
        // Method for loading current revision number
        function storeCurrentRevision($revision_number)
        {
            // TODO: Here should be code for storing the current revision number from persistent storage.
            $this->current_revision = $revision_number;
        }
        
        // Method for loading current revision number
        function loadCurrentRevision()
        {
            // TODO: Here should be code for loading the current revision number from persistent storage.
            return $this->current_revision;
        }
    }

    // Initialize storage
    $storage = new Storage();
    
    // Load API
    require_once "eway.class.php";
    
    // Variable for our output
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
    
    // Create connector
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // GUIDS container
    $contact_guids = array();
    
    // Revisions interval
    $latest_revision = $connector->getLastItemChangeId()->Datum;
    $current_revision = $storage->loadCurrentRevision();
    
    // Get contact GUIDS
    $item_data = $connector->getItemChangeIdentifiers('Contacts', $current_revision, $latest_revision)->Data;
    
    // Go through the contact GUIDS
    foreach ($item_data as $data)
    {
        // Extract the GUIDS
        array_push($contact_guids, $data->ItemGUID);
    }
    
    // Get contacts based on guids
    $contacts_from_guids = $connector->getContactsByItemGuids($contact_guids)->Data;
    
    // Open <table> tag for our output
    $table .= '<table align="center">';
    
    // Head of our output table
    $table .=  '<tr>
                    <td style="border: 1px solid black;"><b>Selected in two steps:</b></td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;"><b>Name</b></td>
                    <td style="border: 1px solid black;"><b>Address</b></td>
                    <td style="border: 1px solid black;"><b>Telephone</b></td>
                    <td style="border: 1px solid black;"><b>Email</b></td>
                </tr>';
    
    // Go through conacts
    foreach ($contacts_from_guids as $contact_from_item_guid)
    {
        $table .= '<tr>'; // Open new table row
        
        // Put the contact information we want into table cells 
        $table .=  '<td style="border: 1px solid black;">'.$contact_from_item_guid->FirstName.' '.$contact_from_item_guid->LastName.'</td>
                    <td style="border: 1px solid black;">'.$contact_from_item_guid->BusinessAddressCity.'<br>'.$contact_from_item_guid->BusinessAddressStreet.' '.$contact_from_item_guid->BusinessAddressPostalCode.'</td>
                    <td style="border: 1px solid black;">'.$contact_from_item_guid->TelephoneNumber1.'</td>
                    <td style="border: 1px solid black;">'.$contact_from_item_guid->Email1Address.'</td>';
        
        $table .= '</tr>'; // Close the table row
    }
    
    // Close the <table> tags
    $table .= '</table>';
    
    // Add space between two tables
    $table .= ' <table>
                    <tr></br></tr>
                </table>';
    
    // Get contacts
    $contacts = $connector->getChangedItems(array('Contacts'), $current_revision, $latest_revision)->Data[0]->ChangedItems;
    
    // Open <table> tag for our output
    $table .= '<table align="center">';
    
    // Head of our output table
    $table .=  '<tr>
                    <td style="border: 1px solid black;"><b>Selected in one step:</b></td>
                </tr>
                <tr>
                    <td style="border: 1px solid black;"><b>Name</b></td>
                    <td style="border: 1px solid black;"><b>Address</b></td>
                    <td style="border: 1px solid black;"><b>Telephone</b></td>
                    <td style="border: 1px solid black;"><b>Email</b></td>
                </tr>';
    
    // Go thorough contacts
    foreach ($contacts as $item)
    {
        $table .= '<tr>'; // Open new table row
        
        // Put the contact information we want into table cells 
        $table .=  '<td style="border: 1px solid black;">'.$item->FirstName.' '.$item->LastName.'</td>
                    <td style="border: 1px solid black;">'.$item->BusinessAddressCity.'<br>'.$item->BusinessAddressStreet.' '.$item->BusinessAddressPostalCode.'</td>
                    <td style="border: 1px solid black;">'.$item->TelephoneNumber1.'</td>
                    <td style="border: 1px solid black;">'.$item->Email1Address.'</td>';
        
        $table .= '</tr>'; // Close the table row
    }
    
    // Close the <table> tags
    $table .= '</table>';
    
    // Show the table with output
    echo $table;
    
    // Store revision number
    $storage->storeCurrentRevision($latest_revision);

?>