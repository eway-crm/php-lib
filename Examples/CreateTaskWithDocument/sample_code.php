<?php

    // Load API
    require_once "../../eway.class.php";
    
    // Create connector
    $connector = new eWayConnector('https://free.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // Get user GUID
    $user_guid = $connector->searchUsers(array('FileAs' => 'Api-Tester, Robot'))->Data[0]->ItemGUID;
    
    // This will be our Task
    $task = array(
                'StartDate' => '2019-02-01 20:00:00Z',
                'DueDate' => '2019-02-02 04:00:00Z',
                'Subject' => 'TASK: Do the task',
                'FileAs' => 'TASK: Do the task',
                'Users_TaskDelegatorGuid' => $user_guid,
                'Users_TaskSolverGuid' => $user_guid
                );
    
    // Save the task
    $task_result = $connector->saveTask($task);
    
    $GUID = trim(com_create_guid(), '{}');
    
    // Save binary of the Document
    $connector->saveBinaryAttachment('Picture.PNG', $GUID);
    
    // This will be our Document
    $document = array(
                        'ItemGUID' => $GUID,
                        'FileAs' => 'Picture',
                        'DocName' => 'Picture',
                        'DocSize' => filesize('Picture.PNG'),
                        'Extension' => 'PNG',
                        'CreationTime' => '2024-05-12T14:08:23',
                        'LastWriteTime' => '2025-01-31T23:12:01'
                      );
    
    // Save the Document
    $document_result = $connector->saveDocument($document);
    
    // Specifications of our relation
    $relation = array(
                    'ItemGUID1'     => $task_result->Guid,
                    'ItemGUID2'     => $document_result->Guid,
                    'FolderName1'   => 'Tasks',
                    'FolderName2'   => 'Documents',
                    'RelationType'  => 'GENERAL'
                    );

    // Save the relation
    $output = $connector->saveRelation($relation);
	
	// Download the picture back
	$connector->getBinaryAttachment($GUID, 'Picture2.PNG');
?>