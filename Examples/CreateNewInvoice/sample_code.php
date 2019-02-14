<?php

    //Load API
    require_once "eway.class.php";
    
    // Create connector
    $connector = new eWayConnector('https://trial.eway-crm.com/31994/WcfService/Service.svc/', 'api', 'ApiTrial@eWay-CRM');
    
    //This is new cart, that we want to create
    $newCart = array(
                     'FileAs' => 'Desired Invoice',
                     'Companies_CustomerGuid' => 'bc0c3aef-64c9-4db5-a739-370937268203',
                     'Contacts_ContactPersonGuid' => '0db3650f-bb87-4acc-96d6-9e6993cc6e61',
                     'GoodsInCart' => array(
                                          array(
                                                'Goods_GoodsInfoGuid' => '9c09e24a-3901-448f-928e-d2041d327cc7'
                                                ),
                                          array(
                                                'FileAs' => 'Service',
                                                'Name' => 'Service',
                                                'Code' => 'WRK-003'
                                                ),
                                          ),
                     'Projects_CartGuid' => '5dac8817-ac48-4469-bae3-41778042a911'
                     );
    
    //Save the Cart
    $connector->saveCart($newCart);
    
?>