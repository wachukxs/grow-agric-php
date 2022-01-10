<?php

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
    'http://localhost:4005',
    'http://localhost:4009',
    'http://localhost:4011',
    'http://localhost:4007',
];
// output to debug console/output
file_put_contents('php://stderr', print_r('Checking origin ' . $origin . ' for CORS access' . "\n", TRUE)); // or var_export($foo, true)

if (isset($origin) && in_array($origin, $allowed_domains)) {
    file_put_contents('php://stderr', print_r('Valid CORS access for ' . $origin . "\n", TRUE));
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    file_put_contents('php://stderr', print_r('Invalid CORS access for ' . $origin . ". Trying fallback\n", TRUE));
    header('Access-Control-Allow-Origin: *');
}
// header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/json');
header('Content-Control-Allow-Methods: POST, GET');
header('Content-Control-Allow-Headers: Content-Control-Allow-Methods, Content-Type, Content-Control-Allow-Headers, Authorization, X-Requested-With');
