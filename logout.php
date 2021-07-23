<?php

session_start();
include_once (dirname(__FILE__) . "/lib/db.php");

if (($staffId = $_SESSION['Logged_StaffId'])) {
    $actionHistoryObj = new ActionHistoryObj();
    $actionHistoryObj->save('StaffObj', $staffId, 'logout', 'logout', '', 'ok');
}
unset($_SESSION['Logged_StaffId']);
session_unset();
Header("location:/login.html");

die();
