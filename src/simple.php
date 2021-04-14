<?php 

error_reporting(E_ALL);
ini_set('display_errors', '1');

$content_type = 'text/html';
$status_code = 200;

$headers = [
    'Content-Type' => $content_type,
];

// defining if request URL has a proper user_type
function fetch_data(string $user_type) {
    if(!($user_type === "teachers" || $user_type === "students")) {
        header('Content-Type: application/json', true, 404);
        print json_encode([
            'error' => 'This page does not exist'
        ]);
        exit(2);
    }
    return json_decode(file_get_contents("$user_type.json"), true);  
};

// Define a function that converts array to xml.
function arrayToXml($array, $rootElement = null, $xml = null) {
    $_xml = $xml;  
    // If there is no Root Element then insert root
    if ($_xml === null) {
        $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>');
    }
    // Visit all key value pair
    foreach ($array as $k => $v) {
        // If there is nested array then
        if (is_array($v)) {  
            // Call function for nested array
            arrayToXml($v, $k, $_xml->addChild($k));
            } 
        else {
            // Simply add child element. 
            $_xml->addChild($k, $v);
        }
    }
    return $_xml->asXML();
}


function format_response(string $mime_type = '', $data) {
    $default_mime_type = 'text/html';
    $final_data = $data;
    
    if($mime_type === 'application/json') {
        header('Content-Type: ' . $mime_type);
        $final_data = json_encode($data);
    } else if ($mime_type === 'application/xml') {
        $final_data = arrayToXml($data);
        header('Content-Type: ' . $mime_type);
    } else if($mime_type === ''){
        header('Content-Type: ' . $default_mime_type);
    }

    return $final_data;
};

function send_response( int $status_code, array $headers, $body, bool $force_send = false ) {
    $force_send ? exit(0) : null;

    foreach($headers as $key => $header) {
        header($key . ' ' . $header);
    }
    http_response_code($status_code);
    
    if($header === 'text/html') {
        for($i = 0; $i < count($body); $i++) {
            foreach($body[$i] as $key => $body_element) {
                echo ("<h1>$key: $body_element</h1>");
            }
        }
    } else {
        echo $body;
    }
    exit(0);
}

//invoking functions
if(isset($_SERVER['PATH_INFO'])) {
    $path = explode('/', $_SERVER['PATH_INFO'])[1];
    $temp_result = fetch_data($path);

    $body = format_response($content_type, $temp_result);

    send_response( $status_code, $headers, $body, false );
};
?>