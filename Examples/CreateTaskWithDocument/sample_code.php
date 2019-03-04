<?php

    // Load API
    require_once "eway.class.php";
    
    // Create connector
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    // Get user GUID
    $userGuid = $connector->searchUsers(array('FileAs' => 'Api-Tester, Robot'))->Data[0]->ItemGUID;
    
    // This will be our Task
    $task = array(
                'StartDate' => '2019-02-01 20:00:00Z',
                'DueDate' => '2019-02-02 04:00:00Z',
                'Subject' => 'TASK: Do the task',
                'FileAs' => 'TASK: Do the task',
                'Users_TaskDelegatorGuid' => $userGuid,
                'Users_TaskSolverGuid' => $userGuid
                );
    
    // Save the task
    $taskResult = $connector->saveTask($task);
    // This willl be our Document
    $document = array(
                        'FileAs' => 'Document',
                        'DocName' => 'Document',
                        'DocSize' => 10,
                        'Extension' => 'txt'
                      );
    
    // Save the Document
    $documentResult = $connector->saveDocument($document);
    
    // Specifications of our relation
    $relation = array(
                    'ItemGUID1'     => $taskResult->Guid,
                    'ItemGUID2'     => $documentResult->Guid,
                    'FolderName1'   => 'Tasks',
                    'FolderName2'   => 'Documents',
                    'RelationType'  => 'GENERAL'
                    );

    // Save the relation
    $output = $connector->saveRelation($relation);

?>