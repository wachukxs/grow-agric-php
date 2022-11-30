<?php

session_start();

// Headers
// https://stackoverflow.com/a/17098221
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : NULL;
$allowed_domains = [
    'https://farmers.growagric.com',
    'https://admin.growagric.com',
    'https://fieldagents.growagric.com',
    'https://investors.growagric.com',
    'https://grow-agric.netlify.app',
    'https://grow-agric-admin.netlify.app',
    'https://grow-agric-fieldagents.netlify.app',
    'http://localhost:4005',
    'http://localhost:4009',
    'http://localhost:4011',
    'http://localhost:4007',
    'https://grow-agric-farmers-testing.netlify.app'
];
// output to debug console/output
// file_put_contents('php://stderr', print_r('Checking origin ' . $origin . ' for CORS access' . "\n", TRUE)); // or var_export($foo, true)

if (isset($origin) && in_array($origin, $allowed_domains)) {
    // file_put_contents('php://stderr', print_r('Valid CORS access for ' . $origin . "\n", TRUE));
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    file_put_contents('php://stderr', print_r('Invalid CORS access for ' . $origin . ". Trying fallback\n", TRUE));
    header('Access-Control-Allow-Origin: *'); // Should disable access
}
header('Access-Control-Allow-Origin: *'); // doesn't work when withCredentials is set to true.
header('Access-Control-Allow-Headers: Content-Type, x-requested-with, timeout, Referer, User-Agent, Accept');
header('Content-Type: application/json');
// header('Content-Type: application/text');
header('Content-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Credentials: true');
// why do we want only 3 mins?
// header('Cache-Control: max-age=300'); // 300 = 3 minutes
// duplicate header
header('Content-Control-Allow-Headers: Content-Control-Allow-Methods, User-Agent, Content-Type, Content-Control-Allow-Headers, Authorization, X-Requested-With, *');
