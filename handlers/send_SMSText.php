<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}

require_once dirname(__FILE__) . '/../lib/db.php';
ApiLogger::addLogVarExport($_REQUEST);
$actionHistoryObj = new ActionHistoryObj();

$objectName = '';
if (strlen($_REQUEST['phones']) > 4 || strlen($_REQUEST['uuids']) > 4) {
//    $text = str_replace("^", "%", $_REQUEST['text']);
//    $text = str_replace("//", "\\", $text);
    $text = $_REQUEST['text'];

    if (strlen($_REQUEST['phones']) > 4) {
        $phonesArr = DB::queryAssData('id', 'phone', 'SELECT * FROM staff_order WHERE id IN (' . substr($_REQUEST['phones'], 0, -1) . ') ');
        $objectName = 'StaffOrderObj';
    } elseif (strlen($_REQUEST['uuids']) > 4) {
        $phonesArr = DB::queryAssData('uuid', 'phone', 'SELECT * FROM Clients where uuid IN (' . substr($_REQUEST['uuids'], 0, -1) . ')');
        $objectName = 'ClientsObj';
    }

    ApiLogger::addLogVarExport($text);

    foreach ($phonesArr as $id => $phoneItem) {
        if ($_REQUEST['kg'] == 'true') {
            sendSms($phoneItem, $text);
        } else if($_REQUEST['ru'] == 'true') {
//            echo $phoneItem;
            $rez = sendSMS($phoneItem, $text);
            print_r($rez);
        } else {
            $rez = sendKcellSMS($phoneItem, $text);
        }
        $actionHistoryObj->save($objectName, $id, 'insert', 'SMS text', '', $text);
    }
}

echo json_encode(array('success' => true));
