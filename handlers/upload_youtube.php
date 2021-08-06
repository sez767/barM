<?php
require_once dirname(__FILE__) . '/../lib/db.php';

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => false,
        "msg" => "Permission denied"
    )));
}

$redis = RedisManager::getInstance()->getRedis();

$ordersData = array();
//  проверка файла на ошибки
if ($_FILES['document']['error'] != 0) {
    die(json_encode(array(
        "success" => false,
        "msg" => "Ошибка: #" . $_FILES['document']['error']
    )));
}

if (!isset($_FILES['document']['tmp_name']) || empty($_FILES['document']['tmp_name']) || !file_exists($_FILES['document']['tmp_name'])) {
    die(json_encode(array(
        "success" => false,
        "msg" => "File not found"
    )));
}

require_once dirname(dirname(__FILE__)) . '/lib/excel_reader.php';

$data = new Spreadsheet_Excel_Reader($_FILES['document']['tmp_name']);
$data = (array) $data;
if (!isset($data['sheets'][0]['cells']) || count($data['sheets'][0]['cells']) == 0) {
    die(json_encode(array(
        "success" => false,
        "msg" => "Ошибка обработки файла " . $_FILES['document']['name']
    )));
}

$data = $data['sheets'][0]['cells'];

$idsArr = array();
foreach ($data as $row) {
    if (($id = (int) $row[1])) {
        $idsArr[$id] = $row[2];
    }
}

$resp = array(
    "success" => true,
    "msg" => 'Ошибка загрузки'
);
if (!empty($idsArr)) {

    foreach ($idsArr as $orderId => $youtubeUrl) {
        $redis->hMset('youtube_urls', array($orderId => $youtubeUrl));
    }

    $resp = array(
        "success" => true,
        "msg" => 'Загружено ' . count($idsArr) . ' ссылок'
    );
}
echo json_encode($resp);

