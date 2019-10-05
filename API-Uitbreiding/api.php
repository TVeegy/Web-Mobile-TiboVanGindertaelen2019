<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

//
// API Demo
//
// This script provides a RESTful API interface for a web application
//
// Input:
//
// $_GET['format'] = [ json | html | xml ]
// $_GET['m'] = []
//
// Output: A formatted HTTP response
//
// Author: Mark Roland
//
// History:
// 11/13/2012 - Created
//
// Adapted by:
// Steven Ophalvens
// -------------------------
// Tijdens een volgende les volgt een meer beveiligde, maar iets meer complexe versie van deze API.
// -------------------------

/* //----------// STEP 0: DB CONNECTION //----------// */
//require_once "dbcon.php";

// een verbinding leggen met de databank
$servername = "localhost";
$username = "root";// dangerous
$password = "";// dangerous
$dbname = "test";// standaard test databank

// Defining API response codes and their related HTTP response
$api_response_code = array(0 => array('HTTP Response' => 400, 
'Message' => 'Unknown Error'), 1 => array('HTTP Response' => 200, 
'Message' => 'Success'), 2 => array('HTTP Response' => 403, 
'Message' => 'HTTPS Required'), 3 => array('HTTP Response' => 401, 
'Message' => 'Authentication Required'), 4 => array('HTTP Response' => 401, 
'Message' => 'Authentication Failed'), 5 => array('HTTP Response' => 404, 
'Message' => 'Invalid Request'), 6 => array('HTTP Response' => 400, 
'Message' => 'Invalid Response Format'), 7 => array('HTTP Response' => 400, 
'Message' => 'DB problems'));

// Setting default HTTP response of 'ok' or NOK in this case
$response['code'] = 0;
$response['status'] = 404;
$response['data'] = NULL;

// Defining whether an HTTPS connection is required
$HTTPS_required = FALSE;

// Defining whether user authentication is required
$authentication_required = FALSE; // staat nu op false. Test dit eens met true, en geef de nodige login credentials mee

// Creating connection
$conn = mysqli_connect($servername, $username, $password, $dbname) or die(mysqli_connect_error());
// de or die() kan vervangen worden door de juiste aanroep van deliver_response();
// dit wordt later gedaan toch nog gedaan op de juiste plaatsen, dus we raken niet verder dan hier.
// Dit treedt normaal enkel op wanneer dit niet nog niet juist is ingesteld.

//require_once "functies.php";


// Note: Our Fetch-approach exposes data using the request-body (not the POST itself)
$body = file_get_contents('php://input');
$postvars = json_decode($body, true);

/* //----------// STEP 2: INIT VARIABLES AND FUNCTIONS //----------// */

///**
// * Deliver HTTP Response
// * @param string $format The desired HTTP response content type: [json, html, xml]
// * @param string $api_response The desired HTTP response data
// * @return void
// **/
function deliver_response($format, $api_response) {

    // Defining HTTP responses
    $http_response_code = array(200 => 'OK', 400 => 'Bad Request', 
    401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found');

    // Setting HTTP Response
    header('HTTP/1.1 ' . $api_response['status'] . ' ' . $http_response_code[$api_response['status']]);

    // Processing different content types
    if (strcasecmp($format, 'json') == 0) {
        // Setting HTTP Response Content Type
        header('Content-Type: application/json; charset=utf-8');

        // Formatting data into a JSON response
        $json_response = json_encode($api_response);

        // Delivering formatted data
        echo $json_response;
    } 
    
    elseif (strcasecmp($format, 'xml') == 0) {
        // Setting HTTP Response Content Type
        header('Content-Type: application/xml; charset=utf-8');

        // Formatting data into an XML response (This is only good at handling string data, not arrays)
        $xml_response = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<response>' . "\n" . "\t" . '<code>' . $api_response['code'] . '</code>' . "\n" . "\t" . '<data>' . $api_response['data'] . '</data>' . "\n" . '</response>';

        // Delivering the formatted data
        echo $xml_response;
    } 
    
    else {
        // Setting HTTP Response Content Type (This is only good at handling string data, not arrays)
        header('Content-Type: text/html; charset=utf-8');

        // Delivering formatted data
        echo $api_response['data'];
    }
    // End script process
    exit ;
}

// Setting Security/Authorisation Options:
if (strcasecmp($_GET['m'], 'register') == 0) {
    // security issue : als de m = register, geen login nodig ...
    $authentication_required = FALSE;
}
if (strcasecmp($_GET['m'], 'hello') == 0) {
    // Disabled authentication for the function 'hello'
    $authentication_required = FALSE;
}

/* //----------// STEP 2: AUTHORISATION //----------// */

// Optionally require HTTPS connections:
if ($HTTPS_required && $_SERVER['HTTPS'] != 'on') {
    // Raise connection failed
    HandleConnectionFailed($response, 2, $api_response_code[$response['code']]['Message']);

    // Prematurely return response to browser. (Exit script)
    deliver_response($_GET['format'], $response);
}

// Handle user authentication (if requested):
if ($authentication_required) {

    // Raise failed Connection if no username/password is given (and return response):
    if (empty($postvars['user']) || empty($postvars['password'])) {
        HandleConnectionFailed($response, 3, $api_response_code[$response['code']]['Message']);

        deliver_response($postvars['format'], $response);
    }

    // Continue by checking connection status and ultimately the credentials:
    else {
        // Check Connection:
        if (!$conn) {
            HandleConnectionFailed($response, 7);
            deliver_response($postvars['format'], $response);

        } 
        // Check Credentials:
        else {
            // Check DB using credentials
            $result = ReturnSimpleOutputQuery("select * FROM users where NAME like '" . $postvars['name'] . "' and PW like '" . $postvars['password'] . "'");
            
            // Raise failed connection when no entries are found
            if (!$result) {
                HandleConnectionFailed($response, 7);
                deliver_response($postvars['format'], $response);
            } 
            
            // Initialise as array and fill with rows
            $rows = array();
            else {
                $response['data'] = "ok";
                while ($row = $result -> fetch_assoc()) {
                    $rows[] = $row;
                }
                // Raises connection succesful
                if (count($rows) == 0) {
                    HandleConnectionSuccess($response, 4, $api_response_code[$response['code']]['Message']);
                    deliver_response($postvars['format'], $response);
                }
            }
        }
    }
}

/* //----------// STEP 2: PROCESS REQUEST //----------// */

// ---------- (1) Hello API ---------- //
// Perform no operations and raise connection succesful:
if (strcasecmp($_GET['m'], 'hello') == 0) {
    HandleConnectionSuccess($response, 1, 'Hello World');
}

// ---------- (2) User Login ---------- //
if (strcasecmp($_GET['m'], 'login') == 0) {

    // Raise connection failed if no connection is made
    if (!$conn) {
        HandleConnectionFailed($response, 0);

    } 
    // Raise connection succesful
    else {
        HandleConnectionSuccess($response, 1);
        // Note: Data from $postvars and NOT from $_POST
        // Execute Search query
        $lQuery = "select * FROM users where NAME like '" . $postvars['name'] . "' and PW like '" . $postvars['password'] . "'";
        $result = $conn -> query($lQuery);
        
        // Raise DB-connection failed
        if (!$result) {
            // could be extended..
            $response['data'] = "db error";
        } 
        // Initialise as array and fill with rows
        $rows = array();
        else {
            while ($row = $result -> fetch_assoc()) {
                $rows[] = $row;
            }
            // Raise connection succesful when records are found
            if (count($rows) > 0) {
                HandleConnectionSuccess($response, 1, $rows[0]);
            } 
            // Raise connection failed
            else {
                HandleConnectionFailed($response, 4, $api_response_code[$response['code']]['Message']);
            }
        }
    }
}


// ---------- (3) Get ServerTime ---------- //
if (strcasecmp($_GET['m'], 'getTime') == 0) {
    // Raise connection failed
    if (!$conn) {
        HandleConnectionFailed($response, 0);

    } 
    // Raise connection succesful
    else {
        HandleConnectionSuccess($response, 1);
        HandleSimpleOutputQuery('select now() as servertime', 1, true);
    }
}

// ---------- (4) Count Producten ---------- //
if (strcasecmp($_GET['m'], 'getProductSom') == 0) {
    // Raise connection failed
    if (!$conn) {
        HandleConnectionFailed($response, 0);

    } 
    // Raise connection succesful
    else {
        HandleConnectionSuccess($response, 1);
        HandleSimpleOutputQuery('select Count(*) as productSom FROM producten', 1, true);
    }
}

// ---------- (5) List Producten ---------- //
if (strcasecmp($_GET['m'], 'getProducten') == 0) {
    // Raise connection failed
    if (!$conn) {
        HandleConnectionFailed($response, 0);

    } 
    // Raise connection succesful
    else {
        HandleConnectionSuccess($response, 1);
        //Note: check input for special alphanumerics (Björn) (format)
        HandleSimpleOutputQuery("select * FROM producten", 1);
    }
}

// ---------- (1) Insert & Get Product ---------- //
if (strcasecmp($_GET['m'], 'createAndGetProduct') == 0) {
    // Raise connection failed
    if (!$conn) {
        HandleConnectionFailed($response, 0);

    } 
    // Raise connection succesful
    else {
        HandleConnectionSuccess($response, 1);
        
        // Note: If possible change last-item-approach to ID-based approach
        // Create (input) a record
        HandleSimpleInputQuery("Insert into Producten (Omschrijving, Prijs) Values ('" . $postvars['prodOmschr'] . "','" . $postvars['prodPrijs'] . "')");

        // Read (output) the created record
        HandleSimpleOutputQuery("sELECT TOP 1 * FROM Table ORDER BY ID DESC", 1);
    }
}

// ---------- (X) Abstracted Logic ---------- //
// Returns a simply computed query, outputting data from the DB to the API
function ReturnSimpleOutputQuery($lQuery) {
    return $GLOBALS['conn'] -> query($lQuery);
}

// Handles a simple computed query, outputting data from the DB to the API and processing the result (to response)
function HandleSimpleOutputQuery($lQuery, $codeAtSuccess, $singleResultMode = false){
    $result = $GLOBALS['conn'] -> query($lQuery);
    $rows = array();
    if (!$result) {
        $GLOBALS['response']['data'] = "db error";
    } 
    else {
        while ($row = $result -> fetch_assoc()) {
            $rows[] = $row;
        }
        HandleConnectionSuccess($GLOBALS['response'], $codeAtSuccess, ($singleResultMode?$rows[0]:$rows));
    }
}

// Handles a simple computed query, inputting data from the API to the DB
function HandleSimpleInputQuery($lQuery){
    $GLOBALS['conn']->query($lQuery);
}

// Handles a connection-failed-event, processing the accompanying data to the response
function HandleConnectionFailed($response, $data){
    $response['code'] = 0;
    $response['status'] = $GLOBALS['api_response_code'][$response['code']]['HTTP Response'];
    $response['data'] = $data;
}

// Handles a connection-success-event, processing the accompanying data to the response
function HandleConnectionSuccess($response, $code=1, $responseData=null){
    // Could be extended..
    if (!function_exists('IsNull') && !function_exists('IsNotNull')){
        function IsNull(){
            
        }
        function IsNotNull(){
            
        }
    }
    
    $response['code'] = $code;
    $response['status'] = $GLOBALS['api_response_code'][$response['code']]['HTTP Response'];
    ($execute = ($responseData==null)?'IsNull':'IsNotNull')($responseData);

    // Determines whether response-data is given and allows for internal handling (possible extension)
    if ($responseData == null){
        $GLOBALS['response']['data'] = $responseData;
    }
}

/* //----------// STEP 3: CLOSE DB CONNECTION //----------// */
mysqli_close($conn);

/* //----------// STEP 2: DELIVER RESPONSE //----------// */
deliver_response($postvars['format'], $response);

?>
