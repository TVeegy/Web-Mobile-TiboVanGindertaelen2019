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

// Define API response codes and their related HTTP response
$api_response_code = array(0 => array('HTTP Response' => 400, 
'Message' => 'Unknown Error'), 1 => array('HTTP Response' => 200, 
'Message' => 'Success'), 2 => array('HTTP Response' => 403, 
'Message' => 'HTTPS Required'), 3 => array('HTTP Response' => 401, 
'Message' => 'Authentication Required'), 4 => array('HTTP Response' => 401, 
'Message' => 'Authentication Failed'), 5 => array('HTTP Response' => 404, 
'Message' => 'Invalid Request'), 6 => array('HTTP Response' => 400, 
'Message' => 'Invalid Response Format'), 7 => array('HTTP Response' => 400, 
'Message' => 'DB problems'));

// Set default HTTP response of 'ok' or NOK in this case
$response['code'] = 0;
$response['status'] = 404;
$response['data'] = NULL;

// Define whether an HTTPS connection is required
$HTTPS_required = FALSE;

// Define whether user authentication is required
$authentication_required = FALSE; // staat nu op false. Test dit eens met true, en geef de nodige login credentials mee

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname) or die(mysqli_connect_error());
// de or die() kan vervangen worden door de juiste aanroep van deliver_response();
// dit wordt later gedaan toch nog gedaan op de juiste plaatsen, dus we raken niet verder dan hier.
// Dit treedt normaal enkel op wanneer dit niet nog niet juist is ingesteld.

//require_once "functies.php";


// de manier waarop we via fetch data meegaven, zorgt er voor dat
// de parameters niet in $_POST, maar in de body van de request zitten
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

    // Define HTTP responses
    $http_response_code = array(200 => 'OK', 400 => 'Bad Request', 
    401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found');

    // Set HTTP Response
    header('HTTP/1.1 ' . $api_response['status'] . ' ' . $http_response_code[$api_response['status']]);

    // Process different content types
    if (strcasecmp($format, 'json') == 0) {
        // Set HTTP Response Content Type
        header('Content-Type: application/json; charset=utf-8');

        // Format data into a JSON response
        $json_response = json_encode($api_response);

        // Deliver formatted data
        echo $json_response;
    } 
    
    elseif (strcasecmp($format, 'xml') == 0) {
        // Set HTTP Response Content Type
        header('Content-Type: application/xml; charset=utf-8');

        // Format data into an XML response (This is only good at handling string data, not arrays)
        $xml_response = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<response>' . "\n" . "\t" . '<code>' . $api_response['code'] . '</code>' . "\n" . "\t" . '<data>' . $api_response['data'] . '</data>' . "\n" . '</response>';

        // Deliver formatted data
        echo $xml_response;
    } 
    
    else {
        // Set HTTP Response Content Type (This is only good at handling string data, not arrays)
        header('Content-Type: text/html; charset=utf-8');

        // Deliver formatted data
        echo $api_response['data'];
    }
    // End script process
    exit ;
}

// security issue : als de m = register, geen login nodig ...
if (strcasecmp($_GET['m'], 'register') == 0) {
    $authentication_required = FALSE;
}
if (strcasecmp($_GET['m'], 'hello') == 0) {
    $authentication_required = FALSE; // om deze functie te testen is geen login nodig ...
}

/* //----------// STEP 2: AUTHORISATION //----------// */

// Optionally require connections to be made via HTTPS
if ($HTTPS_required && $_SERVER['HTTPS'] != 'on') {
    HandleConnectionFailed($response, 2, $api_response_code[$response['code']]['Message']);

    // Return Response to browser. This will exit the script.
    deliver_response($_GET['format'], $response);
}

// Optionally require user authentication
if ($authentication_required) {

    if (empty($postvars['user']) || empty($postvars['password'])) {
        HandleConnectionFailed($response, 3, $api_response_code[$response['code']]['Message']);

        // Return Response to browser
        deliver_response($postvars['format'], $response);
    }

    // Return an error response if user fails authentication. This is a very simplistic example
    // that should be modified for security in a production environment
    else {
        if (!$conn) {
            HandleConnectionFailed($response, 7);

            // Return Response to browser
            deliver_response($postvars['format'], $response);

        } else {
            // de login nakijken
            $result = ReturnSimpleOutputQuery("select * FROM users where NAME like '" . $postvars['name'] . "' and PW like '" . $postvars['password'] . "'");
            $rows = array();
            if (!$result) {
                HandleConnectionFailed($response, 7);

                // Return Response to browser
                deliver_response($postvars['format'], $response);
            } else {
                //$response['data'] = "ok";
                while ($row = $result -> fetch_assoc()) {
                    $rows[] = $row;
                }
                if (count($rows) == 0) {
                    HandleConnectionSuccess($response, 4, $api_response_code[$response['code']]['Message']);

                    // Return Response to browser
                    deliver_response($postvars['format'], $response);
                }
            }
        }
    }
}

/* //----------// STEP 2: PROCESS REQUEST //----------// */

// ---------- (1) Hello API ---------- //
if (strcasecmp($_GET['m'], 'hello') == 0) {
    HandleConnectionSuccess($response, 1, 'Hello World');
}

// ---------- (2) User Login ---------- //
if (strcasecmp($_GET['m'], 'login') == 0) {

    if (!$conn) {
        HandleConnectionFailed($response, 0);

    } else {
        HandleConnectionSuccess($response, 1);
        // de login nakijken
        // let op : we halen deze uit $postvars ipv uit $_POST, wat je online meer zal tegenkomen.
        $lQuery = "select * FROM users where NAME like '" . $postvars['name'] . "' and PW like '" . $postvars['password'] . "'";
        $result = $conn -> query($lQuery);
        $rows = array();
        if (!$result) {
            $response['data'] = "db error";
        } else {

            while ($row = $result -> fetch_assoc()) {
                $rows[] = $row;
            }
            if (count($rows) > 0) {
                HandleConnectionSuccess($response, 1, $rows[0]);
            } else {
                HandleConnectionFailed($response, 4, $api_response_code[$response['code']]['Message']);
            }
        }
    }
}


// ---------- (3) Get ServerTime ---------- //
if (strcasecmp($_GET['m'], 'getTime') == 0) {

    if (!$conn) {
        HandleConnectionFailed($response, 0);

    } else {
        HandleConnectionSuccess($response, 1);
        // het tijdstip van de server opvragen (volgens de db), zodat we kunnen
        // synchroniseren met bvb onze eigen app.
        HandleSimpleOutputQuery('select now() as servertime', 1, true);
    }
}

// ---------- (4) Count Producten ---------- //
if (strcasecmp($_GET['m'], 'getProductSom') == 0) {

    if (!$conn) {
        HandleConnectionFailed($response, 0);

    } else {
        HandleConnectionSuccess($response, 1);
        // het tijdstip van de server opvragen (volgens de db), zodat we kunnen
        // synchroniseren met bvb onze eigen app.
        HandleSimpleOutputQuery('select Count(*) as productSom FROM producten', 1, true);
    }
}

// ---------- (5) List Producten ---------- //
if (strcasecmp($_GET['m'], 'getProducten') == 0) {

    if (!$conn) {
        HandleConnectionFailed($response, 0);

    } else {
        HandleConnectionSuccess($response, 1);
        // de login nakijken
        // @FIXME : nakijken of hier niets moet gedaan worden met deze input : in welk formaat is dit?
        // vooral met speciale tekens zoals in Björn moet ik opletten (op deze server :-/)
        HandleSimpleOutputQuery("select * FROM producten", 1);
    }
}

// ---------- (1) Insert & Get Product ---------- //
if (strcasecmp($_GET['m'], 'createAndGetProduct') == 0) {

    if (!$conn) {
        HandleConnectionFailed($response, 0);

    } else {
        HandleConnectionSuccess($response, 1);
        
        HandleSimpleInputQuery("Insert into Producten (Omschrijving, Prijs) Values ('" . $postvars['prodOmschr'] . "','" . $postvars['prodPrijs'] . "')");

        HandleSimpleOutputQuery("select * FROM producten", 1);
    }
}

// ---------- (X) Abstracted Logic ---------- //
function ReturnSimpleOutputQuery($lQuery) {
    return $GLOBALS['conn'] -> query($lQuery);
}
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
function HandleSimpleInputQuery($lQuery){
    $GLOBALS['conn']->query($lQuery);
}
function HandleConnectionFailed($response, $data){
    $response['code'] = 0;
    $response['status'] = $GLOBALS['api_response_code'][$response['code']]['HTTP Response'];
    $response['data'] = $data;
}

function HandleConnectionSuccess($response, $code=1, $responseData=null){
    //https://stackoverflow.com/questions/1953857/fatal-error-cannot-redeclare-function
    //https://www.php.net/manual/en/functions.variable-functions.php
    //https://www.designcise.com/web/tutorial/whats-the-difference-between-null-coalescing-operator-and-ternary-operator-in-php

    if (!function_exists('IsNull') && !function_exists('IsNotNull')){
        function IsNull(){
            
        }
        function IsNotNull(){
            
        }
    }
    
    $response['code'] = $code;
    $response['status'] = $GLOBALS['api_response_code'][$response['code']]['HTTP Response'];
    ($execute = ($responseData==null)?'IsNull':'IsNotNull')($responseData);

    if ($responseData == null){
        $GLOBALS['response']['data'] = $responseData;
    }
}

/* //----------// STEP 3: CLOSE DB CONNECTION //----------// */
mysqli_close($conn);

/* //----------// STEP 2: DELIVER RESPONSE //----------// */
deliver_response($postvars['format'], $response);

?>
