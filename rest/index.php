<?php
require_once dirname(dirname(__FILE__)) . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('web');
$modx->getService('error','error.modError', '', '');
// Boot up any service classes or packages (models) you will need

// Load the modRestService class and pass it some basic configuration
/* @var modRestService $rest */
//dirname(__FILE__).'/Class/'
$rest = $modx->getService('rest', 'rest.modRestService', '', array(
    'basePath' => dirname(__FILE__) . '/Controllers/',
    'controllerClassSeparator' => '',
    'controllerClassPrefix' => 'MyController',
    'xmlRootNode' => 'response',
    'defaultResponseFormat' => 'json',
));

// Prepare the request
$rest->prepare();
// Make sure the user has the proper permissions, send the user a 401 error if not
if (!$rest->checkPermissions()) {
    $rest->sendUnauthorized(true);
}

include dirname(__FILE__).'/Controllers/interface.php';
header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH');

// Run the request
$rest->process();