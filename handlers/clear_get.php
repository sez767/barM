<?php

session_start();
if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';

if (!empty($_REQUEST['id']) && ($data = DB::queryOneRow('SELECT id, is_get, who_get FROM `staff_order` WHERE `id` = %i', $_REQUEST['id']))) {
    DB::update('staff_order', array('is_get' => 0), 'id = %i AND `who_get` = %i', $data['id'], $_SESSION['Logged_StaffId']);
//    $actionHistoryObj = new ActionHistoryObj();
//    $actionHistoryObj->save('StaffOrderObj', $data['id'], 'update', 'is_get', $data['is_get'], 0);
//    $actionHistoryObj->save('StaffOrderObj', $data['id'], 'update', 'who_get', $data['who_get'], $_SESSION['Logged_StaffId']);
}
echo json_encode(array('success' => true));
