<?php

/**
 * eWayConnector class helps connect and manage operation for web service.
 *
 * @copyright 2014-2022 eWay System s.r.o.
 * @version 2.4
 */
class eWayConnector
{
    private $appVersion;
    private $sessionId;
    private $baseWebServiceAddress;
    private $webServiceAddress;
    private $oldWebServiceAddressUsed;
    private $username;
    private $passwordHash;
    private $dieOnItemConflict;
    private $throwExceptionOnFail;
    private $wcfVersion = null;
    private $userGuid;
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $refreshToken;

    /**
     * Initialize eWayConnector class
     *
     * @param $webServiceAddress Address of web service
     * @param $username User name
     * @param $password Plain password
     * @param $passwordAlreadyEncrypted - if true, user already encrypted password
     * @param $dieOnItemConflict If true, throws rcItemConflict when item has been changed before saving, if false, merges data
     * @param $throwExceptionOnFail If true, throws exception when the web service does not return rcSuccess
     * @param $appVersion Application identifier (name and version)
     * @param $clientId Client ID for OAuth
     * @param $clientSecret Client Secret for OAuth
     * @param $refreshToken Refresh Token used to get Access Token for OAuth
     * @throws Exception If web service address is empty
     * @throws Exception If username is empty
     * @throws Exception If password is empty
     */
    function __construct($webServiceAddress, $username, $password, $passwordAlreadyEncrypted = false, $dieOnItemConflict = false, $throwExceptionOnFail = true, $appVersion = 'PHP2.3',
        $clientId = null, $clientSecret = null, $refreshToken = null)
    {
        if (empty($webServiceAddress))
            throw new Exception('Empty web service address');

        if (empty($username))
            throw new Exception('Empty username');

        if (empty($password) && empty($clientId) && empty($clientSecret))
            throw new Exception('Empty password');

        if (substr($webServiceAddress, -4, 4) == '.svc' || substr($webServiceAddress, -5, 5) == '.svc/')
        {
            $this->webServiceAddress = $webServiceAddress;
            $this->oldWebServiceAddressUsed = true;
        }
        else
        {
            $this->webServiceAddress = $this->getApiServiceUrl($webServiceAddress);
            $this->baseWebServiceAddress = trim($webServiceAddress, "/");
        }
     
        $this->username = $username;
        $this->dieOnItemConflict = $dieOnItemConflict;
        $this->throwExceptionOnFail = $throwExceptionOnFail;
        $this->appVersion = $appVersion;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $refreshToken;
        $this->refreshToken = $refreshToken;

        if (!empty($password)) {
            if ($passwordAlreadyEncrypted)
                $this->passwordHash = $password;
            else
                $this->passwordHash = md5($password);
        }
    }
    
    private function getApiServiceUrl($baseUri, $useOldUrl = false)
    {
        $path = ($useOldUrl) ? "WcfService/Service.svc" : ( ( substr($baseUri, 0, 7) == 'http://' ) ? "InsecureAPI.svc" : "API.svc");
        if (substr_compare($baseUri, '/', -1) === 0)
        {
            return $baseUri.$path;
        }
        else
        {
            return $baseUri."/".$path;
        }
    }

    /**
     * Encode data to Base64URL
     * @param string $data
     * @return boolean|string
     */
    public function base64url_encode($data)
    {
        // First of all you should encode $data to Base64 string
        $b64 = base64_encode($data);

        // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
        if ($b64 === false) {
            return false;
        }

        // Convert Base64 to Base64URL by replacing + with - and / with _
        $url = strtr($b64, '+/', '-_');

        // Remove padding character from the end of line and return the Base64URL result
        return rtrim($url, '=');
    }

    /**
     * Generates code verifier for the OAuth flow.
     */
    public function generateCodeVerifier() 
    {
        return rtrim(base64_encode(md5(microtime())), "=");
    }

    /**
     * Gets code challenge from previously generated code verifier.
     */
    public function getCodeChallenge($codeVerifier)
    {
        if (empty($codeVerifier))
            throw new Exception('CodeVerifier is not specified!');
        
        return $this->base64url_encode(pack('H*', hash('sha256', $codeVerifier)));
    }

    /**
     * Gets OAuth authorization URL.
     */
    public function getAuthorizationUrl($redirectUrl, $challenge, $userName = null, $userNameForced = false)
    {
        if (empty($redirectUrl))
            throw new Exception('Redirect URL is not specified!');
        
        if (empty($this->clientId))
            throw new Exception('ClientID is not specified!');

        if (empty($this->clientSecret))
            throw new Exception('ClientSecret is not specified!');
        
        $url = $this->baseWebServiceAddress."/auth/connect/authorize?client_id={$this->clientId}&scope=api offline_access&redirect_uri={$redirectUrl}&code_challenge=${challenge}&code_challenge_method=S256&response_type=code&prompt=login";

        if (!empty($userName))
        {
            $url .= "&login_hint=".$userName;

            if ($userNameForced)
            {
                $url .= "&login_forced=true";
            }
            else
            {
                $url .= "&login_forced=false";
            }
        }

        return $url;
    }

    /**
     * Finish OAuth authorization to get refresh token.
     */
    public function finishAuthorization($redirectUrl, $codeVerifier, $authorizationCode)
    {
        if (empty($this->clientId))
            throw new Exception('ClientID is not specified!');

        if (empty($this->clientSecret))
            throw new Exception('ClientSecret is not specified!');

        $params = array(
            'code_verifier' => $codeVerifier,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $authorizationCode,
            'redirect_uri' => $redirectUrl,
            'grant_type' => 'authorization_code'
        );

        $response = $this->callTokenEndpoint($params);

        if ($response)
        {
            $this->accessToken = $response->access_token;
            $this->refreshToken = $response->refresh_token;
        }

        return $response;
    }

    /**
     * Get new access token from refresh token.
     */
    public function refreshAccessToken()
    {
        if (empty($this->refreshToken))
            throw new Exception('Refresh Token is not specified!');
        
        if (empty($this->clientId))
            throw new Exception('ClientID is not specified!');

        if (empty($this->clientSecret))
            throw new Exception('ClientSecret is not specified!');
        
        $params = array(
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token'
        );

        return $this->callTokenEndpoint($params);
    }

    private function callTokenEndpoint($params)
    {
        $ch = $this->createPostRequest($this->baseWebServiceAddress.'/auth/connect/token', http_build_query($params), 'application/x-www-form-urlencoded');

        return json_decode($this->executeCurl($ch));
    }

    /**
     * Gets all additional fields
     *
     * @return Json format with all additional fields
     */
    public function getAdditionalFields()
    {
        return $this->postRequest('GetAdditionalFields');
    }
    
    /**
     * Gets additional fields by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getAdditionalFieldsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetAdditionalFieldsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets all additional fields identifiers
     *
     * @return Json format with all additional fields identifiers
     */
    public function getAdditionalFieldsIdentifiers()
    {
        return $this->postRequest('GetAdditionalFieldsIdentifiers');
    }

    /**
     * Searches additional fields
     *
     * @param $additionalField Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If additionalField is empty
     * @return Json format with found additional fields
     */
    public function searchAdditionalFields($additionalField, $includeRelations = false)
    {
        if (empty($additionalField))
            throw new InvalidArgumentException('Empty additional field');

        // Any search request is defined as POST
        return $this->postRequest('SearchAdditionalFields', $additionalField, $includeRelations);
    }
    
    /**
     * Deletes cart
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteCart($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteCart', $guid);
    }

    /**
     * Gets all carts
     *
     * @return Json format with all carts
     */
    public function getCarts()
    {
        return $this->postRequest('GetCarts');
    }
    
    /**
     * Gets carts by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @param $omitGoodsInCart array of additional parameters (default: null)
     * @return Json format with items selected by guids
     */
    public function getCartsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false, $omitGoodsInCart = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        if ($omitGoodsInCart == true) {
            $additionalParameters = array('omitGoodsInCart' => true);
        } else {
            $additionalParameters = null;
        }
        
        return $this->getItemsByItemGuids('GetCartsByItemGuids', $guids, $includeForeignKeys, $includeRelations, $additionalParameters);
    }
    
    /**
     * Gets carts identifiers
     *
     * @return Json format with all carts identifiers
     */
    public function getCartsIdentifiers()
    {
        return $this->getItemIdentifiers('GetCartsIdentifiers');
    }

    /**
     * Searches carts
     *
     * @param $cart Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If cart is empty
     * @return Json format with found carts
     */
    public function searchCarts($cart, $includeRelations = false)
    {
        if (empty($cart))
            throw new InvalidArgumentException('Empty cart');

        // Any search request is defined as POST
        return $this->postRequest('SearchCarts', $cart, $includeRelations);
    }

    /**
     * Saves cart
     *
     * @param $cart Cart array data to save
     * @throws Exception If cart is empty
     * @return Json format with successful response
     */
    public function saveCart($cart)
    {
        if (empty($cart))
            throw new InvalidArgumentException('Empty cart');

        return $this->postRequest('SaveCart', $cart);
    }

    /**
     * Gets all calendars
     *
     * @return Json format with all calendars
     */
    public function getCalendars()
    {
        return $this->postRequest('GetCalendars');
    }
    
    /**
     * Gets calendars by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getCalendarsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetCalendarsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets calendars identifiers
     *
     * @return Json format with all calendars identifiers
     */
    public function getCalendarsIdentifiers()
    {
        return $this->getItemIdentifiers('GetCalendarsIdentifiers');
    }

    /**
     * Searches calendars
     *
     * @param $calendar Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If calendar is empty
     * @return Json format with found calendars
     */
    public function searchCalendars($calendar, $includeRelations = false)
    {
        if (empty($calendar))
            throw new InvalidArgumentException('Empty calendar');

        // Any search request is defined as POST
        return $this->postRequest('SearchCalendars', $calendar, $includeRelations);
    }

    /**
     * Saves calendar
     *
     * @param $calendar Calendar array data to save
     * @throws Exception If calendar is empty
     * @return Json format with successful response
     */
    public function saveCalendar($calendar)
    {
        if (empty($calendar))
            throw new InvalidArgumentException('Empty calendar');

        return $this->postRequest('SaveCalendar', $calendar);
    }

    /**
     * Deletes company
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteCompany($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteCompany', $guid);
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
     * Gets companies by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getCompaniesByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetCompaniesByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }

    /**
     * Gets companies identifiers
     *
     * @return Json format with all companies identifiers
     */
    public function getCompaniesIdentifiers()
    {
        return $this->getItemIdentifiers('GetCompaniesIdentifiers');
    }
    
    /**
     * Searches companies
     *
     * @param $company Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If company is empty
     * @return Json format with found companies
     */
    public function searchCompanies($company, $includeRelations = false)
    {
        if (empty($company))
            throw new InvalidArgumentException('Empty company');

        // Any search request is defined as POST
        return $this->postRequest('SearchCompanies', $company, $includeRelations);
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
            throw new InvalidArgumentException('Empty company');

        return $this->postRequest('SaveCompany', $company);
    }

    /**
     * Deletes contact
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteContact($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteContact', $guid);
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
     * Gets contacts by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getContactsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetContactsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets contacts identifiers
     *
     * @return Json format with all contacts identifiers
     */
    public function getContactsIdentifiers()
    {
        return $this->getItemIdentifiers('GetContactsIdentifiers');
    }

    /**
     * Searches contacts
     *
     * @param $contact Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If contact is empty
     * @return Json format with found contacts
     */
    public function searchContacts($contact, $includeRelations = false)
    {
        if (empty($contact))
            throw new InvalidArgumentException('Empty contact');

        // Any search request is defined as POST
        return $this->postRequest('SearchContacts', $contact, $includeRelations);
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
            throw new InvalidArgumentException('Empty contact');

        return $this->postRequest('SaveContact', $contact);
    }

    /**
     * Deletes document
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteDocument($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteDocument', $guid);
    }
    
    /**
     * Gets all documents
     *
     * @return Json format with all documents
     */
    public function getDocuments()
    {
        return $this->postRequest('GetDocuments');
    }
    
    /**
     * Gets Documents by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getDocumentsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetDocumentsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets documents identifiers
     *
     * @return Json format with all documents identifiers
     */
    public function getDocumentsIdentifiers()
    {
        return $this->getItemIdentifiers('GetDocumentsIdentifiers');
    }

    /**
     * Searches documents
     *
     * @param $document Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If document is empty
     * @return Json format with found documents
     */
    public function searchDocuments($document, $includeRelations = false)
    {
        if (empty($document))
            throw new InvalidArgumentException('Empty document');

        // Any search request is defined as POST
        return $this->postRequest('SearchDocuments', $document, $includeRelations);
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
            throw new InvalidArgumentException('Empty document');

        return $this->postRequest('SaveDocument', $document);
    }

    /**
     * Deletes email
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteEmail($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteEmail', $guid);
    }

    /**
     * Gets all emails
     *
     * @return Json format with all emails
     */
    public function getEmails()
    {
        return $this->postRequest('GetEmails');
    }
    
    /**
     * Gets emails by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getEmailsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetEmailsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets emails identifiers
     *
     * @return Json format with all emails identifiers
     */
    public function getEmailsIdentifiers()
    {
        return $this->getItemIdentifiers('GetEmailsIdentifiers');
    }

    /**
     * Searches emails
     *
     * @param $email Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If email is empty
     * @return Json format with found email
     */
    public function searchEmails($email, $includeRelations = false)
    {
        if (empty($email))
            throw new InvalidArgumentException('Empty email');

        // Any search request is defined as POST
        return $this->postRequest('SearchEmails', $email, $includeRelations);
    }

    /**
     * Saves email
     *
     * @param $email Email array data to save
     * @throws Exception If email is empty
     * @return Json format with successful response
     */
    public function saveEmail($email)
    {
        if (empty($email))
            throw new InvalidArgumentException('Empty email');

        return $this->postRequest('SaveEmail', $email);
    }

    /**
     * Gets all enum types
     *
     * @return Json format with all enum types
     */
    public function getEnumTypes()
    {
        return $this->postRequest('GetEnumTypes');
    }
    
    /**
     * Gets enum types by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getEnumTypesByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetEnumTypesByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets enum types identifiers
     *
     * @return Json format with all enum types identifiers
     */
    public function getEnumTypesIdentifiers()
    {
        return $this->getItemIdentifiers('GetEnumTypesIdentifiers');
    }
    
    /**
     * Searches enum types
     *
     * @param $enumType Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If enumType is empty
     * @return Json format with found enum values
     */
    public function searchEnumTypes($enumType, $includeRelations = false)
    {
        if (empty($enumType))
            throw new InvalidArgumentException('Empty enumType');

        // Any search request is defined as POST
        return $this->postRequest('SearchEnumTypes', $enumType, $includeRelations);
    }
    
    /**
     * Gets all enum values
     *
     * @return Json format with all enum values
     */
    public function getEnumValues()
    {
        return $this->postRequest('GetEnumValues');
    }
    
    /**
     * Gets enum values by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getEnumValuesByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetEnumValuesByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets enum values identifiers
     *
     * @return Json format with all enum values identifiers
     */
    public function getEnumValuesIdentifiers()
    {
        return $this->getItemIdentifiers('GetEnumValuesIdentifiers');
    }

    /**
     * Searches enum values
     *
     * @param $enumValue Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If enumValue is empty
     * @return Json format with found enum values
     */
    public function searchEnumValues($enumValue, $includeRelations = false)
    {
        if (empty($enumValue))
            throw new InvalidArgumentException('Empty enumValue');

        // Any search request is defined as POST
        return $this->postRequest('SearchEnumValues', $enumValue, $includeRelations);
    }

    /**
     * Gets all Features
     *
     * @return Json format with all features
     */
    public function getFeatures()
    {
        return $this->postRequest('GetFeatures');
    }
    
    /**
     * Gets features by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getFeaturesByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetFeaturesByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets features identifiers
     *
     * @return Json format with all features identifiers
     */
    public function getFeaturesIdentifiers()
    {
        return $this->getItemIdentifiers('GetFeaturesIdentifiers');
    }

    /**
     * Searches Features
     *
     * @param $features Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If features is empty
     * @return Json format with found features
     */
    public function searchFeatures($features, $includeRelations = false)
    {
        if (empty($features))
            throw new InvalidArgumentException('Empty features');

        // Any search request is defined as POST
        return $this->postRequest('SearchFeatures', $features, $includeRelations);
    }

    /**
     * Gets all Flows
     *
     * @return Json format with all flows
     */
    public function getFlows()
    {
        return $this->postRequest('GetFlows');
    }
    
    /**
     * Gets additional flows by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getFlowsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetFlowsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }

    /**
     * Gets flows identifiers
     *
     * @return Json format with all flows identifiers
     */
    public function getFlowsIdentifiers()
    {
        return $this->getItemIdentifiers('GetFlowsIdentifiers');
    }
    
    /**
     * Searches Flows
     *
     * @param $flow Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If flow is empty
     * @return Json format with found flows
     */
    public function searchFlows($flow, $includeRelations = false)
    {
        if (empty($flow))
            throw new InvalidArgumentException('Empty flow');

        // Any search request is defined as POST
        return $this->postRequest('SearchFlows', $flow, $includeRelations);
    }

    /**
     * Gets all Global settings
     *
     * @return Json format with all global settings
     */
    public function getGlobalSettings()
    {
        return $this->postRequest('GetGlobalSettings');
    }
    
    /**
     * Gets global settings by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getGlobalSettingsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetGlobalSettingsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets global settings identifiers
     *
     * @return Json format with all global settings identifiers
     */
    public function getGlobalSettingsIdentifiers()
    {
        return $this->getItemIdentifiers('GetGlobalSettingsIdentifiers');
    }

    /**
     * Searches Global settings
     *
     * @param $globalSetting Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If globalSetting is empty
     * @return Json format with found global settings
     */
    public function searchGlobalSettings($globalSetting, $includeRelations = false)
    {
        if (empty($globalSetting))
            throw new InvalidArgumentException('Empty global setting');

        // Any search request is defined as POST
        return $this->postRequest('SearchGlobalSettings', $globalSetting, $includeRelations);
    }

    /**
     * Deletes good
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteGood($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteGood', $guid);
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
     * Gets additional goods by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getGoodsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetGoodsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets goods identifiers
     *
     * @return Json format with all goods identifiers
     */
    public function getGoodsIdentifiers()
    {
        return $this->getItemIdentifiers('GetGoodsIdentifiers');
    }

    /**
     * Searches goods
     *
     * @param $good Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If good is empty
     * @return Json format with found goods
     */
    public function searchGoods($good, $includeRelations = false)
    {
        if (empty($good))
            throw new InvalidArgumentException('Empty good');

        // Any search request is defined as POST
        return $this->postRequest('SearchGoods', $good, $includeRelations);
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
            throw new InvalidArgumentException('Empty good');

        return $this->postRequest('SaveGood', $good);
    }

    
    /**
     * Deletes good in cart
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteGoodInCart($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteGoodInCart', $guid);
    }
    
    /**
     * Gets all goods in cart
     *
     * @return Json format with all goods in cart
     */
    public function getGoodsInCart()
    {
        return $this->postRequest('GetGoodsInCart');
    }
    
    /**
     * Gets goods in cart by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getGoodsInCartByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetGoodsInCartByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets goods in cart identifiers
     *
     * @return Json format with all goods in cart identifiers
     */
    public function getGoodsInCartIdentifiers()
    {
        return $this->getItemIdentifiers('GetGoodsInCartIdentifiers');
    }

    /**
     * Searches goods in cart
     *
     * @param $goodInCart Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If goodInCart is empty
     * @return Json format with found good in cart
     */
    public function searchGoodsInCart($goodInCart, $includeRelations = false)
    {
        if (empty($goodInCart))
            throw new InvalidArgumentException('Empty goodInCart');

        // Any search request is defined as POST
        return $this->postRequest('SearchGoodsInCart', $goodInCart, $includeRelations = false);
    }

    /**
     * Saves good in cart
     *
     * @param $goodInCart GoodInCart array data to save
     * @throws Exception If goodInCart is empty
     * @return Json format with successful response
     */
    public function saveGoodInCart($goodInCart)
    {
        if (empty($goodInCart))
            throw new InvalidArgumentException('Empty goodInCart');

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
     * Gets additional groups by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getGroupsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetGroupsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets groups identifiers
     *
     * @return Json format with all groups identifiers
     */
    public function getGroupsIdentifiers()
    {
        return $this->getItemIdentifiers('GetGroupsIdentifiers');
    }

    /**
     * Searches groups
     *
     * @param $group Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If group is empty
     * @return Json format with found groups
     */
    public function searchGroups($group, $includeRelations = false)
    {
        if (empty($group))
            throw new InvalidArgumentException('Empty group');

        // Any search request is defined as POST
        return $this->postRequest('SearchGroups', $group, $includeRelations);
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
            throw new InvalidArgumentException('Empty group');

        return $this->postRequest('SaveGroup', $group);
    }

    /**
     * Deletes journal
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteJournal($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteJournal', $guid);
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
     * Gets journals by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getJournalsItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetJournalsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }

    /**
     * Gets journals identifiers
     *
     * @return Json format with all journals identifiers
     */
    public function getJournalsIdentifiers()
    {
        return $this->getItemIdentifiers('GetJournalsIdentifiers');
    }
    
    /**
     * Searches journals
     *
     * @param $journal Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If journal is empty
     * @return Json format with found journals
     */
    public function searchJournals($journal, $includeRelations = false)
    {
        if (empty($journal))
            throw new InvalidArgumentException('Empty journal');

        // Any search request is defined as POST
        return $this->postRequest('SearchJournals', $journal, $includeRelations);
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
            throw new InvalidArgumentException('Empty journal');

        return $this->postRequest('SaveJournal', $journal);
    }

    /**
     * Deletes lead
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteLead($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteLead', $guid);
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
     * Gets leads by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getLeadsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetLeadsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets leads identifiers
     *
     * @return Json format with all leads identifiers
     */
    public function getLeadsIdentifiers()
    {
        return $this->getItemIdentifiers('GetLeadsIdentifiers');
    }

    /**
     * Searches leads
     *
     * @param $lead Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If lead is empty
     * @return Json format with found leads
     */
    public function searchLeads($lead, $includeRelations = false)
    {
        if (empty($lead))
            throw new InvalidArgumentException('Empty lead');

        // Any search request is defined as POST
        return $this->postRequest('SearchLeads', $lead, $includeRelations);
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
            throw new InvalidArgumentException('Empty lead');

        return $this->postRequest('SaveLead', $lead);
    }
    
    /**
     * Deletes marketing campaign
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteMarketingCampaign($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteMarketingCampaign', $guid);
    }
    
    /**
     * Gets all marketing campaigns
     *
     * @return Json format with all marketing campaigns
     */
    public function getMarketingCampaigns()
    {
        return $this->postRequest('GetMarketingCampaigns');
    }
    
    /**
     * Gets marketing campaigns by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getMerketingCampaignsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetMarketingCampaignsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets marketing campaigns identifiers
     *
     * @return Json format with all marketing campaigns identifiers
     */
    public function getMarketingCampaignsIdentifiers()
    {
        return $this->getItemIdentifiers('GetMarketingCampaignsIdentifiers');
    }

    /**
     * Searches marketing campaigns
     *
     * @param $marketingCampaign Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If marketing campaign is empty
     * @return Json format with found marketing campaigns
     */
    public function searchMarketingCampaigns($marketingCampaign, $includeRelations = false)
    {
        if (empty($marketingCampaign))
            throw new InvalidArgumentException('Empty marketing campaign');

        // Any search request is defined as POST
        return $this->postRequest('SearchMarketingCampaigns', $marketingCampaign, $includeRelations);
    }
    
    /**
     * Saves marketing campaign
     *
     * @param $marketingCampaign marketing campaign array data to save
     * @throws Exception If marketing campaign is empty
     * @return Json format with successful response
     */
    public function saveMarketingCampaign($marketingCampaign)
    {
        if (empty($marketingCampaign))
            throw new InvalidArgumentException('Empty marketing campaign');

        return $this->postRequest('SaveMarketingCampaign', $marketingCampaign);
    }
    
    /**
     * Deletes marketing list record
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteMarketingListRecord($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteMarketingListRecord', $guid);
    }
    
    /**
     * Gets all marketing lists records
     *
     * @return Json format with all marketing lists records
     */
    public function getMarketingListsRecords()
    {
        return $this->postRequest('GetMarketingListsRecords');
    }
    
    /**
     * Gets marketing lists by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getMarketingListsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetMarketingListsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets marketing lists records identifiers
     *
     * @return Json format with all marketing lists records identifiers
     */
    public function getMarketingListsRecordsIdentifiers()
    {
        return $this->getItemIdentifiers('GetMarketingListsRecordsIdentifiers');
    }

    /**
     * Searches marketing lists records
     *
     * @param $marketingListRecord Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If marketing list record is empty
     * @return Json format with found marketing list records
     */
    public function searchMarketingListsRecords($marketingListRecord, $includeRelations = false)
    {
        if (empty($marketingListRecord))
            throw new InvalidArgumentException('Empty marketing list record');

        // Any search request is defined as POST
        return $this->postRequest('SearchMarketingListsRecords', $marketingListRecords, $includeRelations);
    }
    
    /**
     * Saves marketing list record
     *
     * @param $marketingListRecord marketing list record array data to save
     * @throws Exception If marketing list record is empty
     * @return Json format with successful response
     */
    public function saveMarketingListRecord($marketingListRecord)
    {
        if (empty($marketingListRecord))
            throw new InvalidArgumentException('Empty marketing list record');

        return $this->postRequest('SaveMarketingListRecord', $marketingListRecord);
    }
    
    /**
     * Deletes project
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteProject($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteProject', $guid);
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
     * Gets projects by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getProjectsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetProjectsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets projects identifiers
     *
     * @return Json format with all projects identifiers
     */
    public function getProjectsIdentifiers()
    {
        return $this->getItemIdentifiers('GetProjectsIdentifiers');
    }

    /**
     * Searches projects
     *
     * @param $projects Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If project is empty
     * @return Json format with found projects
     */
    public function searchProjects($project, $includeRelations = false)
    {
        if (empty($project))
            throw new InvalidArgumentException('Empty project');

        // Any search request is defined as POST
        return $this->postRequest('SearchProjects', $project, $includeRelations);
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
            throw new InvalidArgumentException('Empty Project');

        return $this->postRequest('SaveProject', $project);
    }

    /**
     * Gets all sale prices
     *
     * @return Json format with all sale prices
     */
    public function getSalePrices()
    {
        return $this->postRequest('GetSalePrices');
    }
    
    /**
     * Gets sale prices by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getSalePricesByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetSalePricesByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets sale prices identifiers
     *
     * @return Json format with all sale prices identifiers
     */
    public function getSalePricesIdentifiers()
    {
        return $this->getItemIdentifiers('GetSalePricesIdentifiers');
    }

    /**
     * Searches sale prices
     *
     * @param $salePrice Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If salePrice is empty
     * @return Json format with found sale prices
     */
    public function searchSalePrices($salePrice, $includeRelations = false)
    {
        if (empty($salePrice))
            throw new InvalidArgumentException('Empty salePrice');

        // Any search request is defined as POST
        return $this->postRequest('SearchSalePrices', $salePrice, $includeRelations);
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
            throw new InvalidArgumentException('Empty relation');

        return $this->postRequest('SaveRelation', $relation);
    }

    /**
     * Gets all tasks
     *
     * @return Json format with all tasks
     */
    public function getTasks()
    {
        return $this->postRequest('GetTasks');
    }
    
    /**
     * Gets tasks by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getTasksByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetTasksByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets tasks identifiers
     *
     * @return Json format with all tasks identifiers
     */
    public function getTasksIdentifiers()
    {
        return $this->getItemIdentifiers('GetTasksIdentifiers');
    }

    /**
     * Searches tasks
     *
     * @param $task Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If task is empty
     * @return Json format with found tasks
     */
    public function searchTasks($task, $includeRelations = false)
    {
        if (empty($task))
            throw new InvalidArgumentException('Empty task');

        // Any search request is defined as POST
        return $this->postRequest('SearchTasks', $task, $includeRelations);
    }

    /**
     * Saves task
     *
     * @param $task Task array data to save
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If task is empty
     * @return Json format with successful response
     */
    public function saveTask($task, $includeRelations = false)
    {
        if (empty($task))
            throw new InvalidArgumentException('Empty task');

        return $this->postRequest('SaveTask', $task, $includeRelations);
    }

    /**
     * Gets all users
     *
     * @return Json format with all users
     */
    public function getUsers()
    {
        return $this->postRequest('GetUsers');
    }
    
    /**
     * Gets users by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getUsersByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetUsersByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets users identifiers
     *
     * @return Json format with all users identifiers
     */
    public function getUsersIdentifiers()
    {
        return $this->getItemIdentifiers('GetUsersIdentifiers');
    }

    /**
     * Searches users
     *
     * @param $user Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If user is empty
     * @return Json format with found users
     */
    public function searchUsers($user, $includeRelations = false)
    {
        if (empty($user))
            throw new InvalidArgumentException('Empty user');

        // Any search request is defined as POST
        return $this->postRequest('SearchUsers', $user, $includeRelations);
    }

    /**
     * Gets all work flow models
     *
     * @return Json format with all work flows
     */
    public function getWorkFlowModels()
    {
        return $this->postRequest('GetWorkFlowModels');
    }
    
    /**
     * Gets workflow models by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getWorkflowModelsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetWorkflowModelsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets workflow models identifiers
     *
     * @return Json format with all sale workflow models identifiers
     */
    public function getWorkflowModelsIdentifiers()
    {
        return $this->getItemIdentifiers('GetWorkflowModelsIdentifiers');
    }

    /**
     * Searches work flow models
     *
     * @param $workFlowModel Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If workFlowModel is empty
     * @return Json format with found work flow models
     */
    public function searchWorkFlowModels($workFlowModel, $includeRelations = false)
    {
        if (empty($workFlowModel))
            throw new InvalidArgumentException('Empty workFlowModel');

        // Any search request is defined as POST
        return $this->postRequest('SearchWorkFlowModels', $workFlowModel, $includeRelations);
    }

    /**
     * Deletes work report
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteWorkReport($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteWorkReport', $guid);
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
     * Gets work reports by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getWorkReportsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetWorkReportsByItemGuids', $guids, $includeForeignKeys, $includeRelations);
    }
    
    /**
     * Gets work reports identifiers
     *
     * @return Json format with all sale work reports identifiers
     */
    public function getWorkReportsIdentifiers()
    {
        return $this->getItemIdentifiers('GetWorkReportsIdentifiers');
    }

    /**
     * Searches work reports
     *
     * @param $workReport Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @throws Exception If workReport is empty
     * @return Json format with found work reports
     */
    public function searchWorkReports($workReport, $includeRelations = false)
    {
        if (empty($workReport))
            throw new InvalidArgumentException('Empty workReport');

        // Any search request is defined as POST
        return $this->postRequest('SearchWorkReports', $workReport, $includeRelations);
    }

    /**
     * Saves work report
     *
     * @param $workReport work report array data to save
     * @throws Exception If workReport is empty
     * @return Json format with successful response
     */
    public function saveWorkReport($workReport)
    {
        if (empty($workReport))
            throw new InvalidArgumentException('Empty workReport');

        return $this->postRequest('SaveWorkReport', $workReport);
    }
    
    /**
     * Deletes user settings
     *
     * @param $guid guid of item to be deleted
     * @return Json format with deletion status
     */
    public function deleteUserSettings($guid)
    {
        if (empty($guid))
            throw new InvalidArgumentException('Parameter $guid not specified');
        
        return $this->deleteItem('DeleteUserSetting', $guid, '5.3.1.68');
    }
    
    /**
     * Gets all user settings
     *
     * @return Json format with all user settings
     */
    public function getUserSettings()
    {
        return $this->postRequest('GetUserSettings', null, '5.3.1.68');
    }
    
    /**
     * Gets user settings by item guids
     *
     * @param $guids guids of items to get
     * @param $includeFoeignKeys indicator whether you want to include foreign keys (default: true)
     * @param $includeRelations indicator whether you want to include relations (default: false)
     * @return Json format with items selected by guids
     */
    public function getUserSettingsByItemGuids($guids, $includeForeignKeys = true, $includeRelations = false)
    {
        if (empty($guids))
            throw new InvalidArgumentException('Parameter $guids not specified');
        
        return $this->getItemsByItemGuids('GetUserSettingsByItemGuids', $guids, $includeForeignKeys, $includeRelations, '5.3.1.68');
    }
    
    /**
     * Gets user settings identifiers
     *
     * @return Json format with all sale user settings identifiers
     */
    public function getUserSettingsIdentifiers()
    {
        return $this->getItemIdentifiers('GetUserSettingsIdentifiers', '5.3.1.68');
    }
    
    /**
     * Searches user settings
     *
     * @param $workReport Array with specified properties for search
     * @param $includeRelations indicator whether you want to include relations (default: false) 
     * @throws Exception If userSettings is empty
     * @return Json format with found user settings
     */
    public function searchUserSettings($userSettings, $includeRelations = false)
    {
        if (empty($userSettings))
            throw new InvalidArgumentException('Empty userSettings');

        // Any search request is defined as POST
        return $this->postRequest('SearchUserSettings', $userSettings, $includeRelations = false, '5.3.1.68');
    }
    
    /**
     * Saves user settings
     *
     * @param $workReport work report array data to save
     * @throws Exception If userSettings is empty
     * @return Json format with successful response
     */
    public function saveUserSettings($userSettings)
    {
        if (empty($userSettings))
            throw new InvalidArgumentException('Empty userSettings');

        return $this->postRequest('SaveUserSetting', $userSettings, '5.3.1.68');
    }
    
    /**
     * Gets the last item change id (the latest, the highest)
     *
     * @return The last item change id
     */
    public function getLastItemChangeId()
    {
        return $this->doRequest(array('sessionId' => $this->sessionId), 'GetLastItemChangeId');
    }
    
    /**
     * Gets the item change identifiers for the given module and changes interval
     *
     * @param $folderName The module name - object type identifier
     * @param $baseChangeId The base change id
     * @param $targetChangeId The target change id
     * @return The item change identifiers for the given module and changes interval
     */
    public function getItemChangeIdentifiers($folderName, $baseChangeId, $targetChangeId)
    {
        $completeTransmitObject = array(
                'sessionId' => $this->sessionId,
                'folderName' => $folderName,
                'baseChangeId' => $baseChangeId,
                'targetChangeId' => $targetChangeId
            );
        
        return $this->doRequest($completeTransmitObject, 'GetItemChangeIdentifiers');
    }
    
    /**
     * Gets the changed items form the given folders. This method is a combination of calling GetItemChangeIdentifiers and Get[FolderName]ByItemGuids
     *
     * @param $folderNames The folder names
     * @param $baseChangeId The base change id
     * @param $targetChangeId The target change id
     * @param $includeForeignKeys If set to True, the JSON result will contain foreign keys/items fields made from the 1:N relations as well
     * @return The item changes for the given module and changes interval
     */
    public function getChangedItems($folderNames, $baseChangeId, $targetChangeId, $includeForeignKeys = false)
    {
        $completeTransmitObject = array(
                'sessionId' => $this->sessionId,
                'folderNames' => $folderNames,
                'baseChangeId' => $baseChangeId,
                'targetChangeId' => $targetChangeId,
                'includeForeignKeys' => $includeForeignKeys
            );

        return $this->doRequest($completeTransmitObject, 'GetChangedItems', '5.3.0.1');
    }
    
    /**
     * Gets module permissions of the current user
     *
     * @return Json format with permissions
     */
    public function getMyModulePermissions()
    {
        return $this->postRequest('GetMyModulePermissions');
    }
    
    /**
     * Uploads binary attachement against eWay-CRM API
     *
     * @param $filePath Path to file to be attached
     * @param $itemGuid Guid of the attached item (will generate new if empty)
     * @return Json format with successful response
     */
    public function saveBinaryAttachment($filePath, &$itemGuid = null)
    {
        if (empty($itemGuid))
            $itemGuid = trim(com_create_guid(), '{}');
        
        return $this->upload($itemGuid, $filePath);
    }
	
	/**
	 * Downloads the binary attachment of a document and saves it into a file.
	 * If no revision number is specified, downloads the latest revision.
	 * 
	 * @param $itemGuid ItemGUID of the document.
	 * @param $targetFilePath Path to the target file into which the binary content is saved. The file should not exist.
	 * @param $revision (Optional) The revision number. If no supplied or is zero, the latest revision of the document is downloaded.
	 */
	public function getBinaryAttachment($itemGuid, $targetFilePath, $revision = 0) {
		if (empty($revision) || $revision == 0) {
			$revisionObj = array(
                'sessionId' => $this->sessionId,
				'documtentGuid' => $itemGuid
			);
			$revisionResponse = $this->doRequest($revisionObj, 'GetLatestRevision');
			$revision = $revisionResponse->Datum->Revision;
		}
		
		$this->download($itemGuid, $revision, $targetFilePath);
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

    /**
     * Ends active session.
     */
    public function logOut()
    {
        if (empty($this->sessionId))
            return;
        
        $logout = array(
            'sessionId' => $this->sessionId
        );

        $jsonObject = json_encode($logout, true);
        $ch = $this->createPostRequest($this->createWebServiceUrl('LogOut'), $jsonObject);
        $this->executeCurl($ch);
    }

    public function getUserGuid()
    {
        if ($this->userGuid == NULL)
        {
            $this->reLogin();
        }
        return $this->userGuid;
    }

    private function getClientIp()
    {
        return $_SERVER['HTTP_CLIENT_IP'] 
            ? : ($_SERVER['HTTP_X_FORWARDED_FOR'] 
            ? : $_SERVER['REMOTE_ADDR']);
    }
    
    private function reLogin($repeatOnBadAccessToken = true)
    {
        $login = array(
            'userName' => $this->username,
            'passwordHash' => $this->passwordHash,
            'appVersion' => $this->appVersion,
            'clientMachineIdentifier' => $this->getClientIp()
        );

        $jsonObject = json_encode($login, true);
        $ch = $this->createPostRequest($this->createWebServiceUrl('Login'), $jsonObject);

        $result = $this->executeCurl($ch);
        $jsonResult = json_decode($result);
        $returnCode = $jsonResult->ReturnCode;
        $this->wcfVersion = $jsonResult->WcfVersion;
        $this->userGuid = $jsonResult->UserItemGuid;
        
        if ($returnCode == 'rcBadAccessToken' && $repeatOnBadAccessToken == true && $this->doRefreshAccessToken()) {
            $this->reLogin(false);
            return;
        }

        // Check if web service has returned success.
        if ($returnCode != 'rcSuccess') {
            throw new LoginException($jsonResult);
        }

        // Save this sessionId for next time
        $this->sessionId = $jsonResult->SessionId;
    }

    private function createWebServiceUrl($action)
    {
        return $this->joinPaths($this->webServiceAddress, $action);
    }
    
    private function createFileUploadUrl($itemGuid, $fileName)
    {
        return $this->createWebServiceUrl('SaveBinaryAttachment?sessionId='.$this->sessionId.'&itemGuid='.$itemGuid.'&fileName='.$fileName);
    }

    private function joinPaths()
    {
        $args = func_get_args();
        $paths = array();

        foreach ($args as $arg) {
            $paths = array_merge($paths, (array)$arg);
        }

        $paths = array_map(function($p) {return trim($p, "/"); }, $paths);
        $paths = array_filter($paths);
        return join('/', $paths);
    }

    private function postRequest($action, $transmitObject = null, $includeRelations = false, $version = null)
    {
        if ($transmitObject == null) {
            $completeTransmitObject = array(
                'sessionId' => $this->sessionId,
                'includeRelations' => $includeRelations
            );
        } else {
            $completeTransmitObject = array(
                'sessionId' => $this->sessionId,
                'transmitObject' => $transmitObject,
                'includeRelations' => $includeRelations,
                'dieOnItemConflict' => $this->dieOnItemConflict
            );
        }

        return $this->doRequest($completeTransmitObject, $action, $version);
    }
    
    private function getItemIdentifiers($action, $version = null ) {
        $completeTransmitObject = array(
            'sessionId' => $this->sessionId
        );
            
        return $this->doRequest($completeTransmitObject, $action, $version);
    }
    
    private function getItemsByItemGuids($action, $guids, $includeForeignKeys = true, $includeRelations = false, $additionalParameters = null, $version = null ) {
        if ($guids == null) {
            throw new Exception('Action '.$action.' requires an array of searched item guids to be executed on.');
        }
		
		$completeTransmitObject = array(
			'sessionId' => $this->sessionId,
			'itemGuids' => $guids,
			'includeForeignKeys' => $includeForeignKeys,
			'includeRelations' => $includeRelations
		);
		
		if($additionalParameters != null){
			foreach($additionalParameters as $key => $parameter)
				$completeTransmitObject[$key] = $parameter;
		}
        
        return $this->doRequest($completeTransmitObject, $action, $version);
    }
    
    private function deleteItem($action, $guid, $version = null ) {
        if ($guid == null) {
            throw new Exception('Action '.$action.' requires item to be executed on.');
        } else {
            $completeTransmitObject = array(
                'sessionId' => $this->sessionId,
                'itemGuid' => $guid
            );
        }
        
        return $this->doRequest($completeTransmitObject, $action, $version);
    }

    private function executeCurl($ch)
    {
        $result = curl_exec($ch);
        
        // Check if request has been executed successfully.
        if ($result === false) {            
            throw new Exception('Error occurred while communicating with service: '.curl_error($ch));
        }

        // Also Check if return code is OK.
        $curlReturnInfo = curl_getinfo($ch);
        if ($curlReturnInfo['http_code'] != 200 && $curlReturnInfo['http_code'] != 401)
        {
            if (!$this->oldWebServiceAddressUsed && $curlReturnInfo['http_code'] == 404)
            {
                curl_setopt($ch, CURLOPT_URL, str_replace($this->webServiceAddress, $this->getApiServiceUrl($this->baseWebServiceAddress, true), $curlReturnInfo['url']));
                $this->webServiceAddress = $this->getApiServiceUrl($this->baseWebServiceAddress, true);
                $this->oldWebServiceAddressUsed = true;
                
                return $this->executeCurl($ch);
            }
            
            throw new Exception('Error occurred while communicating with service with http code: '.$curlReturnInfo['http_code']);
        }
		
		curl_close($ch); 

        return $result;
    }

    private function doRequest($completeTransmitObject, $action, $version = null, $repeatSession = true)
    {   
        // This is first request, login before
        if (empty($this->sessionId)) {
            $this->reLogin();
            
            $completeTransmitObject['sessionId'] = $this->sessionId;
            return $this->doRequest($completeTransmitObject, $action, $version);
        }
        
        if ( $version != null ){
            if ( version_compare( $this->wcfVersion, $version ) == -1){
                throw new Exception('This function is available from version '.$version.' ! Your version is '.$this->wcfVersion.' .');
            }
        }
        
        $url = $this->createWebServiceUrl($action);
        $jsonObject = json_encode($completeTransmitObject, true);
        $ch = $this->createPostRequest($url, $jsonObject);
        
        $result = $this->executeCurl($ch);
        $jsonResult = json_decode($result);
        $returnCode = $jsonResult->ReturnCode;
        
        // Session timed out, re-log again
        if ($returnCode == 'rcBadSession' || ($returnCode == 'rcBadAccessToken' && $this->doRefreshAccessToken())) {
            $this->reLogin();
            $completeTransmitObject['sessionId'] = $this->sessionId;
        }
        
        if ($returnCode == 'rcBadSession' || $returnCode == 'rcBadAccessToken' || $returnCode == 'rcDatabaseTimeout') {
            // For rcBadSession and rcDatabaseTimeout types of return code we'll try to perform action once again
            if ($repeatSession == true) {
                return $this->doRequest($completeTransmitObject, $action, $version, false);
            }
        }
        
        if ($this->throwExceptionOnFail && $returnCode != 'rcSuccess') {
            throw new ResponseException($jsonResult);
        }
        
        return $jsonResult;
    }

    private function doRefreshAccessToken()
    {
        $refreshTokenResponse = $this->refreshAccessToken();

        if ($refreshTokenResponse && $refreshTokenResponse->access_token)
        {
            $this->accessToken = $refreshTokenResponse->access_token;
            
            return true;
        }

        return false;
    }
    
    private function upload($itemGuid, $filePath, $repeatSession = true)
    {
        // This is first request, login before
        if (empty($this->sessionId)) {
            $this->reLogin();
            
            return $this->upload($itemGuid, $filePath);
        }
		
        $url = $this->createFileUploadUrl($itemGuid, basename($filePath));
        $ch = $this->createUploadRequest($url, $filePath);
        
        $result = $this->executeCurl($ch);
        $jsonResult = json_decode($result);
        $returnCode = $jsonResult->ReturnCode;
        
        if ($returnCode == 'rcBadSession' || $returnCode == 'rcDatabaseTimeout') {
            // For rcBadSession and rcDatabaseTimeout types of return code we'll try to perform action once again
            if($repeatSession == true) {
				if ($returnCode == 'rcBadSession') {
					$this->reLogin();
				}
				return $this->upload($itemGuid, $filePath, false);
            }
        }
        
        if ($this->throwExceptionOnFail && $returnCode != 'rcSuccess') {
            throw new ResponseException($jsonResult);
        }
        
        return $jsonResult;
    }
	
	private function download($itemGuid, $revision, $targetFilePath) {
		// We cannot be sure the request will be ok (there is no return code).
		$this->reLogin();
		
		if (empty($this->sessionId)) {
			throw new Exception('Unable to obtain session for downloading.');
		}
		
		$url = $this->createWebServiceUrl('GetBinaryAttachment');
		$completeTransmitObject = array(
			'sessionId' => $this->sessionId,
			'itemGuid' => $itemGuid, 
			'revision' => $revision
		);
        $jsonObject = json_encode($completeTransmitObject, true);
		
		$targetFileHandle = fopen($targetFilePath, 'wb');
		$ch = $this->createDownloadPostRequest($url, $jsonObject, $targetFileHandle);      
		$result = curl_exec($ch);
        
        // Check if request has been executed successfully.
        if ($result === false) {            
            throw new Exception('Error occurred while communicating with service: '.curl_error($ch));
        }

        // Also Check if return code is OK.
        $curlReturnInfo = curl_getinfo($ch);
        if ($curlReturnInfo['http_code'] != 200)
        {            
            throw new Exception('Error occurred while communicating with service with http code: '.$curlReturnInfo['http_code']);
        }
		
		curl_close($ch);
		fclose($targetFileHandle);
	}
    
    private function createPostRequest($url, $data, $contentType = 'application/json')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: '.$contentType, 'Accept: application/json', 'Authorization: Bearer '.$this->accessToken));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        return $ch;
    }

    private function createUploadRequest($url, $filePath)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/octet-stream', 'Accept: application/json', 'Content-Length: '.filesize($filePath)));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_INFILE, fopen($filePath, 'r'));
        
        return $ch;
    }
	
	private function createDownloadPostRequest($url, $jsonObject, $targetFilePathHandle) {		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $targetFilePathHandle); 
		curl_setopt($ch, CURLOPT_HEADER, 0); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/octet-stream'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonObject);
		
		return $ch;
	}
}

class ResponseException extends Exception
{
    public $returnCode;
    public $description;
    public $completeResponse;
    
    public function __construct($object, $message ='', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
        $this->returnCode = $object->ReturnCode;
        $this->description = $object->Description;
        $this->completeResponse = $object;
    }
    
    public function __toString()
    {
        return $this->returnCode.": {$this->description}\n";
    }
}

class LoginException extends ResponseException
{
    public function __construct($object, $message ='', $code = 0, Exception $previous = null)
    {
        parent::__construct($object, $object->Description, $code, $previous);
    }
}
?>