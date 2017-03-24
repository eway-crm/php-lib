<?php

/**
 * eWayConnector class helps connect and manage operation for web service.
 *
 * @copyright 2014-2015 eWay System s.r.o.
 * @version 2.0
 */
class eWayConnector
{
    private $appVersion = 'PHP2.0';
    private $sessionId;
    private $webServiceAddress;
    private $username;
    private $passwordHash;

    /**
     * Initialize eWayConnector class
     *
     * @param $webServiceAddress Address of web service
     * @param $username User name
     * @param $password Plain password
     * @param $passwordAlreadyEncrypted - if true, user already encrypted password
     * @throws Exception If web service address is empty
     * @throws Exception If username is empty
     * @throws Exception If password is empty
     */
    function __construct($webServiceAddress, $username, $password, $passwordAlreadyEncrypted = false)
    {
        if (empty($webServiceAddress))
            throw new Exception('Empty web service address');

        if (empty($username))
            throw new Exception('Empty username');

        if (empty($password))
            throw new Exception('Empty password');

        $this->webServiceAddress = $webServiceAddress;
        $this->username = $username;

        if ($passwordAlreadyEncrypted)
            $this->passwordHash = $password;
        else
            $this->passwordHash = md5($password);
    }

    /**
     * Gets all contacts
     *
     * @return Json format with all contacts
     */
    public function getContacts()
    {
        return $this->postRequest('GetContacts');
    }

    /**
     * Searches contacts
     *
     * @param $contact Array with specified properties for search
     * @throws Exception If contact is empty
     * @return Json format with found contacts
     */
    public function searchContacts($contact)
    {
        if (empty($contact))
            throw new Exception('Empty contact');

        // Any search request is defined as POST
        return $this->postRequest('SearchContacts', $contact);
    }

    /**
     * Saves contacts
     *
     * @param $contact Contact array data to save
     * @throws Exception If contact is empty
     * @return Json format with successful response
     */
    public function saveContact($contact)
    {
        if (empty($contact))
            throw new Exception('Empty contact');

        return $this->postRequest('SaveContact', $contact);
    }

    /**
     * Gets all companies
     *
     * @return Json format with all companies
     */
    public function getCompanies()
    {
        return $this->postRequest('GetCompanies');
    }

    /**
     * Searches companies
     *
     * @param $company Array with specified properties for search
     * @throws Exception If company is empty
     * @return Json format with found companies
     */
    public function searchCompanies($company)
    {
        if (empty($company))
            throw new Exception('Empty company');

        // Any search request is defined as POST
        return $this->postRequest('SearchCompanies', $company);
    }

    /**
     * Saves company
     *
     * @param $company Company array data to save
     * @throws Exception If company is empty
     * @return Json format with successful response
     */
    public function saveCompany($company)
    {
        if (empty($company))
            throw new Exception('Empty company');

        return $this->postRequest('SaveCompany', $company);
    }

    /**
     * Gets all projects
     *
     * @return Json format with all projects
     */
    public function getProjects()
    {
        return $this->postRequest('GetProjects');
    }

    /**
     * Searches projects
     *
     * @param $projects Array with specified properties for search
     * @throws Exception If project is empty
     * @return Json format with found projects
     */
    public function searchProjects($project)
    {
        if (empty($project))
            throw new Exception('Empty project');

        // Any search request is defined as POST
        return $this->postRequest('SearchProjects', $project);
    }

    /**
     * Saves project
     *
     * @param $project project array data to save
     * @throws Exception If project is empty
     * @return Json format with successful response
     */
    public function saveProject($project)
    {
        if (empty($project))
            throw new Exception('Empty Project');

        return $this->postRequest('SaveProject', $project);
    }

    /**
     * Saves lead
     *
     * @param $lead Lead array data to save
     * @throws Exception If lead is empty
     * @return Json format with successful response
     */
    public function saveLead($lead)
    {
        if (empty($lead))
            throw new Exception('Empty company');

        return $this->postRequest('SaveLead', $lead);
    }

    /**
     * Searches leads
     *
     * @param $lead Array with specified properties for search
     * @throws Exception If leads is empty
     * @return Json format with found companies
     */
    public function searchLeads($lead)
    {
        if (empty($lead))
            throw new Exception('Empty lead');

        // Any search request is defined as POST
        return $this->postRequest('SearchLeads', $lead);
    }

    /**
     * Gets all leads
     *
     * @return Json format with all leads
     */
    public function getLeads()
    {
        return $this->postRequest('GetLeads');
    }

    /**
     * Saves relation
     *
     * @param $relation Relation array data to save
     * @throws Exception If relation is empty
     * @return Json format with successful response
     */
    public function saveRelation($relation)
    {
        if (empty($relation))
            throw new Exception('Empty relation');

        return $this->postRequest('SaveRelation', $relation);
    }

    /**
     * Saves relation between contact and group
     *
     * @param $contactGUID Contact GUID identification
     * @param $groupGUID Group GUID identification
     * @throws Exception If contactGUID is empty
     * @throws Exception If groupGUID is empty
     * @return Json format with successful response
     */
    public function saveContactGroupRelation($contactGUID, $groupGUID)
    {
        if (empty($contactGUID))
            throw new Exception('Empty contactGUID');

        if (empty($groupGUID))
            throw new Exception('Empty groupGUID');

        $relation = array(
            'ItemGUID1' => $contactGUID,
            'ItemGUID2' => $groupGUID,
            'FolderName1' => 'Contacts',
            'FolderName2' => 'Groups',
            'RelationType' => 'GROUP',
            'DifferDirection' => 0);

        $this->saveRelation($relation);
    }

    /**
     * Saves relation between company and group
     *
     * @param $companyGUID Company GUID identification
     * @param $groupGUID Group GUID identification
     * @throws Exception If companyGUID is empty
     * @throws Exception If groupGUID is empty
     * @return Json format with successful response
     */
    public function saveCompanyGroupRelation($companyGUID, $groupGUID)
    {
        if (empty($companyGUID))
            throw new Exception('Empty companyGUID');

        if (empty($groupGUID))
            throw new Exception('Empty groupGUID');

        $relation = array(
            'ItemGUID1' => $companyGUID,
            'ItemGUID2' => $groupGUID,
            'FolderName1' => 'Companies',
            'FolderName2' => 'Groups',
            'RelationType' => 'GROUP',
            'DifferDirection' => 0);

        $this->saveRelation($relation);
    }

    /**
     * Saves relation between contact and company
     *
     * @param $contactGUID Contact GUID identification
     * @param $companyGUID Company GUID identification
     * @throws Exception If contactGUID is empty
     * @throws Exception If companyGUID is empty
     * @return Json format with successful response
     */
    public function saveContactCompanyRelation($contactGUID, $companyGUID)
    {
        if (empty($contactGUID))
            throw new Exception('Empty contactGUID');

        if (empty($companyGUID))
            throw new Exception('Empty companyGUID');

        $relation = array(
            'ItemGUID1' => $contactGUID,
            'ItemGUID2' => $companyGUID,
            'FolderName1' => 'Contacts',
            'FolderName2' => 'Companies',
            'RelationType' => 'GENERAL',
            'DifferDirection' => 0
        );

        $this->saveRelation($relation);
    }

    /**
     * Saves relation between project and contact
     *
     * @param $projectGUID Project GUID identification
     * @param $contactGUID Contact GUID identification
     * @throws Exception If projectGUID is empty
     * @throws Exception If contactGUID is empty
     * @return Json format with successful response
     */
    public function saveProjectContactPersonRelation($projectGUID, $contactGUID)
    {
        if (empty($projectGUID))
            throw new Exception('Empty projectGUID');

        if (empty($contactGUID))
            throw new Exception('Empty contactGUID');

        $relation = array(
            'ItemGUID1' => $projectGUID,
            'ItemGUID2' => $contactGUID,
            'FolderName1' => 'Projects',
            'FolderName2' => 'Contacts',
            'RelationType' => 'CONTACTPERSON',
            'DifferDirection' => 1);

        $this->saveRelation($relation);
    }

    /**
     * Saves relation between project and company
     *
     * @param $projectGUID Project GUID identification
     * @param $companyGUID Company GUID identification
     * @throws Exception If projectGUID is empty
     * @throws Exception If companyGUID is empty
     * @return Json format with successful response
     */
    public function saveProjectCustomerRelation($projectGUID, $companyGUID)
    {
        if (empty($projectGUID))
            throw new Exception('Empty projectGUID');

        if (empty($companyGUID))
            throw new Exception('Empty contactGUID');

        $relation = array(
            'ItemGUID1' => $projectGUID,
            'ItemGUID2' => $companyGUID,
            'FolderName1' => 'Projects',
            'FolderName2' => 'Companies',
            'RelationType' => 'CUSTOMER',
            'DifferDirection' => 1);

        $this->saveRelation($relation);
    }

    /**
     * Gets all groups
     *
     * @return Json format with all groups
     */
    public function getGroups()
    {
        return $this->postRequest('GetGroups');
    }

    /**
     * Searches groups
     *
     * @param $group Array with specified properties for search
     * @throws Exception If group is empty
     * @return Json format with found groups
     */
    public function searchGroups($group)
    {
        if (empty($group))
            throw new Exception('Empty group');

        // Any search request is defined as POST
        return $this->postRequest('SearchGroups', $group);
    }

    /**
     * Saves group
     *
     * @param $group Group array data to save
     * @throws Exception If group is empty
     * @return Json format with successful response
     */
    public function saveGroup($group)
    {
        if (empty($group))
            throw new Exception('Empty group');

        return $this->postRequest('SaveGroup', $group);
    }

    /**
     * Gets all journals
     *
     * @return Json format with all journals
     */
    public function getJournals()
    {
        return $this->postRequest('GetJournals');
    }

    /**
     * Searches journals
     *
     * @param $journal Array with specified properties for search
     * @throws Exception If journal is empty
     * @return Json format with found journals
     */
    public function searchJournals($journal)
    {
        if (empty($journal))
            throw new Exception('Empty journal');

        // Any search request is defined as POST
        return $this->postRequest('SearchJournals', $journal);
    }

    /**
     * Saves journal
     *
     * @param $journal Journal array data to save
     * @throws Exception If journal is empty
     * @return Json format with successful response
     */
    public function saveJournal($journal)
    {
        if (empty($journal))
            throw new Exception('Empty journal');

        return $this->postRequest('SaveJournal', $journal);
    }

    /**
     * Formats date and time for the API calls
     *
     * @param $date Date to be formatted
     * @throws Exception If date is empty
     * @return Formatted date and time as string
     */
    public function formatDate($date)
    {
        if (empty($date))
            throw new Exception('Empty date');

        return date('Y-m-d H:i:s', $date);
    }

    private function reLogin()
    {
        $login = array(
            'userName' => $this->username,
            'passwordHash' => $this->passwordHash,
            'appVersion' => $this->appVersion
        );
        $jsonObject = json_encode($login, true);
        $ch = $this->createPostRequest($this->createWebServiceUrl('Login'), $jsonObject);
        $jsonResult = json_decode(curl_exec($ch));
        $returnCode = $jsonResult->ReturnCode;

        // Login failed, return empty session id
        if ($returnCode != 'rcSuccess') {
            return '';
        }
        return $jsonResult->SessionId;
    }

    private function createWebServiceUrl($action)
    {
        return $this->joinPaths($this->webServiceAddress, $action);
    }

    private function joinPaths()
    {
        $args = func_get_args();
        $paths = array();

        foreach ($args as $arg) {
            $paths = array_merge($paths, (array)$arg);
        }

        $paths = array_map(create_function('$p', 'return trim($p, "/");'), $paths);
        $paths = array_filter($paths);
        return join('/', $paths);
    }

    private function postRequest($action, $transmitObject = null)
    {
        if (empty($this->sessionId)) {
            $this->reLoginAndSaveSessionId();
        }

        $url = $this->createWebServiceUrl($action);
        if ($transmitObject == null) {
            $completeTransmitObject = array(
                'sessionId' => $this->sessionId
            );
        } else {
            $completeTransmitObject = array(
                'sessionId' => $this->sessionId,
                'transmitObject' => $transmitObject
            );
        }

        $jsonObject = json_encode($completeTransmitObject, true);
        $ch = $this->createPostRequest($url, $jsonObject);

        return $this->doRequest($ch, $action);
    }

    private function doRequest($ch, $action)
    {
        // This is first request, login before
        if (empty($this->sessionId)) {
            $this->reLoginAndSaveSessionId();

            // Create URL again with new sessionId
            curl_setopt($ch, CURLOPT_URL, $this->createSessionUrlAction($action));
        }
        
        $result = curl_exec($ch);
        $jsonResult = json_decode($result);
        $returnCode = $jsonResult->ReturnCode;
        
        // Session timed out, re-log again
        if ($returnCode == 'rcBadSession') {
            $this->reLoginAndSaveSessionId();

            // Create URL again with new sessionId
            curl_setopt($ch, CURLOPT_URL, $this->createSessionUrlAction($action));
        }

        // For these types of return code we'll try to perform action once again
        if ($returnCode == 'rcBadSession' || $returnCode == 'rcDatabaseTimeout') {
            // Perform action again
            $result = curl_exec($ch);
            $jsonResult = json_decode($result);
        }

        return $jsonResult;
    }

    private function createPostRequest($url, $jsonObject)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonObject);
        return $ch;
    }

    private function reLoginAndSaveSessionId()
    {
        $sessionId = $this->reLogin();
        if (empty($sessionId) && empty($this->sessionId))
            throw new Exception('Log in failed, please check your username and password');

        // Save this sessionId for next time
        $this->sessionId = $sessionId;
    }
}
?>
