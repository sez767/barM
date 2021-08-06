<?php
/**
 * @author: Offroad
 * Date: 29.09.15
 * Time: 11:30
 * @email: byyy@offroad
 */
session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.php");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

/** PHPExcel */
require_once(dirname(__FILE__) . '/../lib/PHPExcel-1.8/Classes/PHPExcel.php');

/** PHPExcel_Writer_Excel2007 */
require_once(dirname(__FILE__) . '/../lib/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php');

if (!isset($_GET['data']) || !isset($_GET['id'])) {
    die('Not enough data');
}
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

$getData = json_decode($_GET['data'], true);
$id = $_GET['id'];

// Set properties
$objPHPExcel->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(10);
$objPHPExcel->getProperties()->setCreator("Offroad");
$objPHPExcel->getProperties()->setLastModifiedBy("Offroad");
$objPHPExcel->getProperties()->setTitle("Storage offers");
$objPHPExcel->getProperties()->setSubject("Storage offers");
$objPHPExcel->getProperties()->setDescription("Storage offers, generated using PHP classes.");

array_walk($getData['dates'], function(&$item) {
    $item = date('Y-m-d', strtotime($item));
});

$query = "
SELECT
    `storage`.`id` AS `id`,
    `storage`.`offer` AS `offer_id`,
	`storage`.`delivery` AS `delivery`,
    `offers`.`offer_name` AS `offer_name`,
    `offers`.`offer_desc` AS `offer_desc`,
    `offers`.`offers_active` AS `offers_active`,
    CONCAT(`storage`.`offer`,' ',`storage`.`property`) AS `offer_storage_name`,
    `offer_property`.`property_name` AS `offer_property_name`,
    `offer_property`.`property_value` AS `offer_property_value`
FROM
    `storage`
JOIN
    `offers` ON `offers`.`offer_name` = `storage`.`offer`
LEFT JOIN
    `offer_property` ON `offer_property`.`property_id` = `storage`.`property`
    where `offers`.offers_active = 1";

$query .= " ORDER BY delivery,id ASC";

$rs = mysql_query($query);

$data = array();

while ($obj = mysql_fetch_assoc($rs)) {
    $sql = 'select * from `storage_action` where `storage_id` = ' . $obj['id'] . ' and datetime between \'' . ($getData['dates']['start'] . ' 00:00:00') . '\' AND \'' . ($getData['dates']['end'] . ' 23:59:59') . '\';';
    $query = mysql_query($sql);

    $qqq = 'select action_value from storage_action where storage_id = ' . $obj['id'] . ' and action_type = \'rest_start\' and `datetime` <= \'' . ($getData['dates']['start'] . ' 00:00:00') . '\' order by `datetime` desc limit 1;';

    $sqlRestStart = mysql_query($qqq);
    $restStartQqq = mysql_fetch_assoc($sqlRestStart);
    $restStart = $restStartQqq['action_value'];

    $dayDiff = ceil((strtotime($getData['dates']['end'] . ' 23:59:59') - strtotime($getData['dates']['start'] . ' 00:00:00')) / (60 * 60 * 24));
    $dayDiff = ($dayDiff < 1) ? 1 : $dayDiff;

    $obj['rest_start'] = (int) $restStart;
    $obj['send'] = 0;
    $obj['return'] = 0;
    $obj['income'] = 0;
    $obj['write_off'] = 0;
    $obj['rest_end'] = 0;
    $obj['end_after'] = 0;

    if (mysql_num_rows($query) > 0) {
        while ($store = mysql_fetch_object($query)) {
            switch ($store->action_type) {
                case 'send':
                    $obj['send'] += $store->action_value;
                    break;
                case 'return':
                    $obj['return'] += $store->action_value;
                    break;
                case 'income':
                    $obj['income'] += $store->action_value;
                    break;
                case 'write_off':
                    $obj['write_off'] += $store->action_value;
                    break;
            }
        }
    }
    $obj['rest_end'] = $obj['rest_start'] - $obj['send'] + $obj['return'] + $obj['income'] - $obj['write_off'];

    if (($obj['send'] - $obj['return']) > 0) {
        $obj['end_after'] = round($obj['rest_end'] / (($obj['send'] - $obj['return'] + $obj['income'] - $obj['write_off']) / $dayDiff));
    }

    if ($obj['offer_property_name'] != null && in_array($obj['offer_property_name'], ['size', 'color'])) {
        $obj['offer_desc'] .= '[' . $obj['offer_property_value'] . ']';
        $obj['offer_storage_name'] .= '[' . $obj['offer_property_value'] . ']';
    }

    $data[] = $obj;
}
$objPHPExcel->setActiveSheetIndex(0);

$translate = [
    'delivery' => 'Склад',
    'offer_desc' => 'Продукт',
    'offer_storage_name' => 'Продукт',
    'send' => 'Отправлен',
    'return' => 'Возврат',
    'income' => 'Поступление',
    'write_off' => 'Списание',
    'rest_end' => 'Остаток на конец',
    'end_after' => 'Закончится через',
    'rest_start' => 'Остаток на начало'
];
$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Склад');
$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Продукт');
$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Отправлен');
$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Возврат');
$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Поступление');
$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Списание');
$objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Остаток на конец');
$objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Закончится через');
$objPHPExcel->getActiveSheet()->SetCellValue('I1', 'Остаток на начало');

foreach ($data as $index => $fields) {
    $objPHPExcel->getActiveSheet()->SetCellValue('A' . ($index + 2), $fields['delivery']);
    $objPHPExcel->getActiveSheet()->SetCellValue('B' . ($index + 2), $fields['offer_storage_name']);
    $objPHPExcel->getActiveSheet()->SetCellValue('C' . ($index + 2), $fields['send']);
    $objPHPExcel->getActiveSheet()->SetCellValue('D' . ($index + 2), $fields['return']);
    $objPHPExcel->getActiveSheet()->SetCellValue('E' . ($index + 2), $fields['income']);
    $objPHPExcel->getActiveSheet()->SetCellValue('F' . ($index + 2), $fields['write_off']);
    $objPHPExcel->getActiveSheet()->SetCellValue('G' . ($index + 2), $fields['rest_end']);
    $objPHPExcel->getActiveSheet()->SetCellValue('H' . ($index + 2), $fields['end_after']);
    $objPHPExcel->getActiveSheet()->SetCellValue('I' . ($index + 2), $fields['rest_start']);
}

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);

$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=download_storage_' . date('Y-m-d H:i') . '.xlsx');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

ob_clean();
flush();
$objWriter->save('php://output');
