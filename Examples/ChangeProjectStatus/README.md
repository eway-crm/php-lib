# Change status on Project
This example will show you how to change status on Project

## Create Company and store GUID
We will create "testing" company as subject of this example by  ```$connector->saveProject()``` with array of specifications as parameter. In a real life scenario, the Company would probably already exist and we would search for it with ```$connector->SearchProjects()```. Function that is saving the company will also return us an object with GUID of the Company.
```php

//This is new project, that we want to create
$project = array(
                 'FileAs' => 'Workflow example',
                 'StateEn' => '0D6D6D0B-2849-11E2-8ABD-00155D002216',
                 'TypeEn' => '0FB1978A-35C9-4A44-9C1A-6923A72A188A'
                 );

//Save the Cart
$projectGuid = $connector->saveProject($project)->Guid;

```
### Output
As an output we should see our new company with state "new" in outlook.
![example output](Images/sample_output_company.PNG)

## Changing the status
Our testing company was created with "new" state, similar to if it was created in outlook. Important thing about states is that the state may only be change back and forth accordingly to predefined workflows. In our case from "new" to "completed" and backwards. We will need item version of the project, which we will get by ```$connector->SearchProjects()``` with project GUID as parameter and extract item version from the return object. Now we can call ```$connector->saveProject()``` with array of specifications to edit the Project. For purpose of editing, there have to be GUID of the changed Project. Alongside we supply type of the project (GUID of the type), new state of the project, "completed" in our case (GUID of the state) and item version.
```php

//Load version of project for state changing
$projectVersion = $connector->SearchProjects(array('ItemGUID' => $projectGuid))->Data[0]->ItemVersion;

//Changed fields of the projects
$project_edit = array(
                      'ItemGUID' => $projectGuid,
                      'StateEn' => '0D6D6D11-2849-11E2-8ABD-00155D002216',
                      'TypeEn' => '0FB1978A-35C9-4A44-9C1A-6923A72A188A',
                      'ItemVersion' => $projectVersion
                      );

//Edit the state of the project
$connector->saveProject($project_edit);

```
### Output
As an output we should see our company now having "completed" state in outlook.
![example output](Images/sample_output_state.PNG)

## Sample code
To see the whole sample code click [here](sample_code.php)