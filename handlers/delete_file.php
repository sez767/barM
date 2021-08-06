<?php

session_start();

if (!isset($_SESSION['Logged_StaffId'])) {
    header("location: /login.html");
    die();
}
require_once dirname(__FILE__) . '/../lib/db.php';
require_once (dirname(__FILE__) . "/../lib/class.staff.php");



//  проверка файла на ошибки
if (
        strlen($_GET['file']) < 3
) {
    die(json_encode(array(
        "success" => FALSE,
        "msg" => "ќшибка"
    )));
}
unlink('./../docs/' . $_GET['file']);

print json_encode(array(
    "success" => FALSE,
    "msg" => '‘айлу жопа'
));
$actionHistoryObj = new ActionHistoryObj();
$actionHistoryObj->save('StaffObj', $_SESSION['Logged_StaffId'], 'delete', 'file', 'file_' . $_GET['file'], '');
