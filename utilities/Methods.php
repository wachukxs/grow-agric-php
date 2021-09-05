<?php
/**
 * Get the HTTP(S) URL of the current page.
 *
 * @param $server The $_SERVER superglobals array.
 * @return string The URL.
 */
 
function currentUrl($server){
    //Figure out whether we are using http or https.
    $http = 'http';
    //If HTTPS is present in our $_SERVER array, the URL should
    //start with https:// instead of http://
    if(isset($server['HTTPS'])){
        $http = 'https';
    }
    //Get the HTTP_HOST.
    $host = $server['HTTP_HOST'];
    //Get the REQUEST_URI. i.e. The Uniform Resource Identifier.
    $requestUri = $server['REQUEST_URI'];
    //Finally, construct the full URL.
    //Use the function htmlentities to prevent XSS attacks.
    return $http . '://' . htmlentities($host) . '/' . htmlentities($requestUri);
}
?>