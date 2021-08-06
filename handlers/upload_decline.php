<?php
session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    die(json_encode(array(
        "success" => false,
        "msg" => "Permission denied"
    )));
}

require_once dirname(__FILE__) . '/../lib/db.php';
require_once (dirname(__FILE__) . "/../lib/class.staff.php");


$ordersData = array();
//ini_set('max_execution_time', 0);
//error_reporting(E_ALL | E_ERROR);
//ini_set('display_errors', 'On');
//  проверка файла на ошибки
if ($_FILES['document']['error'] != 0) {
    die(json_encode(array(
        "success" => false,
        "msg" => "Ошибка: #" . $_FILES['document']['error']
    )));
}

//  проверка расширения файла
if ($_FILES['document']['size'] <= 100) {
    die(json_encode(array(
        "success" => false,
        "msg" => "Слишком малый размер файла " . $_FILES['document']['name'] . ". Проверьте целостность данных!"
    )));
}

if (!isset($_FILES['document']['tmp_name']) || empty($_FILES['document']['tmp_name']) || !file_exists($_FILES['document']['tmp_name'])) {
    die(json_encode(array(
        "success" => false,
        "msg" => "File not found"
    )));
}

require_once dirname(dirname(__FILE__)) . '/lib/excel_reader.php';

//  парсим файл
$data = array();
$data = new Spreadsheet_Excel_Reader($_FILES['document']['tmp_name']);

$dataSheets = (array) $data;
if (!isset($dataSheets['sheets'][0]['cells']) || count($dataSheets['sheets'][0]['cells']) == 0) {
    die(json_encode(array(
        "success" => false,
        "msg" => "Ошибка обработки файла " . $_FILES['document']['name']
    )));
}

$month = (isset($_POST['month']) && ($_POST['month'] >= 1 && $_POST['month'] <= 12) ? (int) $_POST['month'] : 0);

$setArr = array();
$dataSheet = $dataSheets['sheets'][0]['cells'];
$idsArr = array();
foreach ($dataSheet as $row) {
    if (($id = (int) $row[1])) {
        $idsArr[] = $id;
    }
}

if (!empty($idsArr)) {
    $origAssArr = DB::queryAssArray('id', 'SELECT * FROM staff_order WHERE id IN %li', $idsArr);

    if ($_SESSION['Logged_StaffId'] == 78945378) {
        $updateArr = array(
            'status' => 'Брак',
            'description' => 'Некорректный заказ'
        );
    } else {
        $updateArr = array(
            'status' => 'Отменён',
            'description' => 'Превышен лимит попыток связи'
        );
    }

    if (DB::update('staff_order', $updateArr, 'id IN %li', $idsArr)) {
        $actionHistoryObj = new ActionHistoryObj();
        foreach ($origAssArr as $id => $origData) {
            foreach ($updateArr as $key => $val) {
                $actionHistoryObj->save('StaffOrderObj', $id, 'update', $key, $origData[$key], $val);
            }
        }
    }
}

print json_encode(array(
    "success" => true,
    "msg" => 'Отменено ' . count($ordersData) . ' заказов'
));
