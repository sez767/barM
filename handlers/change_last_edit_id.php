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

if (($newStaffId = (int) $_REQUEST['staff_id']) && !empty($idsArr)) {

    ApiLogger::addLogJson('pre update');
    ApiLogger::addLogJson($idsArr);

    if (($limit = (int) $_REQUEST['ogr'])) {
        $it_ar = array_chunk($idsArr, $limit);
        $idsArr = $it_ar[0];
    }

    $origAssArr = DB::queryAssArray('id', 'SELECT * FROM staff_order WHERE id IN %li', $idsArr);

    $updateArr = array('last_edit' => $newStaffId);
    if (DB::update('staff_order', $updateArr, 'id IN %li', $idsArr)) {
        ApiLogger::addLogJson('update ok');
        $historyArr = array();
        foreach ($origAssArr as $id => $origData) {
            foreach ($updateArr as $key => $val) {
                $historyArr[] = array(
                    'object_id' => $id,
                    'type' => 'update',
                    'property' => $key,
                    'was' => $origData[$key],
                    'set' => $val
                );
            }
        }

        $actionHistoryObj = new ActionHistoryObj();
        $actionHistoryObj->saveAll('StaffOrderObj', $historyArr, null, 'change_last_edit_id');
        ApiLogger::addLogJson('save history ok');
        $resp['success'] = true;
    }
}
echo json_encode($resp);

if (!$resp['success']) {
    ApiLogger::addLogJson('eto kakoy-to pizdec');
}
ApiLogger::addLogJson('-----------------------------END');
