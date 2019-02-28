<?php

    //Load API
    require_once "eway.class.php";
    
    //This willl be our Project
    $connector = new eWayConnector('https://trial.eway-crm.com/31994', 'api', 'ApiTrial@eWay-CRM');
    
    //This is new project, that we want to create
    $project = array(
                     'FileAs' => 'Workflow example',
                     'StateEn' => '0D6D6D0B-2849-11E2-8ABD-00155D002216',
                     'TypeEn' => '0FB1978A-35C9-4A44-9C1A-6923A72A188A'
                     );
    
    //Save the Cart
    $projectGuid = $connector->saveProject($project)->Guid;
    
    //Load version of project for state changing
    $projectVersion = $connector->SearchProjects(array('ItemGUID' => $projectGuid))->Data[0]->ItemVersion + 1;
    
    //Changed fields of the projects
    $project_edit = array(
                          'ItemGUID' => $projectGuid,
                          'StateEn' => '0D6D6D11-2849-11E2-8ABD-00155D002216',
                          'TypeEn' => '0FB1978A-35C9-4A44-9C1A-6923A72A188A',
                          'ItemVersion' => $projectVersion
                          );
    
    //Edit the state of the project
    $connector->saveProject($project_edit);

?>