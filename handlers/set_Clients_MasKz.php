<?php

header('Content-Type: application/json; charset=utf-8', true);

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

require_once dirname(__FILE__) . '/../lib/db.php';

$apiBetaPro = new ApiBetaPro();

ApiLogger::addLogJson("\n-----------------------------START");
ApiLogger::addLogJson($_REQUEST);

$resp = array('success' => false);

$idsArr = array();
if (!empty($_REQUEST['ids_data'])) {
    $idsArr = json_decode($_REQUEST['ids_data'], true);
}

if (!empty($idsArr)) {

    $addArr = array();


    if (($limit = (int) $_REQUEST['ogr'])) {
        $it_ar = array_chunk($idsArr, $limit);
        $idsArr = $it_ar[0];
    }

    $addArr[$_REQUEST['type']] = $_REQUEST['status'];

    // DOBRIK
//    if (in_array($_SESSION['Logged_StaffId'], array(11111111, 11113333))) {
//        print_r($addArr);
//        die;
//    }

    ApiLogger::addLogJson('pre update');
    ApiLogger::addLogJson($addArr);
    ApiLogger::addLogJson($idsArr);

    $origAssArr = DB::queryAssArray('uuid', 'SELECT * FROM Clients WHERE client_group > 0 AND uuid IN %ls', $idsArr);
    if (DB::update('Clients', $addArr, 'uuid IN %ls', $idsArr)) {
        ApiLogger::addLogJson('update ok');
        $historyArr = array();
        foreach ($origAssArr as $uuid => $origData) {
            foreach ($addArr as $key => $val) {
                $historyArr[] = array(
                    'object_id' => $uuid,
                    'type' => 'update',
                    'property' => $key,
                    'was' => $origData[$key],
                    'set' => $val
                );
            }
        }

        $actionHistoryObj = new ActionHistoryObj();
        $actionHistoryObj->saveAll('ClientsObj', $historyArr, null, 'set_Clients_MasKz');
        ApiLogger::addLogJson('save history ok');
        $resp['success'] = true;
    }
}
echo json_encode($resp);

if (!$resp['success']) {
    ApiLogger::addLogJson('eto kakoy-to pizdec');
}
ApiLogger::addLogJson('-----------------------------END');
