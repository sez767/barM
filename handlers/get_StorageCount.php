<?php
session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Permission denied"
    )));
}

require_once dirname(__FILE__) . '/../lib/db.php';

if (
        !isset($_POST['data'])
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Invalid data"
    )));
}

$data = json_decode($_POST['data'], true);

if (
        json_last_error() != JSON_ERROR_NONE ||
        !is_array($data)
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "Decode error"
    )));
}

// print_r($data);
// die();

$result = array();

$storage = new Storage();

foreach ($data AS $item) {
    $hash = $storage->createHash($item['offer'], $item['attributes'], $item['delivery']);
    $count = $storage->getRedisStorageCount($hash);

    $offer_attributes = "";

    if (
            is_array($item['attributes']) &&
            count($item['attributes']) > 0
    ) {
        ksort($item['attributes']); // сортировка свойств по ключу (по возростанию)
        $offer_attributes = "[" . implode("][", $item['attributes']) . "]";
    }

    $result[$hash] = array(
        "hash" => $hash,
        "offer" => $item['offer'] . " " . $offer_attributes . " " . $item['delivery'],
        "count" => (int) $count
    );
}

print json_encode(array(
    "success" => TRUE,
    "data" => $result
));
