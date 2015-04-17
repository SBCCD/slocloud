<?php
namespace SLOCloud;

use Assetic\Asset;
use Assetic\Filter;

require_once '../src/bootstrap.php';
require '../config/config.php';
require '../src/functions.php';
require '../data/LoadData.php';
global $config;

session_name($config['session_name']);
session_cache_limiter('');
if ($config['https'] === true) {
    $params = session_get_cookie_params();
    $params['secure'] = true;
    $params['httponly'] = true;
    call_user_func_array('session_set_cookie_params', $params);
}
header(
    "Content-Security-Policy:".
    " default-src 'self'".
    "; script-src 'self' 'unsafe-inline' 'unsafe-eval'".
    "; style-src 'self' 'unsafe-inline'"
);
header("X-Frame-Options: SAMEORIGIN");
session_start();

// Prepare app
$app = new Application([
    'templates.path' => '../resources/templates',
    'debug' => $config['debug']
], $config);

// Define routes
$app->get('/', [$app, 'requireAuthentication'], function () use ($app) {
    $app->flashKeep();
    $app->redirect('form');
});

$app->get('/login', '\SLOCloud\Controller\Login:getLogin');
$app->post('/login', '\SLOCloud\Controller\Login:postLogin');
$app->get('/logout', '\SLOCloud\Controller\Login:getLogout');

$app->get('/form', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Data:getForm');
$app->post('/form', [$app, 'requireAuthenticationAJAX'], '\SLOCloud\Controller\Data:postForm');
$app->get('/SLOSummary', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Data:getSLOSummary');
$app->get('/PSLOSummary', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Data:getPSLOSummary');
$app->get('/ILOGEOSummary', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Data:getILOGEOSummary');
$app->get('/subjects', [$app, 'requireAuthenticationAJAX'], '\SLOCloud\Controller\Data:getSubjects');
$app->get('/classes', [$app, 'requireAuthenticationAJAX'], '\SLOCloud\Controller\Data:getClasses');
$app->get('/sections', [$app, 'requireAuthenticationAJAX'], '\SLOCloud\Controller\Data:getSections');
$app->get('/slos', [$app, 'requireAuthenticationAJAX'], '\SLOCloud\Controller\Data:getSLOs');
$app->get('/plos', [$app, 'requireAuthenticationAJAX'], '\SLOCloud\Controller\Data:getPLOs');
$app->get('/sloSummaryData', [$app, 'requireAuthenticationAJAX'], '\SLOCloud\Controller\Data:getSLOSummaryData');
$app->get('/psloSummaryData', [$app, 'requireAuthenticationAJAX'], '\SLOCloud\Controller\Data:getPSLOSummaryData');
$app->get('/iloGeoSummaryData', [$app, 'requireAuthenticationAJAX'], '\SLOCloud\Controller\Data:getILOGEOSummaryData');

$app->post('/jslogger', '\SLOCloud\Controller\JsLogger:postLog');
$app->get('/beat', [$app, 'requireAuthenticationAJAX'], '\SLOCloud\Controller\HeartBeat:getBeat');

// Administrative routes
$app->get('/export', [$app, 'requireAuthentication'], '\SLOCloud\Controller\ExportImport:getExport');
$app->post('/export', [$app, 'requireAuthentication'], '\SLOCloud\Controller\ExportImport:postExport');
$app->get('/import', [$app, 'requireAuthentication'], '\SLOCloud\Controller\ExportImport:getImport');
$app->post('/import', [$app, 'requireAuthentication'], '\SLOCloud\Controller\ExportImport:postImport');

$app->get('/reset', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Data:getReset');
$app->post('/reset', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Data:postReset');
$app->get('/cache', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Utility:getCache');
$app->post('/clearCache', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Utility:postClearCache');

// These routes are meant for debugging only
$app->get('/info', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Utility:info');
$app->get('/session', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Utility:session');
$app->get('/config', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Utility:config');
$app->get('/data', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Utility:data');
$app->get('/check', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Utility:check');
$app->get('/loginAs', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Utility:getLoginAs');
$app->post('/loginAs', [$app, 'requireAuthentication'], '\SLOCloud\Controller\Utility:postLoginAs');

// Routes defined on the SLOService
$app->sloService->registerRoutes($app);

// Run app
$app->run();
