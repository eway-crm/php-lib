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
    private $dieOnItemConflict;

    /**
     * Initialize eWayConnector class
     *
     * @param $webServiceAddress Address of web service
     * @param $username User name
     * @param $password Plain password
     * @param $passwordAlreadyEncrypted - if true, user already encrypted password
     * @param $dieOnItemConflict If true, throws rcItemConflict when item has been changed before saving, if false, merges data 
     * @throws Exception If web service address is empty
     * @throws Exception If username is empty
     * @throws Exception If password is empty
     */
    function __construct($webServiceAddress, $username, $password, $passwordAlreadyEncrypted = false, $dieOnItemConflict = false)
    {
        if (empty($webServiceAddress))
            throw new Exception('Empty web service address');

        if (empty($username))
            throw new Exception('Empty username');

        if (empty($password))
            throw new Exception('Empty password');

        $this->webServiceAddress = $webServiceAddress;
        $this->username = $username;
        $this->dieOnItemConflict = $dieOnItemConflict;

        if ($passwordAlreadyEncrypted)
            $this->passwordHash = $password;
        else
            $this->passwordHash = md5($password);
    }

    /**
     * Saves cart
     *
     * @param $cart Cart array data to save
     * @throws Exception If vart is empty
     * @return Json format with successful response
     */
    public function saveCart($cart)
    {
        if (empty($cart))
            throw new Exception('Empty cart');

        return $this->postRequest('SaveCart', $cart);
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
     * Saves contact
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
     * Gets all documents
     *
     * @return Json format with all documents
     */
    public function getDocuments()
    {
        return $this->postRequest('getDocuments');
    }

    /**
     * Searches documents
     *
     * @param $document Array with specified properties for search
     * @throws Exception If document is empty
     * @return Json format with found documents
     */
    public function searchDocuments($document)
    {
        if (empty($document))
            throw new Exception('Empty document');

        // Any search request is defined as POST
        return $this->postRequest('SearchDocuments', $document);
    }

    /**
     * Saves document
     *
     * @param $document Document array data to save
     * @throws Exception If document is empty
     * @return Json format with successful response
     */
    public function saveDocument($document)
    {
        if (empty($document))
            throw new Exception('Empty document');

        return $this->postRequest('SaveDocument', $document);
    }

    /**
     * Gets all goods
     *
     * @return Json format with all goods
     */
    public function getGoods()
    {
        return $this->postRequest('GetGoods');
    }

    /**
     * Searches goods
     *
     * @param $good Array with specified properties for search
     * @throws Exception If good is empty
     * @return Json format with found goods
     */
    public function searchGoods($good)
    {
        if (empty($good))
            throw new Exception('Empty good');

        // Any search request is defined as POST
        return $this->postRequest('SearchGoods', $good);
    }

    /**
     * Saves good
     *
     * @param $good Good array data to save
     * @throws Exception If good is empty
     * @return Json format with successful response
     */
    public function saveGood($good)
    {
        if (empty($good))
            throw new Exception('Empty good');

        return $this->postRequest('SaveGood', $good);
    }

    /**
     * Gets all goods in cart
     *
     * @return Json format with all goods in cart
     */
    public function getGoodsInCart()
    {
        return $this->postRequest('getGoodsInCart');
    }

    /**
     * Searches goods in cart
     *
     * @param $document Array with specified properties for search
     * @throws Exception If good in cart is empty
     * @return Json format with found good in cart
     */
    public function searchGoodsInCart($goodInCart)
    {
        if (empty($goodInCart))
            throw new Exception('Empty good in cart');

        // Any search request is defined as POST
        return $this->postRequest('SearchGoodsInCart', $goodInCart);
    }

    /**
     * Saves good in cart
     *
     * @param $goodInCart GoodInCart array data to save
     * @throws Exception If good in cart is empty
     * @return Json format with successful response
     */
    public function saveGoodInCart($goodInCart)
    {
        if (empty($goodInCart))
            throw new Exception('Empty goodInCart');

        return $this->postRequest('SaveGoodInCart', $goodInCart);
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
     * Gets all leads
     *
     * @return Json format with all leads
     */
    public function getLeads()
    {
        return $this->postRequest('GetLeads');
    }

    /**
     * Searches leads
     *
     * @param $lead Array with specified properties for search
     * @throws Exception If leads is empty
     * @return Json format with found leads
     */
    public function searchLeads($lead)
    {
        if (empty($lead))
            throw new Exception('Empty lead');

        // Any search request is defined as POST
        return $this->postRequest('SearchLeads', $lead);
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
            throw new Exception('Empty lead');

        return $this->postRequest('SaveLead', $lead);
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
     * Gets all work reports
     *
     * @return Json format with all work reports
     */
    public function getWorkReports()
    {
        return $this->postRequest('GetWorkReports');
    }

    /**
     * Searches work reports
     *
     * @param $projects Array with specified properties for search
     * @throws Exception If work report is empty
     * @return Json format with found work reports
     */
    public function searchWorkReports($workReport)
    {
        if (empty($workReport))
            throw new Exception('Empty workReport');

        // Any search request is defined as POST
        return $this->postRequest('SearchWorkReports', $project);
    }

    /**
     * Saves work report
     *
     * @param $workReport work report array data to save
     * @throws Exception If work report is empty
     * @return Json format with successful response
     */
    public function saveWorkReport($workReport)
    {
        if (empty($workReport))
            throw new Exception('Empty workReport');

        return $this->postRequest('SaveWorkReport', $WorkReport);
    }

    /**
     * Gets all work tasks
     *
     * @return Json format with all tasks
     */
    public function getTasks()
    {
        return $this->postRequest('GetTasks');
    }

    /**
     * Searches tasks
     *
     * @param $projects Array with specified properties for search
     * @throws Exception If task is empty
     * @return Json format with found tasks
     */
    public function searchTasks($task)
    {
        if (empty($task))
            throw new Exception('Empty task');

        // Any search request is defined as POST
        return $this->postRequest('SearchTasks', $project);
    }

    /**
     * Saves task
     *
     * @param $task Task array data to save
     * @throws Exception If task is empty
     * @return Json format with successful response
     */
    public function saveTask($task)
    {
        if (empty($task))
            throw new Exception('Empty task');

        return $this->postRequest('SaveTask', $task);
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

        if ($returnCode != 'rcSuccess') {
            throw new Exception('Login failed: '.$jsonResult->Description);
        }

        // Save this sessionId for next time
        $this->sessionId = $jsonResult->SessionId;
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
            $this->reLogin();
        }
  
        if ($transmitObject == null) {
            $completeTransmitObject = array(
                'sessionId' => $this->sessionId
            );
        } else {
            $completeTransmitObject = array(
                'sessionId' => $this->sessionId,
                'transmitObject' => $transmitObject,
                'dieOnItemConflict' => $this->dieOnItemConflict
            );
        }

        return $this->doRequest($completeTransmitObject, $action);
    }
    private function doRequest($completeTransmitObject, $action)
    {
        // This is first request, login before
        if (empty($this->sessionId)) {
            $this->reLogin();
            
            $completeTransmitObject['sessionId'] = $this->sessionId;
            return $this->doRequest($completeTransmitObject, $action);
        }
        
        $url = $this->createWebServiceUrl($action);
        $jsonObject = json_encode($completeTransmitObject, true);
        $ch = $this->createPostRequest($url, $jsonObject);
        
        $result = curl_exec($ch);
        $jsonResult = json_decode($result);
        $returnCode = $jsonResult->ReturnCode;
        // Session timed out, re-log again
        if ($returnCode == 'rcBadSession') {
            $this->reLogin();
            $completeTransmitObject['sessionId'] = $this->sessionId;
        }
        if ($returnCode != 'rcBadSession' && $returnCode != 'rcDatabaseTimeout') {
            return $jsonResult;
        }
        
        // For rcBadSession and rcDatabaseTimeout types of return code we'll try to perform action once again
        return $this->doRequest($completeTransmitObject, $action);
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
}
?>
