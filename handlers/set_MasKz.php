<?php

header('Content-Type: application/json; charset=utf-8', true);

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

require_once dirname(__FILE__) . '/../lib/db.php';

ApiLogger::addLogJson('START');

$apiBetaPro = new ApiBetaPro();

ApiLogger::addLogJson("\n-----------------------------START");
ApiLogger::addLogJson($_REQUEST);

$resp = array('success' => false);

$idsArr = array();
if (!empty($_REQUEST['ids_data'])) {
    $idsArr = json_decode($_REQUEST['ids_data'], true);
}

$resp = setMassKz($_REQUEST['type'], $_REQUEST['status'], $idsArr, $_REQUEST['ogr']);

echo json_encode($resp);

if (!$resp['success']) {
    ApiLogger::addLogJson('eto kakoy-to pizdec');
}
ApiLogger::addLogJson('-----------------------------END');
