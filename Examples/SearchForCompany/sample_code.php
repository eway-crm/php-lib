<?php

    //Load API
    require_once "eway.class.php";
    
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
    
    //Search criteria
    $company = array(
                    'FileAs' => 'Dorl & Son Inc'    
                    );
    
    // Create connector
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    //Search for the company
    $output = $connector->SearchCompanies($company);
    
    //Open <table> tag for our output
    $table .= '<table align="center">';
    
    //Head of our output table
    $table .=  '<tr>
                    <td style="border: 1px solid black;"><b>Name</b></td>
                    <td style="border: 1px solid black;"><b>Address</b></td>
                    <td style="border: 1px solid black;"><b>Telephone</b></td>
                </tr>';
                
                
    //List through results
    foreach ($output->Data as $item)
    {
        $table .= '<tr>'; //Open new table row
        
        //Put the contact information we want into table cells 
        $table .=  '<td style="border: 1px solid black;">'.$item->FileAs.'</td>
                    <td style="border: 1px solid black;">'.$item->Address1City.'<br>'.$item->Address1Street.' '.$item->Address1PostalCode.'</td>
                    <td style="border: 1px solid black;">'.$item->Phone.'</td>';
        
        $table .= '</tr>'; //Close the table row
    }
    
    //Close the <table> tags
    $table .= '</table>';
    
    //Show the table with output
    echo $table;
    
?>