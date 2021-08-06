<?php

require_once dirname(__FILE__) . "/../lib/db.php";

// УПЛ
header('Content-Type: text/html; charset=utf-8', true);

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
// require_once dirname(__FILE__) . "/../lib/excel/excel.class.php";
require_once dirname(__FILE__) . "/excel.inc.php";

$ids = array();
if (!empty($_REQUEST['ids_data'])) {
    $ids = json_decode($_REQUEST['ids_data'], true);
}

foreach ($ids as $key => $id) {
    if (strlen($id) == 0 || $id <= 0) {
        unset($ids[$key]);
    }
}
$ids[] = -1;

$query = "
	SELECT id
	FROM staff_order
	WHERE `send_status` = 'Отправлен' AND `status_kz` IN ('На доставку', 'Вручить подарок') AND
                date_delivery BETWEEN CURDATE() AND CURDATE() + INTERVAL 1 DAY AND
                snapshot_by = 0 AND
                id in %li";

$checkData = DB::queryFirstColumn($query, $ids);

if (DB::update('staff_order', array('snapshot_by' => $_SESSION['Logged_StaffId']), 'id IN %li', $checkData)) {
    // staff_order_snapshot - заполниться на mysql-триггере
    $result['data'][] = 'Количество принятых позиций: ' . count($checkData);
} else {
    $result['data'][] = 'Ошибка принятия отчета';
}

print json_encode($result);

