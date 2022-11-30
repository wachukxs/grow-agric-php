<?php
/**
 * Get the HTTP(S) URL of the current page.
 *
 * @return string The URL.
 */
 
function currentUrl(){
    //Figure out whether we are using http or https.
    $http = 'http';
    //If HTTPS is present in our $_SERVER array, the URL should
    //start with https:// instead of http://
    if(isset($_SERVER['HTTPS'])){
        $http = 'https';
    };
    //Finally, construct the full URL.
    //Use the function htmlentities to prevent XSS attacks.
    return $http . '://' . htmlentities($_SERVER['HTTP_HOST']) . '/' . htmlentities($_SERVER['REQUEST_URI']);
}
?>