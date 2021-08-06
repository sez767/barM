<?php

/**
 * Сбор данных о заказах с доставкой на "сегодня" и отправка на E-Mail курьерам
 * Заказы с доставкой на сегодня
 * Упаковочный лист
 * Конверт
 */
# error_reporting(E_ALL);
# ini_set("display_errors", 1);

require_once dirname(__FILE__) . '/../lib/db.php';
$_SESSION['api_log_enable'] = true;

if (!isset($_SESSION['Logged_StaffId'])) {
    header('Location: login.html');
    die;
}

header('Content-Type: text/html; charset=utf-8');
$ids = array();

if (!empty($_REQUEST['ids_data'])) {
    $ids = json_decode($_REQUEST['ids_data'], true);
}
foreach ($ids as $key => $id) {
    if (strlen($id) == 0 || $id <= 0) {
        unset($ids[$key]);
    }
}
if (count($ids) == 0) {
    die(json_encode(array(
        'success' => false,
        'data' => array('Empty set of IDs')
    )));
}

$result = array();

//
// Start baribarda selection
//
$staffOrderObj = new StaffOrderObj();
$sql = "SELECT id, logos_desc FROM `{$staffOrderObj->cGetTableName()}` WHERE id IN %li AND logos_desc LIKE 'ketkz_id:%' AND logos_desc IS NOT NULL";
if (($sendedIds = DB::queryAssData('id', 'logos_desc', $sql, $ids))) {
    if (($ketData = getFromKet($sendedIds, array('uid' => 85935432, 'secret' => 'zPGMr1Sy')))) {

        $updateCount = 0;
        foreach ($ketData as $ketItem) {
            if (!empty($ketItem['kz_code'])) {
                DB::update($staffOrderObj->cGetTableName(), array('kz_code' => $ketItem['kz_code']), 'id = %i', $ketItem['ext_id']);
                $updateCount++;
            }
        }
        $result['data'][] = "Обновлено $updateCount кодов";

    } else {
        $result['data'][] = "Не найдено отправленых заказов";
    }
} else {
    $result['data'][] = "Не найдено отправленых заказов";
}

//////////////////////////////////////////////////////////////////////
//
// Показать результат
print json_encode($result);

function getFromKet($orderIds, $conf) {

    $data = array();
    foreach ($orderIds as $orderId) {
        $data[] = array('id' => preg_replace('/\D/', '', $orderId));
    }

    ApiLogger::addLogVarExport($data);

    $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

    ApiLogger::addLogVarExport('$dataJson');
    ApiLogger::addLogVarExport($dataJson);

    $url = 'http://ketkz.com/api/get_orders.php?uid=' . $conf['uid'] . '&s=' . $conf['secret'];

//    echo "-url => $url\n";
//    echo "-post=> $dataJson\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('data' => $dataJson));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Pragma: no-cache'));

    $result['EXE'] = curl_exec($ch);
    $result['INF'] = curl_getinfo($ch);
    $result['ERR'] = curl_error($ch);

    ApiLogger::addLogVarExport($result);

    return json_decode($result['EXE'], true);
}
