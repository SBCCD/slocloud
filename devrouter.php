<?php
/**
 * @file
 * This router is only used when using the built-in php development web server
 *
 * Usage:
 *
 * php -S localhost:8000 -t public -c tests\php.ini devrouter.php
 * or
 * phing devserver
 */

$url = parse_url($_SERVER["REQUEST_URI"]);
if (file_exists('.'.$url['path'])) {
    // Serve the requested resource as-is.
    return false;
}

// Simulates routing all requests to the index.php file
// this is usually specified in the web.config or .htaccess file and done by the web server
$_SERVER['SCRIPT_NAME'] = "/index.php";
include 'public/index.php';
