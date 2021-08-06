<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

if (strlen($_GET['ids']) > 5) {
    $dataIds = explode(",", substr($_GET['ids'], 0, strlen($_GET['ids']) - 1));

    $limit = (int) $_GET['ogr'];
    if ($limit) {
        $it_ar = array_chunk($dataIds, $limit);
        $dataIds = $it_ar[0];
    }

    $dataIds[] = -1;
    $updateArr = array('status_kz' => (int) $_GET['status']);
    $origAssArr = DB::queryAssArray('id', 'SELECT * FROM staff_order WHERE id IN %li', $dataIds);
    if ((DB::update('staff_order', $updateArr, 'id IN %li', $dataIds))) {
        $actionHistoryObj = new ActionHistoryObj();
        foreach ($origAssArr as $id => $origData) {
            foreach ($updateArr as $key => $val) {
                $actionHistoryObj->save('StaffOrderObj', $id, 'update', $key, $origData[$key], $val);
            }
        }
        echo '{"success":true}';
    } else {
        echo '{"success":false}';
    }
}
